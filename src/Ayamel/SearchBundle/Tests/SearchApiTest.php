<?php

namespace Ayamel\SearchBundle\Tests;

use Guzzle\Http\Client;
use Ayamel\ApiBundle\Tests\FixturedTestCase;

/**
 * This set of tests makes sure the API search routes perform as expected.  Most importantly
 * they need to strip unauthoried Resources from the returned results.
 *
 * @package AyamelSearchBundle
 * @author Evan Villemez
 */
class SearchApiTest extends FixturedTestCase
{
    protected function createDummyResources()
    {
        parent::setUp();
        $this->runCommand("fos:elastica:populate");
        $this->index = $this->getClient()->getContainer()->get('fos_elastica.index.ayamel');
        $type = $this->index->getType('test');
        $this->index->refresh();
        $this->index->flush();

        $uploadUrls = [];
        $ids = [];
        $titles = ['The Russia House','The Sealand House','The Maxwell House'];

        foreach ($titles as $title) {
            $response = $this->getJson('POST', '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
                'CONTENT_TYPE' => 'application/json'
            ), json_encode(array(
                'title' => $title,
                'type' => 'document',
            )));

            $uploadUrls[] = substr($response['contentUploadUrl'], strlen('http://localhost'));
            $ids[] = $response['resource']['id'];
        }

        foreach ($uploadUrls as $uploadUrl) {
            $content = $this->getJson('POST', $uploadUrl.'?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
                'CONTENT_TYPE' => 'application/json'
            ), json_encode(array(
                'uri' => 'http://www.google.com/'
            )));
        }

        return $ids;
    }

    /**
     * Make sure the fixtures were loaded
     */
    public function testFixtures()
    {
        $this->assertTrue(!empty($this->fixtureData));
        $id = $this->fixtureData['AyamelResourceBundle:Resource'][0]->getId();
        $content = $this->callJsonApi('GET', "/api/v1/resources/$id?_key=45678isafgd56789asfgdhf4567");
        $this->assertArrayHasKey('resource', $content);
    }
    /**
     * Make sure that the search index actually knows about the fixtures
     */
    public function testSearchIndex()
    {
        $this->assertSame(10, $this->index->count());
        $results = $this->index->search();

        // The search results seem to come back in an indeterminate order,
        // so just check that some fields are present in the returned data.
        $this->assertFalse(empty($results[0]->getData()['functionalDomains']));

        //*
        $this->createDummyResources();
        $response = $this->getJson('GET', '/api/v1/resources');
        $this->assertSame(3, (count($response['resources'])));

        $ids = [];
        foreach ($response['resources'] as $r) {
            $ids[] = $r['id'];
        }

        return $ids;
        //*/
    }

    /**
     * @depends testFixtures
     * @depends testSearchIndex
     */
    public function testSimpleSearchApi($ids)
    {
        $client = new Client('http://127.0.0.1:9200');
        $response = $client->get('/ayamel/resource/')->send();
        var_dump($response->getBody());

        return;

        $this->createDummyResources();

        $proc = $this->startRabbitListener(3);
        $tester = $this;
        $b = [];
        $proc->wait(function($type, $buffer) use ($tester, $proc) {

            $tester->assertTrue(false);

            while ($proc->isRunning()) {
                usleep(50000); //wait a tiny bit to make sure the process actually quit (... meh)
            }

            if (!$proc->isSuccessful()) {
                throw new \RuntimeException($proc->getErrorOutput());
            }

            //hit raw ES api
            $client = new Client('http://127.0.0.1:9200');
            $response = $client->get('/ayamel/resource/_search')->send();
            $body = json_decode($response->getBody());
            $tester->assertSame(3, count($body['hits']['hits']));

            //hit ayamel api
            $response = $tester->getJson('GET', '/api/v1/resources/search?q=House');
            $code = $response['response']['code'];
            $tester->assertSame(200, $code);
            $tester->assertFalse(empty($response['results']['_results']));
            $tester->assertSame(3, count($response['results']['_results']));
            $tester->assertSame(3, count($response['results']['_response']['_response']['hits']['hits']));
        });
    }

    /**
     * @depends testFixtures
     */
    public function testSimpleSearchApiHidesUnauthorizedResources($ids)
    {
        $this->markTestSkipped();
    }

    /**
     * @depends testFixtures
     */
    public function testAdvancedSearchApi($ids)
    {
        $this->markTestSkipped();
    }

    /**
     * @depends testFixtures
     */
    public function testAdvancedSearchApiHidesUnauthorizedResources($ids)
    {
        $this->markTestSkipped();
    }
}
