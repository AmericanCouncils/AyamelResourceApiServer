<?php

namespace Ayamel\ApiBundle\Tests;

class FilterResourcesTest extends FixturedTestCase
{

    public function testShowResources()
    {
        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2');
        $this->assertSame(30, $res['total']);
        $this->assertSame(20, count($res['resources']));
        $this->assertSame(20, $res['limit']);
        $this->assertSame(0, $res['skip']);
    }

    public function testShowResourcesEnforcesVisibility()
    {
        //anonymous request
        $res = $this->callJsonApi('GET', '/api/v1/resources');
        $this->assertSame(16, $res['total']);
        foreach ($res['resources'] as $res) {
            $this->assertTrue(is_null($res['visibility']) || empty($res['visibility']));
        }

        //with client
        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2');
        $this->assertSame(30, $res['total']);
        $this->assertSame(20, count($res['resources']));
        foreach ($res['resources'] as $res) {
            $this->assertTrue(
                is_null($res['visibility']) ||
                empty($res['visibility']) ||
                in_array('another-test-client', $res['visibility'])
            );
        }
    }

    /**
     * @depends testShowResources
     */
    public function testLimit()
    {
        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&limit=5');
        $this->assertSame(30, $res['total']);
        $this->assertSame(5, count($res['resources']));
        $this->assertSame(5, $res['limit']);
    }

    /**
     * @depends testShowResources
     */
    public function testSkip()
    {
        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&skip=25');
        $this->assertSame(30, $res['total']);
        $this->assertSame(5, count($res['resources']));
        $this->assertSame(25, $res['skip']);
    }

    /**
     * @depends testShowResources
     */
    public function testFilterType()
    {
        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&type=video');
        $this->assertSame(6, $res['total']);
        $this->assertSame(6, count($res['resources']));
        foreach($res['resources'] as $res) {
            $this->assertSame('video', $res['type']);
        }

        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&type=audio');
        $this->assertSame(5, $res['total']);
        $this->assertSame(5, count($res['resources']));
        foreach($res['resources'] as $res) {
            $this->assertSame('audio', $res['type']);
        }

        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&type=audio,video');
        $this->assertSame(11, $res['total']);
        $this->assertSame(11, count($res['resources']));
        foreach($res['resources'] as $res) {
            $this->assertTrue(in_array($res['type'], ['audio','video']));
        }
    }

    /**
     * @depends testShowResources
     */
    public function testFilterClient()
    {
        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2');
        foreach ($res['resources'] as $res) {
            var_dump($res['client']);
        }
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
    public function testFilterPublic()
    {
        //whether or not "visibility" is null/empty
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
