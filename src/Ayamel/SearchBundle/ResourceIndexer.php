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
use Ayamel\SearchBundle\Exception\IndexException;
use Ayamel\SearchBundle\Exception\BulkIndexException;

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
     * @return boolean        Returns true when operation was successful, false otherwise
     * @throws IndexException Thrown when a Resource could not be indexed.
     **/
    public function indexResource($id)
    {
        $doc = $this->createResourceSearchDocumentForId($id);

        if ($doc instanceof Document) {
            $this->type->addDocument($doc);
            $this->type->getIndex()->refresh();

            return true;
        }

        return false;
    }

    public function indexResources(array $ids, $batch = 100)
    {
        $count = 0;
        $failed = array();
        foreach ($ids as $id) {
            try {
                $count++;
                $doc = $this->createResourceSearchDocumentForId($id);
                if ($doc) {
                    $this->type->addDocument($doc);
                }
            } catch (IndexException $e) {
                $failed[$id] = $e->getMessage();
                continue;
            }

            if ($count >= $batch) {
                $count = 0;
                $this->type->getIndex()->refresh();
            }
        }

        $this->type->getIndex()->refresh();

        if (!empty($failed)) {
             $e = new BulkIndexException($failed);

             // TODO: ad-hoc debug, if needed should implement logging
             
             // $messages = $e->getMessages();
             // $indices = array_keys($messages);
             // print_r("\nFailed to index " . count($messages) . " resources.\n");
             // print_r("ResourceIndexer failure messages:\n");
             // foreach ($indices as $index) {
             //     print_r("id: $index; message: " . $messages[$index] . "\n");
             // }

             throw $e;
        }

        return true;
    }

    public function indexResourcesByFields(array $fields = array(), $batch = 100)
    {
        throw new \RuntimeException('not implemented');

        $ids = array(); //query for ids;

        $this->indexResources($ids, $batch);

        return true;
    }

    /**
     * Given an ID, this will create a corresponding search document IF POSSIBLE.
     * If the Resource was deleted, it will be immediately removed from the index.
     *
     * @param  string           $id
     * @return Elastca\Document
     */
    protected function createResourceSearchDocumentForId($id)
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

            return false;
        }

        if (!in_array($resource->getType(), $this->indexableResourceTypes)) {
            throw new IndexException(sprintf("Resources of type [%s] are not indexable.", $resource->getType()));
        }

        if ('awaiting_content' === $resource->getStatus()) {
            throw new IndexException(sprintf("Resource [%s] cannot be indexed until it has content.", $resource->getId()));
        }

        //fill in relations if any
        $relations = $this->manager->getRepository('AyamelResourceBundle:Relation')->getQBForRelations(array(
            'subjectId' => $id,
            'client.id' => $resource->getClient()->getId()
        ))->getQuery()->execute();

        if (count($relations) > 0) {
            $resource->setRelations(iterator_to_array($relations));
        }

        return $this->createResourceSearchDocument($resource);
    }

    /**
     * Creates an Elastica document from a Resource.  This will search the database
     * for related Resources to find text content that should be indexed.
     *
     * @return Elastica\Document
     **/
    protected function createResourceSearchDocument(Resource $resource)
    {
        //meh, stupidly inefficient
        $data = json_decode($this->serializer->serialize($resource, 'json'), true);

        //now check search relations and get relevant file content
        $relatedResourceIds = array();
        $relatedResources = array();
        foreach ($resource->getRelations() as $relation) {
            if ('search' === $relation->getType() && $resource->getId() === $relation->getSubjectId()) {
                $relatedResourceIds[] = $relation->getObjectId();
            }
        }

        if (!empty($relatedResourceIds)) {
            $relatedResources = $this->manager->getRepository('AyamelResourceBundle:Resource')
                ->getQBForResources(array('id' => $relatedResourceIds))
                ->getQuery()
                ->execute();
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
        foreach ($relatedResources as $related) {
            $lang = $this->parseLanguage($related->languages);

            if (!$lang) {
                $lang = 'canonical';
            }

            $field = 'content_'.$lang;
            if (!isset($contentFields[$field])) {
                $contentFields[$field] = array();
            }

            if ($related->content) {
                foreach ($related->content->getFiles() as $fileReference) {
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
     * @param  Languages    $langs
     * @return string|false
     */
    protected function parseLanguage(Languages $langs = null)
    {
        if (!$langs) {
            return false;
        }

        if ($langs->iso639_3) {
            $tag = $langs->iso639_3[0];

            if (isset($this->languageFieldMap[$tag])) {
                return $tag;
            }

            return $this->searchLanguageMapForTag($tag);

        } elseif ($langs->bcp47) {
            $exp = explode('-', $langs->bcp47[0]);
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
