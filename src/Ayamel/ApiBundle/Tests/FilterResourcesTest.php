<?php

namespace Ayamel\ApiBundle\Tests;

class FilterResourcesTest extends FixturedTestCase
{

    public function testShowResources()
    {
        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2');
        $this->assertSame(36, $res['total']);
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
        $this->assertSame(36, $res['total']);
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
        $this->assertSame(36, $res['total']);
        $this->assertSame(5, count($res['resources']));
        $this->assertSame(5, $res['limit']);
    }

    /**
     * @depends testShowResources
     */
    public function testSkip()
    {
        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&skip=30');
        $this->assertSame(36, $res['total']);
        $this->assertSame(6, count($res['resources']));
        $this->assertSame(30, $res['skip']);
    }

    /**
     * @depends testShowResources
     */
    public function testFilterType()
    {
        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&type=video');
        $this->assertSame(7, $res['total']);
        $this->assertSame(7, count($res['resources']));
        foreach($res['resources'] as $res) {
            $this->assertSame('video', $res['type']);
        }

        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&type=audio');
        $this->assertSame(8, $res['total']);
        $this->assertSame(8, count($res['resources']));
        foreach($res['resources'] as $res) {
            $this->assertSame('audio', $res['type']);
        }

        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&type=audio,video');
        $this->assertSame(15, $res['total']);
        $this->assertSame(15, count($res['resources']));
        foreach($res['resources'] as $res) {
            $this->assertTrue(in_array($res['type'], ['audio','video']));
        }
    }

    /**
     * @depends testShowResources
     */
    public function testFilterClient()
    {
        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&client=test-client');
        $this->assertSame(6, $res['total']);
        foreach ($res['resources'] as $res) {
            $this->assertSame('test-client', $res['client']['id']);
        }

        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&client=another-test-client');
        $this->assertSame(30, $res['total']);
        foreach ($res['resources'] as $res) {
            $this->assertSame('another-test-client', $res['client']['id']);
        }

        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&client=another-test-client,test-client');
        $this->assertSame(36, $res['total']);
        foreach ($res['resources'] as $res) {
            $this->assertTrue(in_array($res['client']['id'], ['test-client','another-test-client']));
        }
    }

    /**
     * @depends testShowResources
     */
    public function testFilterClientUser()
    {
        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&clientUser=user-1');
        $this->assertSame(2, $res['total']);

        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&clientUser=user-2');
        $this->assertSame(3, $res['total']);

        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&clientUser=user-1,user-2');
        $this->assertSame(5, $res['total']);        
    }

    /**
     * @depends testShowResources
     */
    public function testFilterId()
    {
        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2');
        $ids = [$res['resources'][0]['id']];
        $ids[] = $res['resources'][5]['id'];

        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&id='.$ids[0]);
        $this->assertSame(1, $res['total']);
        $this->assertSame($ids[0], $res['resources'][0]['id']);

        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&id='.$ids[1]);
        $this->assertSame(1, $res['total']);
        $this->assertSame($ids[1], $res['resources'][0]['id']);

        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&id='.implode(',', $ids));
        $this->assertSame(2, $res['total']);
        foreach ($res['resources'] as $res) {
            $this->assertTrue(in_array($res['id'], $ids));
        }
    }

    /**
     * @depends testShowResources
     */
    public function testFilterStatus()
    {
        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&status=normal');
        $this->assertSame(10, $res['total']);

        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&status=awaiting_processing');
        $this->assertSame(14, $res['total']);

        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&status=normal,awaiting_processing');
        $this->assertSame(24, $res['total']);
    }

    /**
     * @depends testShowResources
     */
    public function testFilterLanguage()
    {
        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&languages=en');
        $this->assertSame(17, $res['total']);
        foreach ($res['resources'] as $res) {
            $this->assertTrue(in_array('en', $res['languages']['bcp47']));
        }

        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&languages=rus');
        $this->assertSame(17, $res['total']);
        foreach ($res['resources'] as $res) {
            $this->assertTrue(in_array('rus', $res['languages']['iso639_3']));
        }

        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&languages=en,rus');
        $this->assertSame(27, $res['total']);
        foreach ($res['resources'] as $res) {
            $this->assertTrue(in_array('en', $res['languages']['bcp47']) || in_array('rus', $res['languages']['iso639_3']));
        }
    }

    /**
     * @depends testShowResources
     */
    public function testFilterPublic()
    {
        //all visible resources for client, visibility doesn't matter
        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2');
        $this->assertSame(36, $res['total']);
        foreach ($res['resources'] as $res) {
            $this->assertTrue(
                is_null($res['visibility']) ||
                empty($res['visibility']) ||
                in_array('another-test-client', $res['visibility'])
            );
        }

        //only public resources
        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&public=true');
        $this->assertSame(16, $res['total']);
        foreach ($res['resources'] as $res) {
            $this->assertTrue(is_null($res['visibility']) || empty($res['visibility']));
        }

        //only non-public
        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&public=false');
        $this->assertSame(20, $res['total']);
        foreach ($res['resources'] as $res) {
            $this->assertTrue(in_array('another-test-client', $res['visibility']));
        }

        //bad value
        $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&public=fooo',[
            'expectedCode' => 400
        ]);
    }

    /**
     * @depends testShowResources
     */
    public function testMultipleFilters()
    {
        //by type
        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&type=audio');
        $this->assertSame(8, $res['total']);

        //plus status
        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&type=audio&status=normal');
        $this->assertSame(3, $res['total']);
        foreach ($res['resources'] as $res) {
            $this->assertSame('audio', $res['type']);
            $this->assertSame('normal', $res['status']);
        }

        //plus client user id
        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2&type=audio&status=normal&clientUser=user-10');
        $this->assertSame(2, $res['total']);
        foreach ($res['resources'] as $res) {
            $this->assertSame('audio', $res['type']);
            $this->assertSame('normal', $res['status']);
            $this->assertSame('user-10', $res['clientUser']['id']);
        }
    }

    /**
     * @depends testShowResources
     */
    public function testDoesNotShowDeletedResources()
    {
        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2');
        $id = $res['resources'][0]['id'];

        $res = $this->callJsonApi('DELETE', '/api/v1/resources/'.$id.'?_key=key-for-test-client-2');
        $this->assertSame(200, $res['response']['code']);

        $res = $this->callJsonApi('GET', '/api/v1/resources?_key=key-for-test-client-2');
        foreach ($res['resources'] as $res) {
            $this->assertFalse('deleted' === $res['status']);
        }
    }

}
