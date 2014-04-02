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
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=dolor');
        $code = $response['response']['code'];
        $this->assertSame(200, $code);
        $this->assertSame(14, count($response['hits']));
    }

    /**
     * @depends testSimpleSearchApi
     */
    public function testTypeFilter()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=dolor&filter:type=audio');
        $this->assertSame(4, count($response['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=dolor&filter:type=video');
        $this->assertSame(4, count($response['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=dolor&filter:type=video,audio');
        $this->assertSame(8, count($response['hits']));
    }

    /**
     * @depends testSimpleSearchApi
     */
    public function testClientFilter()
    {
        $this->markTestSkipped();
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=dolor&filter:client=another-test-client');
        $this->assertSame(3, count($response['hits']));
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
    public function testSubjectDomainsFilter()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=dolor&filter:subjectDomains=science');
        $this->assertSame(1, count($response['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=dolor&filter:subjectDomains=weather');
        $this->assertSame(3, count($response['hits']));

        //OR
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=dolor&filter:subjectDomains=science,weather');
        $this->assertSame(4, count($response['hits']));

        //AND
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=dolor&filter:subjectDomains[]=science&filter:subjectDomains[]=weather');
        $this->assertSame(0, count($response['hits']));
    }

    /**
     * @depends testSimpleSearchApi
     */
    public function testFunctionalDomainsFilter()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=dolor&filter:functionalDomains=informative');
        $this->assertSame(7, count($response['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=dolor&filter:functionalDomains=presentational');
        $this->assertSame(7, count($response['hits']));

        //OR
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=dolor&filter:functionalDomains=presentational,informative');
        $this->assertSame(11, count($response['hits']));

        //AND
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=dolor&filter:functionalDomains[]=presentational&filter:functionalDomains[]=informative');
        $this->assertSame(3, count($response['hits']));
    }

    /**
     * @depends testSimpleSearchApi
     */
    public function testRegistersFilter()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=dolor&filter:registers=formal');
        $this->assertSame(5, count($response['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=dolor&filter:registers=intimate');
        $this->assertSame(5, count($response['hits']));

        //OR
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=dolor&filter:registers=formal,intimate');
        $this->assertSame(10, count($response['hits']));

        //AND
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=dolor&filter:registers[]=formal&filter:registers[]=intimate');
        $this->assertSame(0, count($response['hits']));
    }

    /**
     * @depends testSimpleSearchApi
     */
    public function testMultipleFilters()
    {
        $this->markTestSkipped();
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

    /**
     * @depends testSimpleSearchApi
     */
    public function testSimpleSearchApiHidesUnauthorizedResources($ids)
    {
        //  if anon, where resource.visibility null
        //  if known, where resource.visibility null OR currentClient in resource.visibility
        $this->markTestSkipped();
    }
}
