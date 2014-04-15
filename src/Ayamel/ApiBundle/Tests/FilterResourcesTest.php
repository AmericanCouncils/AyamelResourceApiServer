<?php

namespace Ayamel\ApiBundle\Tests;

class FilterResourcesTest extends FixturedTestCase
{

    public function testShowResources()
    {
        $this->markTestIncomplete();
        //get w/out apikey
        //test total
        
        //get w/ api key
        //test total
    }

    /**
     * @depends testShowResources
     */
    public function testLimit()
    {

    }

    /**
     * @depends testShowResources
     */
    public function testSkip()
    {

    }

    /**
     * @depends testShowResources
     */
    public function testFilterType()
    {
        $this->markTestIncomplete();
    }

    /**
     * @depends testShowResources
     */
    public function testFilterClient()
    {
        $this->markTestIncomplete();
    }

    /**
     * @depends testShowResources
     */
    public function testFilterClientUser()
    {
        $this->markTestIncomplete();
    }

    /**
     * @depends testShowResources
     */
    public function testFilterId()
    {
        $this->markTestIncomplete();
    }

    /**
     * @depends testShowResources
     */
    public function testFilterStatus()
    {
        $this->markTestIncomplete();
    }

    /**
     * @depends testShowResources
     */
    public function testFilterLanguage()
    {
        $this->markTestIncomplete();
    }

    /**
     * @depends testShowResources
     */
    public function testMultipleFilters()
    {
        //lang and type
        $this->markTestIncomplete();
    }

}
