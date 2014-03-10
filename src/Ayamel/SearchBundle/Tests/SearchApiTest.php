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

    public function setUp()
    {
        // add some dummy resources to query
        $json = $this->getJson('POST', '/api/v1/resources?_key='.'1', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array(
            'title' => 'Russia House',
            'type' => 'video',
            'description' => 'An expatriate British publisher unexpectedly finds himself working for British intelligence to investigate people in Russia.'
        )));

        $json = $this->getJson('POST', '/api/v1/resources?_key='.'2', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array(
            'title' => 'Dire Dire Docks',
            'type' => 'uri',
            'description' => 'An original vocal arrangement of the Super Mario 64 song Dire Dire Docks.',
            'uri' => 'https://www.youtube.com/watch?v=GBBlLeqKaf4'
        )));             
    }

    public function testSetupDummyResources()
    {

        $response = $this->getJson('GET', '/api/v1/resources', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(['id' => [1,2]]));
        // $this->markTestIncomplete();
        // query db to make sure that the dummy resources are present

    }

    /**
     * @depends testSetupDummyResources
     */
    public function testSimpleSearchApi($ids)
    {
        $response = $this->getJson('GET', '/api/v1/resources/search',  array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(['q' => 'russia']));
        $code = $response['response']['code'];
        if (200 != $code) {
            print_r($response);
        }
        $this->assertSame(200, $code);
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
