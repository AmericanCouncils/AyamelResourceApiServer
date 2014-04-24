<?php

namespace Ayamel\SearchBundle\Tests;

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
        $this->index->refresh();
        $this->index->flush();
    }

    public function testQueryStringRequired()
    {
        $res = $this->callJsonApi('GET', '/api/v1/resources/search?filter:type=audio', ['expectedCode' => 400]);
        $res = $this->callJsonApi('GET', '/api/v1/resources/search?q&filter:type=audio', ['expectedCode' => 400]);
        $res = $this->callJsonApi('GET', '/api/v1/resources/search?q=&filter:type=audio', ['expectedCode' => 400]);
    }

    public function testSearchApi()
    {
        //hit ayamel api
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user');
        $code = $response['response']['code'];
        $this->assertSame(200, $code);
        $this->assertSame(16, $response['result']['query']['total']);
        $this->assertSame(16, count($response['result']['hits']));
    }

    /**
     * @depends testSearchApi
     */
    public function testLimit()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&limit=5');
        $code = $response['response']['code'];
        $this->assertSame(200, $code);
        $this->assertSame(16, $response['result']['query']['total']);
        $this->assertSame(5, count($response['result']['hits']));
    }

    /**
     * @depends testSearchApi
     */
    public function testSkip()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&skip=15');
        $code = $response['response']['code'];
        $this->assertSame(200, $code);
        $this->assertSame(16, $response['result']['query']['total']);
        $this->assertSame(1, count($response['result']['hits']));
    }

    /**
     * @depends testSearchApi
     */
    public function testSearchApiHidesUnauthorizedResources($ids)
    {
        //public request
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&limit=50');
        $this->assertSame(16, $response['result']['query']['total']);
        foreach ($response['result']['hits'] as $hit) {
            $this->assertTrue(empty($hit['resource']['visibility']));
        }

        //private test_client + public
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&limit=50&_key=45678isafgd56789asfgdhf4567');
        $this->assertSame(33, $response['result']['query']['total']);
        foreach ($response['result']['hits'] as $hit) {
            if (!empty($hit['resource']['visibility'])) {
                $this->assertTrue(in_array('test_client', $hit['resource']['visibility']));
            }
        }

        //private, test_client2 + public
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&limit=50&_key=55678isafgd56789asfgdhf4568');
        $this->assertSame(50, $response['result']['query']['total']);
        foreach ($response['result']['hits'] as $hit) {
            if (!empty($hit['resource']['visibility'])) {
                $this->assertTrue(in_array('test_client2', $hit['resource']['visibility']));
            }
        }
    }

    /**
     * @depends testSearchApi
     */
    public function testTypeFilter()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&filter:type=audio');
        $this->assertSame(4, count($response['result']['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&filter:type=video');
        $this->assertSame(4, count($response['result']['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&filter:type=video,audio');
        $this->assertSame(8, count($response['result']['hits']));
    }

    /**
     * @depends testSearchApi
     */
    public function testSubjectDomainsFilter()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&filter:subjectDomains=science');
        $this->assertSame(4, count($response['result']['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&filter:subjectDomains=weather');
        $this->assertSame(4, count($response['result']['hits']));

        //OR
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&filter:subjectDomains=science,weather');
        $this->assertSame(7, count($response['result']['hits']));

        //AND
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&filter:subjectDomains[]=science&filter:subjectDomains[]=weather');
        $this->assertSame(1, count($response['result']['hits']));
    }

    /**
     * @depends testSearchApi
     */
    public function testFunctionalDomainsFilter()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&filter:functionalDomains=informative');
        $this->assertSame(9, count($response['result']['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&filter:functionalDomains=presentational');
        $this->assertSame(7, count($response['result']['hits']));

        //OR
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&filter:functionalDomains=presentational,informative');
        $this->assertSame(13, count($response['result']['hits']));

        //AND
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&filter:functionalDomains[]=presentational&filter:functionalDomains[]=informative');
        $this->assertSame(3, count($response['result']['hits']));
    }

    /**
     * @depends testSearchApi
     */
    public function testRegistersFilter()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&filter:registers=formal');
        $this->assertSame(5, count($response['result']['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&filter:registers=intimate');
        $this->assertSame(7, count($response['result']['hits']));

        //OR
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&filter:registers=formal,intimate');
        $this->assertSame(11, count($response['result']['hits']));

        //AND
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&filter:registers[]=formal&filter:registers[]=intimate');
        $this->assertSame(1, count($response['result']['hits']));
    }

    /**
     * @depends testSearchApi
     */
    public function testClientFilter()
    {
        $this->markTestSkipped();
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&filter:client=another-test-client');
        $this->assertSame(3, count($response['result']['hits']));
    }

    public function testClientUserFilter()
    {
        $this->markTestSkipped();
    }

    /**
     * @depends testSearchApi
     */
    public function testLanguageFilter()
    {
        $this->markTestSkipped();
    }

    /**
     * @depends testSearchApi
     */
    public function testMultipleFilters()
    {
        //either audio or video
        //that contains both geography and politics as subject
        //and has contains either formal or intimate registers
        $response = $this->callJsonApi('GET',
            '/api/v1/resources/search?q=user'.
            '&filter:registers=formal,intimate'.
            '&filter:subjectDomains[]=geography'.
            '&filter:subjectDomains[]=politics'.
            '&filter:type=audio,video'
        );
        $this->assertSame(1, count($response['result']['hits']));
        $hit = $response['result']['hits'][0]['resource'];

        $this->assertTrue(in_array($hit['type'], ['audio','video']));
        $this->assertTrue(in_array('geography', $hit['subjectDomains']));
        $this->assertTrue(in_array('politics', $hit['subjectDomains']));
    }

    public function testEmptyFacets()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user');
        $this->assertTrue(isset($response['result']['facets']));
        $this->assertTrue(empty($response['result']['facets']));
    }

    /**
     * @depends testSearchApi
     */
    public function testTypeFacet()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&facet:type');
        $facet = $response['result']['facets'][0];
        $this->assertSame(5, count($facet['values']));
        $this->assertSame('type', $facet['field']);
        $this->assertSame($facet['hits'], $response['result']['query']['total']);
        $this->assertSame(0, $facet['missing']);
        $this->assertSame(0, $facet['other']);

        //limit size of facet
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&facet:type=2');
        $facet = $response['result']['facets'][0];
        $this->assertSame(2, count($facet['values']));
        $this->assertSame('type', $facet['field']);
        $this->assertSame($facet['hits'], $response['result']['query']['total']);
        $this->assertTrue($facet['other'] > 0);
    }

    /**
     * @depends testSearchApi
     */
    public function testFunctionalDomainsFacet()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&facet:functionalDomains');
        $facet = $response['result']['facets'][0];
        $this->assertSame(3, count($facet['values'])); //default limit
        $this->assertSame('functionalDomains', $facet['field']);

        //test limit
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&facet:functionalDomains=2');
        $facet = $response['result']['facets'][0];
        $this->assertSame(2, count($facet['values']));
    }

    public function testMultipleFacets()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&facet:type&facet:subjectDomains');
        $this->assertSame(2, count($response['result']['facets']));
    }

    /**
     * @depends testSearchApi
     */
    public function testSubjectDomainsFacet()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&facet:subjectDomains');
        $facet = $response['result']['facets'][0];
        $this->assertSame(10, count($facet['values'])); //default limit
        $this->assertSame('subjectDomains', $facet['field']);

        //test limit
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&facet:subjectDomains=2');
        $facet = $response['result']['facets'][0];
        $this->assertSame(2, count($facet['values']));
    }

    /**
     * @depends testSearchApi
     */
    public function testRegistersFacet()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&facet:registers');
        $facet = $response['result']['facets'][0];
        $this->assertSame(5, count($facet['values'])); //default limit
        $this->assertSame('registers', $facet['field']);

        //test limit
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&facet:registers=2');
        $facet = $response['result']['facets'][0];
        $this->assertSame(2, count($facet['values']));
    }

    /**
     * @depends testSearchApi
     */
    public function testClientFacet()
    {
        $this->markTestSkipped();
    }

    /**
     * @depends testSearchApi
     */
    public function testClientUserFacet()
    {
        $this->markTestSkipped();
    }

    /**
     * @depends testSearchApi
     */
    public function testLangaugeFacet()
    {
        $this->markTestSkipped();
    }
}
