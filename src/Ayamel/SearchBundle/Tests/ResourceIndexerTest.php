<?php

namespace Ayamel\SearchBundle\Tests;

use Ayamel\SearchBundle\ResourceIndexer;
use Ayamel\SearchBundle\SearchTest;
use Ayamel\ResourceBundle\Document\Resource;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Guzzle\Http\Exception\ClientErrorResponseException;

/**
 * TODO: Convert this test to use fixtures, rather than the API.
 */
class ResourceIndexerTest extends SearchTest
{

    public function testLoadIndexer()
    {
        $indexer = $this->getContainer()->get('ayamel.search.resource_indexer');
        $this->assertTrue($indexer instanceof ResourceIndexer);
    }

    public function testThrowsExceptionOnNonExistingResource()
    {
        $this->setExpectedException('Ayamel\SearchBundle\Exception\IndexException');
        $this->getContainer()->get('ayamel.search.resource_indexer')->indexResource('123456');
    }

    public function testThrowsExceptionOnUnindexableResource()
    {
        $response = $this->getJson('POST', '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array(
            'title' => 'Hamlet pwnz!',
            'type' => 'data'
        )));
        $this->assertSame(201, $response['response']['code']);
        $resourceId = $response['resource']['id'];

        $this->setExpectedException('Ayamel\SearchBundle\Exception\IndexException');
        $this->getContainer()->get('ayamel.search.resource_indexer')->indexResource($resourceId);
    }

    public function testThrowsExceptionIndexingResourcesWithNoContent()
    {
        $response = $this->getJson('POST', '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array(
            'title' => 'Hamlet pwnz!',
            'type' => 'document'
        )));
        $this->assertSame(201, $response['response']['code']);
        $resourceId = $response['resource']['id'];

        $this->setExpectedException('Ayamel\SearchBundle\Exception\IndexException');
        $this->getContainer()->get('ayamel.search.resource_indexer')->indexResource($resourceId);
    }

    public function testIndexResource()
    {
        $container = $this->getContainer();

        //create resource
        $response = $this->getJson('POST', '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array(
            'title' => 'Hamlet pwnz!',
            'type' => 'document'
        )));
        $this->assertSame(201, $response['response']['code']);
        $resourceId = $response['resource']['id'];
        $uploadUrl = substr($response['contentUploadUrl'], strlen('http://localhost'));
        $content = $this->getJson('POST', $uploadUrl.'?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array(
            'uri' => 'http://www.google.com/'
        )));
        $this->assertSame(200, $content['response']['code']);

        //expect 404 from elastic search, not indexed yet
        $this->setUpGuzzle();
        try {
            $response = $this->guzzleClient->get("/$this->indexName/resource/".$resourceId)->send();
        } catch (ClientErrorResponseException $exception) {
        }
        $this->assertSame(404, $exception->getResponse()->getStatusCode());

        //index new resource
        $container->get('ayamel.search.resource_indexer')->indexResource($resourceId);

        //query the specific search document
        $response = $this->guzzleClient->get("/$this->indexName/resource/".$resourceId)->send();
        $this->assertSame(200, $response->getStatusCode());

        return $resourceId;
    }

    /**
     * @depends testIndexResource
     **/
    public function testIndexerRemovesDeletedResources($id)
    {
        //delete resource
        $response = $this->getJson('DELETE', '/api/v1/resources/'.$id.'?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ));
        $this->assertSame(200, $response['response']['code']);

        //reindex deleted resource
        $this->getContainer()->get('ayamel.search.resource_indexer')->indexResource($id);

        $this->setUpGuzzle();
        try {
            $response = $this->guzzleClient->get("/$this->indexName/resource/".$id)->send();
        } catch (ClientErrorResponseException $exception) {
        }
        $this->assertSame(404, $exception->getResponse()->getStatusCode());
    }

    public function testIndexerIndexesContentFromFiles()
    {
        $container = $this->getContainer();
        $indexer = $container->get('ayamel.search.resource_indexer');

        $response = $this->getJson('POST', '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array(
            'title' => 'Hamlet pwnz!',
            'type' => 'document',
            'languages' => array(
                'iso639_3' => array('eng'),
                'bcp47' => array('en')
            )
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
        $content = $this->getJson('POST', $uploadUrl.'?_key=45678isafgd56789asfgdhf4567', [], array('file' => $uploadedFile));
        $this->assertSame(202, $content['response']['code']);

        //index it
        $indexer->indexResource($resourceId);

        //make sure record exists
        $this->setUpGuzzle();
        $response = $this->guzzleClient->get("/$this->indexName/resource/".$resourceId)->send();
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);

        //check for content_canonical
        $this->assertTrue(isset($body['_source']['content_canonical']));
        $this->assertSame(1, count($body['_source']['content_canonical']));
        $this->assertSame(0, strpos($body['_source']['content_canonical'][0], "To be, or not to be"));

        return $resourceId;
    }
    
    public function testIndexerConvertsNonUtf8FileContent()
    {
        $response = $this->getJson('POST', '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array(
            'title' => 'Utf-8 is for n00bs!',
            'type' => 'document',
            'languages' => array(
                'iso639_3' => array('eng'),
                'bcp47' => array('en')
            )
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
        $content = $this->getJson('POST', $uploadUrl.'?_key=45678isafgd56789asfgdhf4567', [], array('file' => $uploadedFile));
        $this->assertSame(202, $content['response']['code']);
    }
    
    public function testIndexerSkipsNonConvertableFileContent()
    {
        
    }

    /**
     * @depends testIndexerIndexesContentFromFiles
     **/
    public function testIndexerIndexesContentFromRelatedResources($id)
    {
        $container = $this->getContainer();
        $indexer = $container->get('ayamel.search.resource_indexer');

        //create a new resource w/ Russian
        $response = $this->getJson('POST', '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array(
            'title' => 'Гамлет круто!',
            'type' => 'document',
            'languages' => array(
                'iso639_3' => array('rus'),
                'bcp47' => array('ru')
            )
        )));
        $this->assertSame(201, $response['response']['code']);
        $objectId = $response['resource']['id'];
        $uploadUrl = substr($response['contentUploadUrl'], strlen('http://localhost'));
        $testFilePath = __DIR__."/files/hamlet.ru.txt";
        $uploadedFile = new UploadedFile(
            $testFilePath,
            'hamlet.ru.txt',
            'text/plain',
            filesize($testFilePath)
        );
        $content = $this->getJson('POST', $uploadUrl.'?_key=45678isafgd56789asfgdhf4567', [], array('file' => $uploadedFile));
        $this->assertSame(202, $content['response']['code']);

        //create relations
        $response = $this->getJson('POST', '/api/v1/relations?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array(
            'subjectId' => $id,
            'objectId' => $objectId,
            'type' => 'search'
        )));
        $this->assertSame(201, $response['response']['code']);

        //reindex the subject resource
        $indexer->indexResource($id);
        $this->setUpGuzzle();

        //check for new content fields imported from related resource
        $response = $this->guzzleClient->get("/$this->indexName/resource/".$id)->send();
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertTrue(isset($body['_source']['content_canonical']));
        $this->assertSame(1, count($body['_source']['content_canonical']));
        $this->assertSame(0, strpos($body['_source']['content_canonical'][0], "To be, or not to be"));
        $this->assertTrue(isset($body['_source']['content_rus']));
        $this->assertSame(1, count($body['_source']['content_rus']));
        $this->assertSame(0, strpos($body['_source']['content_rus'][0], "Быть иль не быть"));

        //index the object resource
        $indexer->indexResource($objectId);
        $response = $this->guzzleClient->get("/$this->indexName/resource/".$objectId)->send();
        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody(), true);
        $this->assertTrue(isset($body['_source']['content_canonical']));
        $this->assertSame(1, count($body['_source']['content_canonical']));
        $this->assertSame(0, strpos($body['_source']['content_canonical'][0], "Быть иль не быть"));
    }

    public function testBulkIndexResources()
    {
        $this->markTestSkipped();
    }
}
