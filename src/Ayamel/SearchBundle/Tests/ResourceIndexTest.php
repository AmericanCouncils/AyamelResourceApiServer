<?php

namespace Ayamel\SearchBundle\Tests;

use Guzzle\Http\Client;
use Ayamel\ApiBundle\Tests\FixturedTestCase;

class ResourceIndexTest extends FixturedTestCase
{
    private $indexName;
    private $guzzleClient;

    public function setUp()
    {
        parent::setUp();

        $container = $this->getClient()->getContainer();
        $this->guzzleClient = new Client(implode([
            'http://',
            $container->getParameter('elasticsearch_host'),
            ":",
            $container->getParameter('elasticsearch_port')
        ]));

        $this->indexName = $container->getParameter('elasticsearch_index');
    }

    public function testCreateIndex()
    {
        $this->runCommand('fos:elastica:reset');

        //index should exist
        $response = $this->guzzleClient->get("/$this->indexName/resource/_mapping")->send();
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testPopulateIndex()
    {
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
        $this->assertSame(50, $this->index->count());

        // The search results seem to come back in an indeterminate order,
        // so just check that some fields are present in the returned data.
        $results = $this->index->search();
        $this->assertFalse(empty($results[0]->getData()['topics']));

        //hit raw ES API, expect fixtures
        $response = $this->guzzleClient->get("/$this->indexName/resource/_search")->send();
        $body = json_decode($response->getBody(), true);

        //10 is the default ES limit
        $this->assertSame(10, count($body['hits']['hits']));
    }
}
