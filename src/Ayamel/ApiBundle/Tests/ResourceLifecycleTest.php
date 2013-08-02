<?php

namespace Ayamel\ApiBundle\Tests;
use Ayamel\ApiBundle\ApiTestCase;

class ResourceLifecycleTest extends ApiTestCase
{
    public function testResourceLifecycle()
    {
        //create it
        $json = $this->getJson("POST", '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array('title'=>'foo', 'type'=>'data')));
        $this->assertSame(201, $json['response']['code']);
        $this->assertSame('awaiting_content', $json['resource']['status']);
        $this->assertFalse(isset($json['resource']['content']));
        $id = $json['resource']['id'];
        $uploadUrl = $json['contentUploadUrl'];

        //get it
        $json = $this->getJson('GET', '/api/v1/resources/'.$id.'?_key=45678isafgd56789asfgdhf4567');
        $this->assertSame(200, $json['response']['code']);
        $this->assertSame('awaiting_content', $json['resource']['status']);
        $this->assertFalse(isset($json['resource']['content']));

        //modify it
        $json = $this->getJson("PUT", '/api/v1/resources/'.$id.'?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array('keywords'=>'foo,bar,baz')));
        $this->assertSame(200, $json['response']['code']);
        $this->assertSame('awaiting_content', $json['resource']['status']);
        $this->assertSame('foo,bar,baz', $json['resource']['keywords']);
        $this->assertFalse(isset($json['resource']['content']));

        //get it
        $json = $this->getJson('GET', '/api/v1/resources/'.$id.'?_key=45678isafgd56789asfgdhf4567');
        $this->assertSame(200, $json['response']['code']);
        $this->assertSame('awaiting_content', $json['resource']['status']);
        $this->assertSame('foo,bar,baz', $json['resource']['keywords']);
        $this->assertFalse(isset($json['resource']['content']));

        //upload content
        $remoteFiles = array(
            array(
                'downloadUri' => 'http://www.google.com/',
                'representation' => 'original',
                'quality' => 0,
                'mimeType' => 'text/html',
                'mime' => 'text/html',
                'bytes' => 345336
            )
        );
        $apiPath = substr($uploadUrl, strlen('http://localhost'));
        $json = $this->getJson('POST', $apiPath.'?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array('remoteFiles' => $remoteFiles)));
        $this->assertSame(200, $json['response']['code']);
        $this->assertSame('normal', $json['resource']['status']);
        $this->assertSame('foo,bar,baz', $json['resource']['keywords']);
        $this->assertTrue(isset($json['resource']['content']));
        $this->assertSame(1, count($json['resource']['content']));

        //get it
        $json = $this->getJson('GET', '/api/v1/resources/'.$id.'?_key=45678isafgd56789asfgdhf4567');
        $this->assertSame(200, $json['response']['code']);
        $this->assertSame('normal', $json['resource']['status']);
        $this->assertSame('foo,bar,baz', $json['resource']['keywords']);
        $this->assertTrue(isset($json['resource']['content']));
        $this->assertSame(1, count($json['resource']['content']));

        //modify it
        $json = $this->getJson("PUT", '/api/v1/resources/'.$id.'?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array('keywords'=>'foo,bar,baz,qux')));
        $this->assertSame(200, $json['response']['code']);
        $this->assertSame('normal', $json['resource']['status']);
        $this->assertSame('foo,bar,baz,qux', $json['resource']['keywords']);
        $this->assertTrue(isset($json['resource']['content']));
        $this->assertSame(1, count($json['resource']['content']));

        //get it
        $json = $this->getJson('GET', '/api/v1/resources/'.$id.'?_key=45678isafgd56789asfgdhf4567');
        $this->assertSame(200, $json['response']['code']);
        $this->assertSame('normal', $json['resource']['status']);
        $this->assertSame('foo,bar,baz,qux', $json['resource']['keywords']);
        $this->assertTrue(isset($json['resource']['content']));
        $this->assertSame(1, count($json['resource']['content']));

        //delete it
        $json = $this->getJson("DELETE", '/api/v1/resources/'.$id.'?_key=45678isafgd56789asfgdhf4567');
        $this->assertSame(200, $json['response']['code']);
        $this->assertSame('deleted', $json['resource']['status']);
        $this->assertTrue(isset($json['resource']['dateDeleted']));
        $this->assertFalse(isset($json['resource']['content']));

        //get it
        $json = $this->getJson('GET', '/api/v1/resources/'.$id.'?_key=45678isafgd56789asfgdhf4567');
        $this->assertSame(410, $json['response']['code']);
        $this->assertSame('deleted', $json['resource']['status']);
        $this->assertTrue(isset($json['resource']['dateDeleted']));
        $this->assertFalse(isset($json['resource']['content']));
    }
}
