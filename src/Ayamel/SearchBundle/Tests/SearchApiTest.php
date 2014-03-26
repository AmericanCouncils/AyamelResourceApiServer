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
    public function setUp()
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
     *
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
     *
     */
    public function testSearchIndex()
    {
        $this->assertSame(10, $this->index->count());
        $results = $this->index->search();
        // The search results seem to come back in an indeterminate order,
        // so just check that some fields are present in the returned data.
        $this->assertFalse(empty(($results[0]->getData()['functionalDomains'])));
    }

    /**
     * @depends testFixtures
     * @depends testSearchIndex
     */
    public function testSimpleSearchApi($ids)
    {
        $this->markTestIncomplete();
        $client = new Client('http://127.0.0.1:9200');
        $response = $client->get('/ayamel/resource/')->send();
        var_dump($response->getBody());

        return;

        $proc = $this->startRabbitListener(3);
        $tester = $this;
        $proc->setTimeout(5);
        $b = [];
        $proc->wait(function ($type, $buffer) use ($tester, $proc) {
            $b[] = $buffer;
            while ($proc->isRunning()) {
                usleep(50000); //wait a tiny bit to make sure the process actually quit (... meh)
            }

            if (!$proc->isSuccessful()) {
                throw new \RuntimeException($proc->getErrorOutput());
            }

            $response = $tester->getJson('GET', '/api/v1/resources/search?q=House');
            $code = $response['response']['code'];
            $tester->assertSame(200, $code);
            print_r($response);
            $tester->assertFalse(empty($response['results']['_results']));
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
