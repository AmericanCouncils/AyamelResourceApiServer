<?php

namespace Ayamel\SearchBundle\Tests;

use Guzzle\Http\Client;
use Ayamel\ApiBundle\Tests\FixturedTestCase;

class ResourceIndexTest extends FixturedTestCase
{
    public function testCreateIndex()
    {
        $this->runCommand('fos:elastica:reset');

        //index should exist
        $client = new Client('http://127.0.0.1:9200');
        $response = $client->get('/ayamel/resource/_mapping')->send();
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testPopulateIndex()
    {
        parent::setUp();

        //ensure fixtures loaded
        $this->assertTrue(!empty($this->fixtureData));
        $id = $this->fixtureData['AyamelResourceBundle:Resource'][0]->getId();
        $content = $this->callJsonApi('GET', "/api/v1/resources/$id?_key=45678isafgd56789asfgdhf4567");
        $this->assertArrayHasKey('resource', $content);

        //populate index
        $this->runCommand("fos:elastica:populate");
        $this->index = $this->getClient()->getContainer()->get('fos_elastica.index.ayamel');
        $this->index->refresh();
        $this->index->flush();
        $this->assertSame(10, $this->index->count());

        // The search results seem to come back in an indeterminate order,
        // so just check that some fields are present in the returned data.
        $results = $this->index->search();
        $this->assertFalse(empty($results[0]->getData()['functionalDomains']));

        //hit raw ES API, expect fixtures
        $client = new Client('http://127.0.0.1:9200');
        $response = $client->get('/ayamel/resource/_search')->send();
        $body = json_decode($response->getBody(), true);
        $this->assertSame(10, count($body['hits']['hits']));
    }
}
