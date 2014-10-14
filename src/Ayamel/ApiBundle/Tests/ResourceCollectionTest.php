<?php

namespace Ayamel\ApiBundle\Tests;
use Ayamel\ApiBundle\ApiTestCase;

class ResourceCollectionTest extends ApiTestCase
{
    public function testCreateResourceCollection()
    {
        $data = array(
            'title' => 'yo dawg',
            'type' => 'collection'
        );
        $return = $this->getJson("POST", '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode($data));

        $this->assertSame(201, $return['response']['code']);

        $uploadUrl = substr($return['contentUploadUrl'], strlen('http://localhost'));

        return $uploadUrl;
    }

    /**
     * @depends testCreateResourceCollection
     */
    public function testCollectionDoesNotAllowContentUploading($uploadUrl)
    {
        $return = $this->getJson('POST', $uploadUrl.'?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array(
            'uri' => 'http://www.google.com/'
        )));

        $this->assertSame(400, $return['response']['code']);
    }
}
