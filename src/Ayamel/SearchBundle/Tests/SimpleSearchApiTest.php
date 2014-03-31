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
        //assert that "q" param is required
        $this->markTestSkipped();
    }

    public function testSimpleSearchApi()
    {
        //hit ayamel api
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=dolor');
        $code = $response['response']['code'];
        $this->assertSame(200, $code);
        $this->assertSame(3, count($response['hits']));
    }

    /**
     * @depends testSimpleSearchApi
     */
    public function testTypeFilter()
    {
        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=dolor&filter:type=audio');
        $this->assertSame(2, count($response['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=dolor&filter:type=video');
        $this->assertSame(1, count($response['hits']));

        $response = $this->callJsonApi('GET', '/api/v1/resources/search?q=dolor&filter:type=video,audio');
        $this->assertSame(3, count($response['hits']));
    }

    /**
     * @depends testSimpleSearchApi
     */
    public function testClientFilter()
    {
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
        $this->markTestSkipped();
    }

    /**
     * @depends testSimpleSearchApi
     */
    public function testFunctionalDomainsFilter()
    {
        $this->markTestSkipped();
    }

    /**
     * @depends testSimpleSearchApi
     */
    public function testRegistersFilter()
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
