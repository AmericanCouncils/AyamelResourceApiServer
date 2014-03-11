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
        $this->clearDatabase();

        $json = $this->getJson('POST', '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array(
            'title' => 'Russia House',
            'type' => 'document',
        )));

        $json = $this->getJson('POST', '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array(
            'title' => 'Sealand House',
            'type' => 'document',
        )));     
        $json = $this->getJson('POST', '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array(
            'title' => 'Maxwell House',
            'type' => 'document',
        )));             
    }

    public function testSetupDummyResources()
    {
        $response = $this->getJson('GET', '/api/v1/resources');
        $this->assertSame(3, (count($response['resources'])));
    }

    /**
     * @depends testSetupDummyResources
     */
    public function testSimpleSearchApi($ids)
    {
        $response = $this->getJson('GET', '/api/v1/resources/search?q=russia');
        $code = $response['response']['code'];
        if (200 != $code) {
            print_r($response);
        }
        print_r($response);
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
