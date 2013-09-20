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

        $this->markTestSkipped();

        return $ids;
    }

    /**
     * @depends testSetupDummyResources
     */
    public function testSimpleSearchApi($ids)
    {
        $this->markTestSkipped();
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
