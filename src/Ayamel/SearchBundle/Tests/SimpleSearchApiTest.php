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
class SimpleSearchApiTest extends FixturedTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->runCommand("fos:elastica:populate");
        $this->index = $this->getClient()->getContainer()->get('fos_elastica.index.ayamel');
        $this->index->refresh();
        $this->index->flush();
    }

    public function testQueryStringRequired()
    {
        $res = $this->callJsonApi('GET', '/api/v1/resources/search?filter:type=audio', ['expectedCode' => 400]);
        $res = $this->callJsonApi('GET', '/api/v1/resources/search?q&filter:type=audio', ['expectedCode' => 400]);
        $res = $this->callJsonApi('GET', '/api/v1/resources/search?q=&filter:type=audio', ['expectedCode' => 400]);
    }

    public function testSimpleSearchApi()
    {
        //hit ayamel api
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=enim');
        $code = $response['response']['code'];
        $this->assertSame(200, $code);
        $this->assertSame(16, $response['query']['total']);
        $this->assertSame(16, count($response['hits']));
    }

    /**
     * @depends testSimpleSearchApi
     */
    public function testLimit()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=enim&limit=5');
        $code = $response['response']['code'];
        $this->assertSame(200, $code);
        $this->assertSame(16, $response['query']['total']);
        $this->assertSame(5, count($response['hits']));
    }

    /**
     * @depends testSimpleSearchApi
     */
    public function testSkip()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=enim&skip=15');
        $code = $response['response']['code'];
        $this->assertSame(200, $code);
        $this->assertSame(16, $response['query']['total']);
        $this->assertSame(1, count($response['hits']));
    }

    /**
     * @depends testSimpleSearchApi
     */
    public function testSimpleSearchApiHidesUnauthorizedResources($ids)
    {
        //public request
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=enim&limit=50');
        $this->assertSame(16, $response['query']['total']);
        foreach ($response['hits'] as $hit) {
            $this->assertTrue(empty($hit['resource']['visibility']));
        }

        //private test_client + public
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=enim&limit=50&_key=45678isafgd56789asfgdhf4567');
        $this->assertSame(33, $response['query']['total']);
        foreach ($response['hits'] as $hit) {
            if (!empty($hit['resource']['visibility'])) {
                $this->assertTrue(in_array('test_client', $hit['resource']['visibility']));
            }
        }

        //private, test_client2 + public
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=enim&limit=50&_key=55678isafgd56789asfgdhf4568');
        $this->assertSame(50, $response['query']['total']);
        foreach ($response['hits'] as $hit) {
            if (!empty($hit['resource']['visibility'])) {
                $this->assertTrue(in_array('test_client2', $hit['resource']['visibility']));
            }
        }
    }

    /**
     * @depends testSimpleSearchApi
     */
    public function testTypeFilter()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=enim&filter:type=audio');
        $this->assertSame(4, count($response['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=enim&filter:type=video');
        $this->assertSame(4, count($response['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=enim&filter:type=video,audio');
        $this->assertSame(8, count($response['hits']));
    }

    /**
     * @depends testSimpleSearchApi
     */
    public function testSubjectDomainsFilter()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=enim&filter:subjectDomains=science');
        $this->assertSame(4, count($response['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=enim&filter:subjectDomains=weather');
        $this->assertSame(4, count($response['hits']));

        //OR
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=enim&filter:subjectDomains=science,weather');
        $this->assertSame(7, count($response['hits']));

        //AND
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=enim&filter:subjectDomains[]=science&filter:subjectDomains[]=weather');
        $this->assertSame(1, count($response['hits']));
    }

    /**
     * @depends testSimpleSearchApi
     */
    public function testFunctionalDomainsFilter()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=enim&filter:functionalDomains=informative');
        $this->assertSame(9, count($response['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=enim&filter:functionalDomains=presentational');
        $this->assertSame(7, count($response['hits']));

        //OR
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=enim&filter:functionalDomains=presentational,informative');
        $this->assertSame(13, count($response['hits']));

        //AND
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=enim&filter:functionalDomains[]=presentational&filter:functionalDomains[]=informative');
        $this->assertSame(3, count($response['hits']));
    }

    /**
     * @depends testSimpleSearchApi
     */
    public function testRegistersFilter()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=enim&filter:registers=formal');
        $this->assertSame(5, count($response['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=enim&filter:registers=intimate');
        $this->assertSame(7, count($response['hits']));

        //OR
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=enim&filter:registers=formal,intimate');
        $this->assertSame(11, count($response['hits']));

        //AND
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=enim&filter:registers[]=formal&filter:registers[]=intimate');
        $this->assertSame(1, count($response['hits']));
    }

    /**
     * @depends testSimpleSearchApi
     */
    public function testClientFilter()
    {
        $this->markTestSkipped();
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=enim&filter:client=another-test-client');
        $this->assertSame(3, count($response['hits']));
    }

    public function testClientUserFilter()
    {
        $this->markTestSkipped();
    }

    /**
     * @depends testSimpleSearchApi
     */
    public function testLanguageFilter()
    {
        $this->markTestSkipped();
    }

    /**
     * @depends testSimpleSearchApi
     */
    public function testMultipleFilters()
    {
        //either audio or video
        //that contains both geography and politics as subject
        //and has contains either formal or intimate registers
        $response = $this->callJsonApi('GET',
            '/api/v1/resources/search?q=enim'.
            '&filter:registers=formal,intimate'.
            '&filter:subjectDomains[]=geography'.
            '&filter:subjectDomains[]=politics'.
            '&filter:type=audio,video'
        );
        $this->assertSame(1, count($response['hits']));
        $hit = $response['hits'][0]['resource'];

        $this->assertTrue(in_array($hit['type'], ['audio','video']));
        $this->assertTrue(in_array('geography', $hit['subjectDomains']));
        $this->assertTrue(in_array('politics', $hit['subjectDomains']));
    }

    /**
     * @depends testSimpleSearchApi
     */
    public function testTypeFacet()
    {
        $this->markTestSkipped();
    }

    /**
     * @depends testSimpleSearchApi
     */
    public function testClientFacet()
    {
        $this->markTestSkipped();
    }

    /**
     * @depends testSimpleSearchApi
     */
    public function testLangaugeFacet()
    {
        $this->markTestSkipped();
    }

    /**
     * @depends testSimpleSearchApi
     */
    public function testFunctionalDomainsFacet()
    {
        $this->markTestSkipped();
    }

    /**
     * @depends testSimpleSearchApi
     */
    public function testSubjectDomainsFacet()
    {
        $this->markTestSkipped();
    }

    /**
     * @depends testSimpleSearchApi
     */
    public function testRegistersFacet()
    {
        $this->markTestSkipped();
    }

}
