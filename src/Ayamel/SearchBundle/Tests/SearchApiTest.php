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
        $this->createDummyResources();
        $this->assertSame(10, $this->index->count());

        $results = $this->index->search();

        // The search results seem to come back in an indeterminate order,
        // so just check that some fields are present in the returned data.
        $this->assertFalse(empty($results[0]->getData()['functionalDomains']));
    }

    /**
     * @depends testSearchIndex
     */
    public function testSimpleSearchApi()
    {
        $this->createDummyResources();

        //hit raw ES api
        $client = new Client('http://127.0.0.1:9200');
        $response = $client->get('/ayamel/resource/_search')->send();
        $body = json_decode($response->getBody(), true);
        $this->assertSame(10, count($body['hits']['hits']));

        //hit ayamel api
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=House');
        $code = $response['response']['code'];
        $this->assertSame(200, $code);

$this->markTestIncomplete();
var_dump($response);
        $this->assertFalse(empty($response['results']['_results']));
        $this->assertSame(10, count($response['results']['_results']));
        $this->assertSame(10, count($response['results']['_response']['_response']['hits']['hits']));
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
