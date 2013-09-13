<?php

namespace Ayamel\SearchBundle\Tests;

use Ayamel\ApiBundle\ApiTestCase;
use Ayamel\SearchBundle\ResourceIndexer;
use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\Relation;
use Ayamel\ResourceBundle\Document\Origin;
use Ayamel\ResourceBundle\Document\ContentCollection;
use Ayamel\ResourceBundle\Document\FileReference;
use Guzzle\Http\Client;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Guzzle\Http\Exception\ClientErrorResponseException;

class ResourceIndexerTest extends ApiTestCase
{
    
    public function testLoadIndexer()
    {
        $indexer = $this->getContainer()->get('ayamel.search.resource_indexer');
        $this->assertTrue($indexer instanceof ResourceIndexer);
    }

    public function testThrowsExceptionOnNonExistingResource()
    {
        $this->setExpectedException('Ayamel\SearchBundle\IndexException');
        $this->getContainer()->get('ayamel.search.resource_indexer')->indexResource('123456');
    }

    public function testThrowsExceptionOnUnindexableResource()
    {
        $response = $this->getJson('POST', '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array(
            'title' => 'Hamlet pwnz!',
            'type' => 'data'
        )));
        $this->assertSame(201, $response['response']['code']);
        $resourceId = $response['resource']['id'];

        $this->setExpectedException('Ayamel\SearchBundle\IndexException');
        $this->getContainer()->get('ayamel.search.resource_indexer')->indexResource($resourceId);
    }

    public function testThrowsExceptionIndexingResourcesWithNoContent()
    {
        $response = $this->getJson('POST', '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array(
            'title' => 'Hamlet pwnz!',
            'type' => 'document'
        )));
        $this->assertSame(201, $response['response']['code']);
        $resourceId = $response['resource']['id'];

        $this->setExpectedException('Ayamel\SearchBundle\IndexException');
        $this->getContainer()->get('ayamel.search.resource_indexer')->indexResource($resourceId);
    }

    public function testIndexResource()
    {
        $container = $this->getContainer();
        $indexer = $container->get('ayamel.search.resource_indexer');
        $client = new Client('http://127.0.0.1:9200');

        //create resource
        $response = $this->getJson('POST', '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array(
            'title' => 'Hamlet pwnz!',
            'type' => 'document'
        )));
        $this->assertSame(201, $response['response']['code']);
        $resourceId = $response['resource']['id'];
        $uploadUrl = substr($response['contentUploadUrl'], strlen('http://localhost'));
        $testFilePath = __DIR__."/files/hamlet.en.txt";
        $uploadedFile = new UploadedFile(
            $testFilePath,
            'hamlet.en.txt',
            'text/plain',
            filesize($testFilePath)
        );
        $content = $this->getJson('POST', $uploadUrl.'?_key=45678isafgd56789asfgdhf4567', array(), array('file' => $uploadedFile));
        $this->assertSame(202, $content['response']['code']);

        //expect 404 from elastic search, not indexed yet
        try {
            $response = $client->get('/ayamel/resource/'.$resourceId)->send();
        } catch (ClientErrorResponseException $exception) {
        }
        $this->assertSame(404, $exception->getResponse()->getStatusCode());

        //index new resource
        $indexer->indexResource($resourceId);

        //query the specific search document
        $response = $client->get('/ayamel/resource/'.$resourceId)->send();
        $this->assertSame(200, $response->getStatusCode());

        return $resourceId;
    }

    /**
     * @depends testIndexResource
     **/
    public function testIndexerRemovesDeletedResources($id)
    {
        //delete resource
        $response = $this->getJson('DELETE', '/api/v1/resources/'.$id.'?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ));
        $this->assertSame(200, $response['response']['code']);

        //reindex deleted resource
        $this->getContainer()->get('ayamel.search.resource_indexer')->indexResource($id);

        $client = new Client('http://127.0.0.1:9200');
        try {
            $response = $client->get('/ayamel/resource/'.$id)->send();
        } catch (ClientErrorResponseException $exception) {
        }
        $this->assertSame(404, $exception->getResponse()->getStatusCode());
    }

    public function testIndexerIndexesContentFromFiles()
    {
        $this->markTestSkipped();

        //check for content_canonical
    }

    /**
     * @depends testIndexerIndexesContentFromFiles
     **/
    public function testIndexerIndexesContentFromRelatedResources()
    {
        $this->markTestSkipped();

        //create new resource w/ russian
        //create relations
        //index original
        //check for specific content_rus
    }
}
