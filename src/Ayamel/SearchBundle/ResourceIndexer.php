<?php

namespace Ayamel\SearchBundle;

use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\Relation;
use Ayamel\ResourceBundle\Document\FileReference;
use Ayamel\ResourceBundle\Document\Languages;
use Doctrine\ODM\MongoDB\DocumentManager;
use JMS\Serializer\SerializerInterface;
use Elastica\Document;
use Elastica\Type;
use Psr\Log\LoggerInterface;

/**
 * This class implements the logic for creating Elastica search documents from
 * Resource objects.
 * 
 * NOTE: This implementation currently contains a bit of a hack using the serializer - once the serializer
 * supports serializing to arrays, it can be made more efficient: https://github.com/schmittjoh/serializer/pull/20/
 *
 * @package AyamelSearchBundle
 * @author Evan Villemez
 **/
class ResourceIndexer
{
    private $manager;
    private $type;
    private $serializer;
    private $indexableMimeTypes;
    private $indexableResourceTypes;
    private $logger = null;
    private $languageFieldMap;

    /**
     * Constructor needs lots of stuff
     *
     **/
    public function __construct(
        DocumentManager $manager,
        Type $resourceType, 
        SerializerInterface $serializer, 
        array $indexableMimeTypes = array('text/plain'),
        array $indexableResourceTypes = array('audio','video','image'),
        LoggerInterface $logger = null,
        $languageFieldMap = array()
    ) {
        $this->manager = $manager;
        $this->type = $resourceType;
        $this->serializer = $serializer;
        $this->indexableMimeTypes = $indexableMimeTypes;
        $this->indexableResourceTypes = $indexableResourceTypes;
        $this->logger = $logger;
        $this->languageFieldMap = $languageFieldMap;
    }

    /**
     * Update a Resource's search index document.  If the Resource was deleted,
     * this will take care of removing it from the search index as well.
     *
     * @return boolean Returns true when operation was successful, false otherwise
     * @throws  IndexException Thrown when a Resource could not be indexed.
     **/
    public function indexResource($id)
    {
        $resource = $this->manager->getRepository('AyamelResourceBundle:Resource')->find($id);

        if (!$resource) {
            if ($this->logger) {
                $this->logger->warning(sprintf("Tried indexing a non-exiting resource [%s]", $id));
            }
            
            throw new IndexException("The Resource could not be found in order to index.");
        }

        if ($resource->isDeleted()) {
            $this->type->deleteById($id);
            return true;
        }

        if (!in_array($resource->getType(), $this->indexableResourceTypes)) {
            throw new IndexException(sprintf("Resources of type [%s] are not indexable.", $resource->getType()));
        }

        if ('awaiting_content' === $resource->getStatus()) {
            throw new IndexException(sprintf("Resource [%s] cannot be indexed until it has content.", $resource->getId()));
        }

        //fill in relations if any
        $relations = $this->manager->getRepository('AyamelResourceBundle:Relation')->getRelationsForResource($id, array(
            'type' => 'search'
        ));
        if (count($relations) > 0) {
            $resource->setRelations(iterator_to_array($relations));
        }

        $this->type->addDocument($this->createResourceSearchDocument($resource));
        $this->type->getIndex()->refresh();

        return true;
    }

    public function indexResourcesByFields(array $fields = array(), $batch = 100)
    {
        throw new \RuntimeException('not implemented');
    }

    public function indexResources(array $ids, $batch = 100)
    {
        $newDocs = array();
        $deletedDocs = array();

        throw new \RuntimeException("Not yet implemented.");
    }

    public function createSearchDocumentById($id)
    {
        throw new \RuntimeException("not implemented");

        $resource = $this->manager->getRepository('AyamelResourceBundle:Resource')->find($id);
        $relations = $this->manager->getRepository('AyamelResourceBundle:Relation')->getRelationsForResource($id, array(
            'type' => 'search'
        ));

        if (count($relations) > 0) {
            $resource->setRelations(iterator_to_array($relations));
        }

        return $this->createResourceSearchDocument($resource);
    }

