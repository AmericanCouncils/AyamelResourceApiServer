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

    public function testSearchApi()
    {
        //should return all (visible) results
        $response = $this->callJsonApi('GET', '/api/v1/resources/search');
        $code = $response['response']['code'];
        $this->assertSame(200, $code);
        $this->assertSame(16, $response['result']['query']['total']);
        $this->assertSame(16, count($response['result']['hits']));
    }

    /**
     * @depends testSearchApi
     */
    public function testSimpleSearchStringQuery()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=enim');
        $code = $response['response']['code'];
        $this->assertSame(200, $code);
        $this->assertSame(4, $response['result']['query']['total']);
        $this->assertSame(4, count($response['result']['hits']));
    }

    /**
     * @depends testSearchApi
     */
    public function testSearchStringQuery()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?s=enim');
        $code = $response['response']['code'];
        $this->assertSame(200, $code);
        $this->assertSame(4, $response['result']['query']['total']);
        $this->assertSame(4, count($response['result']['hits']));
    }

    /**
     * @depends testSearchApi
     */
    public function testLimit()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?limit=5');
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
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?skip=15');
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
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?limit=50');
        $this->assertSame(16, $response['result']['query']['total']);
        foreach ($response['result']['hits'] as $hit) {
            $this->assertTrue(empty($hit['resource']['visibility']));
        }

        //private test_client + public
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?limit=50&_key=45678isafgd56789asfgdhf4567');
        $this->assertSame(33, $response['result']['query']['total']);
        foreach ($response['result']['hits'] as $hit) {
            if (!empty($hit['resource']['visibility'])) {
                $this->assertTrue(in_array('test_client', $hit['resource']['visibility']));
            }
        }

        //private, test_client2 + public
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?limit=50&_key=55678isafgd56789asfgdhf4568');
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
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?filter:type=audio');
        $this->assertSame(4, count($response['result']['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?filter:type=video');
        $this->assertSame(4, count($response['result']['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?filter:type=video,audio');
        $this->assertSame(8, count($response['result']['hits']));
    }

    /**
     * @depends testSearchApi
     */
    public function testTopicsFilter()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?filter:topics=religion');
        $this->assertSame(7, count($response['result']['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?filter:topics=weather');
        $this->assertSame(4, count($response['result']['hits']));

        //OR
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?filter:topics=religion,weather');
        $this->assertSame(10, count($response['result']['hits']));

        //AND
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?filter:topics[]=religion&filter:topics[]=weather');
        $this->assertSame(1, count($response['result']['hits']));
    }

    /**
     * @depends testSearchApi
     */
    public function testFunctionsFilter()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?filter:functions=promise');
        // $response = $this->callJsonApi('GET', '/api/v1/resources/search?');
        $this->assertSame(4, count($response['result']['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?filter:functions=reporting');
        $this->assertSame(3, count($response['result']['hits']));

        //OR
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?filter:functions=reporting,promise');
        $this->assertSame(6, count($response['result']['hits']));

        //AND
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?filter:functions[]=reporting&filter:functions[]=promise');
        $this->assertSame(1, count($response['result']['hits']));
    }

    /**
     * @depends testSearchApi
     */
    public function testRegistersFilter()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?filter:registers=formal');
        $this->assertSame(4, count($response['result']['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?filter:registers=other');
        $this->assertSame(3, count($response['result']['hits']));

        //OR
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?filter:registers=formal,other');
        $this->assertSame(6, count($response['result']['hits']));

        //AND
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?filter:registers[]=formal&filter:registers[]=other');
        $this->assertSame(1, count($response['result']['hits']));
    }

    /**
     * @depends testSearchApi
     */
    public function testFormatsFilter()
    {
        $response = $this->callJsonApi(
            'GET',
            'api/v1/resources/search?filter:formats=interview'
        );
        $this->assertSame(1, count($response['result']['hits']));

        $response = $this->callJsonApi(
            'GET',
            'api/v1/resources/search?filter:formats=documentary'
        );
        $this->assertSame(3, count($response['result']['hits']));

        //OR
        $response = $this->callJsonApi(
            'GET',
            'api/v1/resources/search?filter:formats=interview,documentary'
        );
        $this->assertSame(3, count($response['result']['hits']));

        //AND
        $response = $this->callJsonApi(
            'GET',
            'api/v1/resources/search?filter:formats[]=interview&filter:formats[]=documentary'
        );
        $this->assertSame(1, count($response['result']['hits']));
    }

    /**
     * @depends testSearchApi
     */
    public function testAuthenticityFilter()
    {
        $response = $this->callJsonApi(
            'GET',
            'api/v1/resources/search?filter:authenticity=native'
        );
        $this->assertSame(5, count($response['result']['hits']));

        $response = $this->callJsonApi(
            'GET',
            'api/v1/resources/search?filter:authenticity=other'
        );
        $this->assertSame(4, count($response['result']['hits']));

        //OR
        $response = $this->callJsonApi(
            'GET',
            'api/v1/resources/search?filter:authenticity=native,other'
        );
        $this->assertSame(9, count($response['result']['hits']));

        //AND
        $response = $this->callJsonApi(
            'GET',
            'api/v1/resources/search?filter:authenticity[]=native&filter:authenticity[]=other'
        );
        $this->assertSame(0, count($response['result']['hits']));
    }

    /**
     * @depends testSearchApi
     */
    public function testGenresFilter()
    {
        $response = $this->callJsonApi(
            'GET',
            'api/v1/resources/search?filter:genres=comedy'
        );
        $this->assertSame(3, count($response['result']['hits']));

        $response = $this->callJsonApi(
            'GET',
            'api/v1/resources/search?filter:genres=musical'
        );
        $this->assertSame(3, count($response['result']['hits']));

        //OR
        $response = $this->callJsonApi(
            'GET',
            'api/v1/resources/search?filter:genres=comedy,musical'
        );
        $this->assertSame(5, count($response['result']['hits']));

        //AND
        $response = $this->callJsonApi(
            'GET',
            'api/v1/resources/search?filter:genres[]=comedy&filter:genres[]=musical'
        );
        $this->assertSame(1, count($response['result']['hits']));
    }

    /**
     * @depends testSearchApi
     */
    public function testClientFilter()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?filter:client=test-client');
        $this->assertSame(6, count($response['result']['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?filter:client=another-test-client');
        $this->assertSame(10, count($response['result']['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?filter:client=test-client,another-test-client');
        $this->assertSame(16, count($response['result']['hits']));
    }

    /**
     * @depends testSearchApi
     */
    public function testClientUserFilter()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?filter:clientUser=user-6');
        $this->assertSame(7, count($response['result']['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?filter:clientUser=user-10');
        $this->assertSame(3, count($response['result']['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?filter:clientUser=user-6,user-10');
        $this->assertSame(10, count($response['result']['hits']));
    }

    /**
     * @depends testSearchApi
     */
    public function testLanguageFilter()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?filter:languages=eng');
        $this->assertSame(8, count($response['result']['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?filter:languages=ru');
        $this->assertSame(10, count($response['result']['hits']));

        //and
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?filter:languages=ru,eng');
        $this->assertSame(12, count($response['result']['hits']));

        //or (has both english & russian)
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?filter:languages[]=eng,rus&filter:languages[]=en,ru');
        $this->assertSame(8, count($response['result']['hits']));
        foreach ($response['result']['hits'] as $hit) {
            $langs = $hit['resource']['languages'];
            $this->assertTrue(in_array('en', $langs['bcp47']) || in_array('ru', $langs['bcp47']));
            $this->assertTrue(in_array('eng', $langs['iso639_3']) || in_array('rus', $langs['iso639_3']));
        }
    }

    public function testLicenseFilter()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?filter:license=CC BY');
        $this->assertSame(2, count($response['result']['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?filter:license=CC BY-ND');
        $this->assertSame(2, count($response['result']['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?filter:license=CC BY,CC BY-ND');
        $this->assertSame(4, count($response['result']['hits']));
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
            '/api/v1/resources/search?'.
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

    /**
     * @depends testSearchApi
     */
    public function testEmptyFacets()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?');
        $this->assertTrue(isset($response['result']['facets']));
        $this->assertTrue(empty($response['result']['facets']));
    }

    /**
     * @depends testSearchApi
     */
    public function testTypeFacet()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?facet:type');
        $facet = $response['result']['facets'][0];
        $this->assertSame(5, count($facet['values']));
        $this->assertSame('type', $facet['field']);
        $this->assertSame($facet['hits'], $response['result']['query']['total']);
        $this->assertSame(0, $facet['missing']);
        $this->assertSame(0, $facet['other']);

        //limit size of facet
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?facet:type=2');
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
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?facet:functions');
        $facet = $response['result']['facets'][0];
        $this->assertSame(10, count($facet['values'])); //default limit
        $this->assertSame('functions', $facet['field']);

        //test limit
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?facet:functions=2');
        $facet = $response['result']['facets'][0];
        $this->assertSame(2, count($facet['values']));
    }

    /**
     * @depends testSearchApi
     */
    public function testTopicsFacet()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?facet:topics');
        $facet = $response['result']['facets'][0];
        $this->assertSame(10, count($facet['values'])); //default limit
        $this->assertSame('topics', $facet['field']);

        //test limit
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?facet:topics=2');
        $facet = $response['result']['facets'][0];
        $this->assertSame(2, count($facet['values']));
    }

    /**
     * @depends testSearchApi
     */
    public function testRegistersFacet()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?facet:registers');
        $facet = $response['result']['facets'][0];
        $this->assertSame(6, count($facet['values']));
        $this->assertSame('registers', $facet['field']);

        //test limit
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?facet:registers=2');
        $facet = $response['result']['facets'][0];
        $this->assertSame(2, count($facet['values']));
    }

    /**
     * @depends testSearchApi
     */
    public function testFormatsFacet()
    {
        $response = $this->callJsonApi('GET', 'api/v1/resources/search?facet:formats');
        $facet = $response['result']['facets'][0];
        $this->assertSame(10, count($facet['values']));
        $this->assertSame('formats', $facet['field']);

        $response = $this->callJsonApi('GET', 'api/v1/resources/search?facet:formats=2');
        $facet = $response['result']['facets'][0];
        $this->assertSame(2, count($facet['values']));
    }

    /**
     * @depends testSearchApi
     */
    public function testAuthenticityFacet()
    {
        $response = $this->callJsonApi('GET', 'api/v1/resources/search?facet:authenticity');
        $facet = $response['result']['facets'][0];
        $this->assertSame(4, count($facet['values']));
        $this->assertSame('authenticity', $facet['field']);

        $response = $this->callJsonApi('GET', 'api/v1/resources/search?facet:authenticity=2');
        $facet = $response['result']['facets'][0];
        $this->assertSame(2, count($facet['values']));
    }

    /**
     * @depends testSearchApi
     */
    public function testGenresFacet()
    {
        $response = $this->callJsonApi('GET', 'api/v1/resources/search?facet:genres');
        $facet = $response['result']['facets'][0];
        $this->assertSame(10, count($facet['values']));
        $this->assertSame('genres', $facet['field']);

        $response = $this->callJsonApi('GET', 'api/v1/resources/search?facet:genres=2');
        $facet = $response['result']['facets'][0];
        $this->assertSame(2, count($facet['values']));
    }

    /**
     * @depends testSearchApi
     */
    public function testClientFacet()
    {
        $response = $this->callJsonApi('GET', 'api/v1/resources/search?facet:client');
        $facet = $response['result']['facets'][0];
        $this->assertSame(2, count($facet['values']));
        $this->assertSame('client', $facet['field']);

        $response = $this->callJsonApi('GET', 'api/v1/resources/search?facet:client=1');
        $facet = $response['result']['facets'][0];
        $this->assertSame(1, count($facet['values']));
    }

    /**
     * @depends testSearchApi
     */
    public function testClientUserFacet()
    {
        $response = $this->callJsonApi('GET', 'api/v1/resources/search?facet:clientUser');
        $facet = $response['result']['facets'][0];
        $this->assertSame(7, count($facet['values']));
        $this->assertSame('clientUser', $facet['field']);

        $response = $this->callJsonApi('GET', 'api/v1/resources/search?facet:clientUser=1');
        $facet = $response['result']['facets'][0];
        $this->assertSame(1, count($facet['values']));
    }

    /**
     * @depends testSearchApi
     */
    public function testLangaugeFacet()
    {
        $response = $this->callJsonApi('GET', 'api/v1/resources/search?facet:languages');
        $facet = $response['result']['facets'][0];
        $this->assertSame(7, count($facet['values']));
        $this->assertSame('languages', $facet['field']);

        $response = $this->callJsonApi('GET', 'api/v1/resources/search?facet:languages=1');
        $facet = $response['result']['facets'][0];
        $this->assertSame(1, count($facet['values']));
    }

    /**
     * @depends testSearchApi
     */
    public function testLicenseFacet()
    {
        $response = $this->callJsonApi('GET', 'api/v1/resources/search?facet:license');
        $facet = $response['result']['facets'][0];
        $this->assertSame(6, count($facet['values']));
        $this->assertSame('license', $facet['field']);

        $response = $this->callJsonApi('GET', 'api/v1/resources/search?facet:license=1');
        $facet = $response['result']['facets'][0];
        $this->assertSame(1, count($facet['values']));
    }


    /**
     * @depends testSearchApi
     */
    public function testMultipleFacets()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?facet:type&facet:topics');
        $this->assertSame(2, count($response['result']['facets']));
    }
}
