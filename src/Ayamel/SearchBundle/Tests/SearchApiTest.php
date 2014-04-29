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
    public function testTopicsFilter()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&filter:topics=religion');
        $this->assertSame(7, count($response['result']['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&filter:topics=weather');
        $this->assertSame(4, count($response['result']['hits']));

        //OR
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&filter:topics=religion,weather');
        $this->assertSame(10, count($response['result']['hits']));

        //AND
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&filter:topics[]=religion&filter:topics[]=weather');
        $this->assertSame(1, count($response['result']['hits']));
    }

    /**
     * @depends testSearchApi
     */
    public function testFunctionsFilter()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&filter:functions=promise');
        // $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user');
        $this->assertSame(4, count($response['result']['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&filter:functions=reporting');
        $this->assertSame(3, count($response['result']['hits']));

        //OR
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&filter:functions=reporting,promise');
        $this->assertSame(6, count($response['result']['hits']));

        //AND
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&filter:functions[]=reporting&filter:functions[]=promise');
        $this->assertSame(1, count($response['result']['hits']));
    }

    /**
     * @depends testSearchApi
     */
    public function testRegistersFilter()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&filter:registers=formal');
        $this->assertSame(4, count($response['result']['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&filter:registers=other');
        $this->assertSame(3, count($response['result']['hits']));

        //OR
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&filter:registers=formal,other');
        $this->assertSame(6, count($response['result']['hits']));

        //AND
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&filter:registers[]=formal&filter:registers[]=other');
        $this->assertSame(1, count($response['result']['hits']));
    }

    public function testFormatsFilter()
    {
        $response = $this->callJsonApi(
            'GET',
            'api/v1/resources/search?q=user&filter:formats=interview'
        );
        $this->assertSame(1, count($response['result']['hits']));

        $response = $this->callJsonApi(
            'GET',
            'api/v1/resources/search?q=user&filter:formats=documentary'
        );
        $this->assertSame(3, count($response['result']['hits']));

        //OR
        $response = $this->callJsonApi(
            'GET',
            'api/v1/resources/search?q=user&filter:formats=interview,documentary'
        );
        $this->assertSame(3, count($response['result']['hits']));

        //AND
        $response = $this->callJsonApi(
            'GET',
            'api/v1/resources/search?q=user&filter:formats[]=interview&filter:formats[]=documentary'
        );
        $this->assertSame(1, count($response['result']['hits']));
    }

    public function testAuthenticityFilter()
    {
        $response = $this->callJsonApi(
            'GET',
            'api/v1/resources/search?q=user&filter:authenticity=native'
        );
        $this->assertSame(8, count($response['result']['hits']));

        $response = $this->callJsonApi(
            'GET',
            'api/v1/resources/search?q=user&filter:authenticity=other'
        );
        $this->assertSame(4, count($response['result']['hits']));

        //OR
        $response = $this->callJsonApi(
            'GET',
            'api/v1/resources/search?q=user&filter:authenticity=native,other'
        );
        $this->assertSame(12, count($response['result']['hits']));

        //AND
        $response = $this->callJsonApi(
            'GET',
            'api/v1/resources/search?q=user&filter:authenticity[]=native&filter:authenticity[]=other'
        );
        $this->assertSame(0, count($response['result']['hits']));
    }

    public function testGenresFilter()
    {
        $response = $this->callJsonApi(
            'GET',
            'api/v1/resources/search?q=user&filter:genres=comedy'
        );
        $this->assertSame(3, count($response['result']['hits']));

        $response = $this->callJsonApi(
            'GET',
            'api/v1/resources/search?q=user&filter:genres=musical'
        );
        $this->assertSame(3, count($response['result']['hits']));

        //OR
        $response = $this->callJsonApi(
            'GET',
            'api/v1/resources/search?q=user&filter:genres=comedy,musical'
        );
        $this->assertSame(5, count($response['result']['hits']));

        //AND
        $response = $this->callJsonApi(
            'GET',
            'api/v1/resources/search?q=user&filter:genres[]=comedy&filter:genres[]=musical'
        );
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
        //that contains both geography and politics as topic
        //and has contains either formal or intimate registers
        $response = $this->callJsonApi('GET',
            '/api/v1/resources/search?q=user'.
            '&filter:registers=formal,intimate'.
            '&filter:topics[]=geography'.
            '&filter:topics[]=politics'.
            '&filter:type=audio,video'
        );
        $this->assertSame(1, count($response['result']['hits']));
        $hit = $response['result']['hits'][0]['resource'];

        $this->assertTrue(in_array($hit['type'], ['audio','video']));
        $this->assertTrue(in_array('geography', $hit['topics']));
        $this->assertTrue(in_array('politics', $hit['topics']));
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
    public function testFunctionsFacet()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&facet:functions');
        $facet = $response['result']['facets'][0];
        $this->assertSame(10, count($facet['values'])); //default limit
        $this->assertSame('functions', $facet['field']);

        //test limit
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&facet:functions=2');
        $facet = $response['result']['facets'][0];
        $this->assertSame(2, count($facet['values']));
    }

    public function testMultipleFacets()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&facet:type&facet:topics');
        $this->assertSame(2, count($response['result']['facets']));
    }

    /**
     * @depends testSearchApi
     */
    public function testTopicsFacet()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&facet:topics');
        $facet = $response['result']['facets'][0];
        $this->assertSame(10, count($facet['values'])); //default limit
        $this->assertSame('topics', $facet['field']);

        //test limit
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&facet:topics=2');
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
        $this->assertSame(6, count($facet['values']));
        $this->assertSame('registers', $facet['field']);

        //test limit
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=user&facet:registers=2');
        $facet = $response['result']['facets'][0];
        $this->assertSame(2, count($facet['values']));
    }

    public function testFormatsFacet()
    {
        $response = $this->callJsonApi('GET', 'api/v1/resources/search?q=user&facet:formats');
        $facet = $response['result']['facets'][0];
        $this->assertSame(10, count($facet['values']));
        $this->assertSame('formats', $facet['field']);

        $response = $this->callJsonApi('GET', 'api/v1/resources/search?q=user&facet:formats=2');
        $facet = $response['result']['facets'][0];
        $this->assertSame(2, count($facet['values']));
    }

    public function testAuthenticityFacet()
    {
        $response = $this->callJsonApi('GET', 'api/v1/resources/search?q=user&facet:authenticity');
        $facet = $response['result']['facets'][0];
        $this->assertSame(4, count($facet['values']));
        $this->assertSame('authenticity', $facet['field']);

        $response = $this->callJsonApi('GET', 'api/v1/resources/search?q=user&facet:authenticity=2');
        $facet = $response['result']['facets'][0];
        $this->assertSame(2, count($facet['values']));
    }

    public function testGenresFacet()
    {
        $response = $this->callJsonApi('GET', 'api/v1/resources/search?q=user&facet:genres');
        $facet = $response['result']['facets'][0];
        $this->assertSame(10, count($facet['values']));
        $this->assertSame('genres', $facet['field']);

        $response = $this->callJsonApi('GET', 'api/v1/resources/search?q=user&facet:genres=2');
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
