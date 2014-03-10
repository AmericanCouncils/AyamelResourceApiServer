<?php

namespace Ayamel\SearchBundle\Tests;

use Ayamel\ApiBundle\ApiTestCase;

/**
 * This set of tests makes sure the API search routes perform as expected.  Most importantly
 * they need to strip unauthoried Resources from the returned results.
 *
 * @package AyamelSearchBundle
 * @author Evan Villemez
 */
class SearchApiTest extends ApiTestCase
{

    public function testSetupDummyResources()
    {
        $ids = array();
        
        
        // $this->markTestSkipped();

        return $ids;
    }

    /**
     * @depends testSetupDummyResources
     */
    public function testSimpleSearchApi($ids)
    {
        
        $requestData = json_encode([
            'query_string' => 'Russia',
        ]);
        $this->getClient()->request('GET', '/api/search', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], $requestData);
        $crawler = $this->getClient()->request('GET', '/api/search');
        $this->assertFalse(empty($this->getClient()->getResponse()));


        // $content = json_decode($this->getClient()->getResponse()->getContent(), True);
        // print_r($content);
        // $this->assertSame(200, $content['response']['code']);
        // $this->assertFalse(500 != $content['response']['code']);
    }

    /**
     * @depends testSetupDummyResources
     */
    public function testSimpleSearchApiHidesUnauthorizedResources($ids)
    {
        $this->markTestSkipped();
    }

    /**
     * @depends testSetupDummyResources
     */
    public function testAdvancedSearchApi($ids)
    {
        $this->markTestSkipped();
    }

    /**
     * @depends testSetupDummyResources
     */
    public function testAdvancedSearchApiHidesUnauthorizedResources($ids)
    {
        $this->markTestSkipped();
    }
}