    /**
     * Creates an Elastica document from a Resource.  This will search the database
     * for related Resources to find text content that should be indexed.
     *
     * @return Document
     **/
    public function createResourceSearchDocument(Resource $resource)
    {
        //meh, stupidly inefficient
        $data = json_decode($this->serializer->serialize($resource, 'json'), true);
        
        var_dump($resource->getRelations());
        
        //now check search relations and get relevant file content
        $relatedResourceIds = array();
        $relatedResources = array();
        foreach ($resource->getRelations() as $relation) {
            if ('search' === $relation->getType() && $resource->getId() === $relation->getSubjectId()) {
                $relatedResourceIds[] = $relation->getObjectId();
            }
        }

        if (!empty($relatedResourceIds)) {
            $relatedResources = $this->manager->getRepository('AyamelResourceBundle:Resource')->findBy(array('id' => $relatedResourceIds));
            $relatedResources = count($relatedResources) > 0 ? iterator_to_array($relatedResources) : array();
        }

        $contentFields = $this->generateContentFields($resource, $relatedResources);
        $data = array_merge($data, $contentFields);

        return new Document($resource->getId(), $data);
    }

    /**
     * This actually imports file content for the main resource and related resource to populate all the "content_*" fields.
     *
     * Note: Currently this is very inefficient, it's triggering requests one-by-one and can be refactored to make requests
     * for file content in parallel using a library like Guzzle.
     *
     * @return array
     **/
    protected function generateContentFields(Resource $resource, array $relatedResources = array())
    {
        $contentFields = array();

        //handle content for main resource
        if ($resource->content) {
            foreach ($resource->content->getFiles() as $fileReference) {
                if (in_array($fileReference->getMimeType(), $this->indexableMimeTypes)) {
                    if (!isset($contentFields['content_canonical'])) {
                        $contentFields['content_canonical'] = array();
                    }

                    if ($content = $this->retrieveContent($fileReference)) {
                        $contentFields['content_canonical'][] = $content;
                    }
                }
            }
        }

        //check related resources for indexable text
        foreach ($relatedResources as $resource) {
            $lang = $this->parseLanguage($resource->languages);
            if (!$lang) {
                $lang = 'canonical';
            }
            $field = 'content_'.$lang;
            if (!isset($contentFields[$field])) {
                $contentFields[$field] = array();
            }

            if ($resource->content) {
                foreach ($resource->content->getFiles() as $fileReference) {
                    if (in_array($fileReference->getMimeType(), $this->indexableMimeTypes)) {
                        if ($content = $this->retrieveContent($fileReference)) {
                            $contentFields[$field][] = $content;
                        }
                    }
                }
            }
        }

        return $contentFields;
    }

    protected function retrieveContent(FileReference $ref)
    {
        $uri = ($ref->getInternalUri()) ? $ref->getInternalUri() : $ref->getDownloadUri();
        if ($uri) {
            try {
                return file_get_contents($uri);
            } catch (\Exception $e) {
                if ($this->logger) {
                    $this->logger->warning(sprintf("Failed getting search index content at [%s]", $uri));
                }
                
                return false;
            }
        }

        return false;
    }

    /**
     * Note, it's assumed that the first language in any list is the primary language.
     *
     * @param Languages $langs
     * @return string|false
     */
    protected function parseLanguage(Languages $langs)
    {
        if ($langs->iso639_9) {
            $tag = $langs->iso639_9[0];

            if (isset($this->languageFieldMap[$tag])) {
                return $tag;
            }
            
            return $this->searchLanguageMapForTag($tag);
            
        } else if ($langs->bcp47) {
            $exp = explode('-', $langs->iso639_9[0]);
            $tag = $exp[0];
            
            return $this->searchLanguageMapForTag($tag);
        }
        
        return false;
    }
    
    protected function searchLanguageMapForTag($tag)
    {
        foreach ($this->languageFieldMap as $key => $vals) {
            if (in_array($tag, $vals)) {
                return $key;
            }
        }
        
        return false;
    }
}
