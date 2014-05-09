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
use Elastica\Exception\NotFoundException;
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
        try {
            $doc = $this->createResourceSearchDocumentForId($id);
        } catch (IndexException $e) {
            $this->log(sprintf("Not indexing [%s] because [%s]", $id, $e->getMessage()));

            throw $e;
        }        

        if ($doc instanceof Document) {
            $this->type->addDocument($doc);
            $this->type->getIndex()->refresh();

            $this->log(sprintf("Indexed Resource %s", $id));

            return true;
        }

        return false;
    }

    public function indexResources(array $ids, $batch = 100)
    {
        $count = 0;
        $fails = 0;
        $failed = array();
        foreach ($ids as $id) {
            try {
                $count++;
                $doc = $this->createResourceSearchDocumentForId($id);
                if ($doc) {
                    $this->type->addDocument($doc);
                }
            } catch (IndexException $e) {
                $fails++;
                $failed[$id] = $e->getMessage();
                continue;
            }

            if ($count >= $batch) {
                $this->type->getIndex()->refresh();
                $this->log(sprintf("Indexed [%s] & skipped [%s] resources.", $count, $fails));
                $count = 0;
                $fails = 0;
            }
        }

        $this->type->getIndex()->refresh();
        $this->log(sprintf("Indexed [%s] & skipped [%s] resources.", $count, $fails));

        if (!empty($failed)) {
            throw new BulkIndexException($failed);
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
     * @param  string            $id
     * @return Elastica\Document
     */
    protected function createResourceSearchDocumentForId($id)
    {
        $resource = $this->manager->getRepository('AyamelResourceBundle:Resource')->find($id);

        if (!$resource) {
            throw new IndexException(sprintf("Tried indexing a non-existing resource [%s]", $id));
        }

        if ($resource->isDeleted()) {
            try {
                $this->type->deleteById($id);
            } catch (NotFoundException $e) {
                throw new IndexException("The Resource was already removed from the index.");
            }

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
            $resource->setRelations($relations->toArray());
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
        //TODO: change how the Resource gets loaded and force array hydration so we can
        //get rid of this silly hack
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
                $this->log(sprintf("Failed getting search index content at [%s]", $uri), 'warning');

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

        if ($langs->getIso639_3()) {
            $tag = $langs->getIso639_3()[0];

            if (isset($this->languageFieldMap[$tag])) {
                return $tag;
            }

            return $this->searchLanguageMapForTag($tag);

        } elseif ($langs->getBcp47()) {
            $exp = explode('-', $langs->getBcp47()[0]);
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

    protected function log($msg, $level = 'info')
    {
        if ($this->logger) {
            $this->logger->log($level, $msg);
        }
    }
}
