<?php

namespace Ayamel\ApiBundle\Tests;
use Ayamel\ApiBundle\ApiTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ContentUploadIntegrationTest extends ApiTestCase
{

    //a series of tests as this is a one-time-use url
    public function testGetUploadUrl()
    {
        $data = array(
            'title' => 'test',
            'type' => 'data'
        );

        $response = $this->getJson('POST', '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode($data));

        $this->assertSame(201, $response['response']['code']);
        $this->assertFalse(isset($response['resource']['content']));

        $resourceId = $response['resource']['id'];
        $apiPath = substr($response['contentUploadUrl'], strlen('http://localhost'));

        //hit the path with empty request, expect 422 (unprocessable) - then 401 on subsequent requests
        $response = $this->getResponse('POST', $apiPath.'?_key=45678isafgd56789asfgdhf4567');
        $this->assertSame(422, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame(422, $content['response']['code']);

        $response = $this->getResponse('POST', $apiPath);
        $this->assertSame(401, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame(401, $content['response']['code']);

        //now get a new one-time url
        $response = $this->getResponse('GET', '/api/v1/resources/'.$resourceId."/request-upload-url?_key=45678isafgd56789asfgdhf4567");
        $this->assertSame(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame(200, $content['response']['code']);
        $this->assertTrue(isset($content['contentUploadUrl']));
        $uploadUrl = substr($content['contentUploadUrl'], strlen('http://localhost'));

        $response = $this->getResponse('POST', $uploadUrl.'?_key=45678isafgd56789asfgdhf4567');
        $this->assertSame(422, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame(422, $content['response']['code']);

        $response = $this->getResponse('POST', $uploadUrl.'?_key=45678isafgd56789asfgdhf4567');
        $this->assertSame(401, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame(401, $content['response']['code']);

        //deny access
        $response = $this->getResponse('GET', '/api/v1/resources/'.$resourceId."/request-upload-url?_key=45678isafgd56789asfgdhf4567");
        $this->assertSame(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame(200, $content['response']['code']);
        $this->assertTrue(isset($content['contentUploadUrl']));
        $uploadUrl = substr($content['contentUploadUrl'], strlen('http://localhost'));
    }

    public function testUploadContentAsRemoteFilesArray()
    {
        $data = array(
            'title' => 'test',
            'type' => 'data'
        );

        $response = $this->getJson('POST', '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode($data));
        $this->assertSame(201, $response['response']['code']);
        $this->assertFalse(isset($response['resource']['content']));
        $this->assertSame('awaiting_content', $response['resource']['status']);

        $resourceId = $response['resource']['id'];
        $apiPath = substr($response['contentUploadUrl'], strlen('http://localhost'));

        $data = array(
            'remoteFiles' => array(
                array(
                    'downloadUri' => 'https://www.google.com/',             //api actually tries to query the file, so this url is likely to work in tests
                    'streamUri' => 'http://streaming.example.com/test',
                    'bytes' => 23456,
                    'representation' => 'original',
                    'quality' => 1,
                    'mime' => 'video/mp4; encoding=binary',
                    'mimeType' => 'video/mp4',
                    'attributes' => array(
                        'frameSize' => array(
                            'height' => 1080,
                            'width' => 1920
                        ),
                    )
                ),
                array(
                    'downloadUri' => 'https://www.google.com/',             //api actually tries to query the file, so this url is likely to work in tests
                    'streamUri' => 'http://streaming.example.com/test.low',
                    'bytes' => 23456,
                    'representation' => 'transcoding',
                    'quality' => 0,
                    'mime' => 'video/mp4; encoding=binary',
                    'mimeType' => 'video/mp4',
                    'attributes' => array(
                        'frameSize' => array(
                            'height' => 400,
                            'width' => 600
                        ),
                    )
                )
            )
        );
        $response = $this->getJson('POST', $apiPath.'?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode($data));

        $this->assertSame(200, $response['response']['code']);
        $this->assertSame($data['remoteFiles'], $response['resource']['content']['files']);
        $this->assertSame('normal', $response['resource']['status']);
    }

    public function testUploadContentAsFile()
    {
        //get content upload url
        $data = array(
            'title' => 'test',
            'type' => 'data'
        );

        $response = $this->getJson('POST', '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode($data));
        $this->assertSame(201, $response['response']['code']);
        $this->assertFalse(isset($response['resource']['content']));
        $resourceId = $response['resource']['id'];
        $uploadUrl = substr($response['contentUploadUrl'], strlen('http://localhost'));

        //create uploaded file
        $testFilePath = __DIR__."/files/resource_test_files/lorem.txt";
        $uploadedFile = new UploadedFile(
            $testFilePath,
            'lorem.txt',
            'text/plain',
            filesize($testFilePath)
        );

        $content = $this->getJson('POST', $uploadUrl.'?_key=45678isafgd56789asfgdhf4567', [], array('file' => $uploadedFile));
        $this->assertSame(202, $content['response']['code']);
        $this->assertSame('awaiting_processing', $content['resource']['status']);
        $this->assertSame($data['title'], $content['resource']['title']);
        $this->assertTrue(isset($content['resource']['content']));
        $this->assertTrue(isset($content['resource']['content']['files']));
        $data = $content['resource']['content']['files'][0];
        $this->assertTrue(isset($data['downloadUri']));
        $this->assertSame('text/plain', $data['mime']);
        $this->assertSame('text/plain', $data['mimeType']);
        $this->assertSame(filesize($testFilePath), $data['bytes']);
    }

    public function testUploadContentAsGenericUri()
    {
        $data = array(
            'title' => 'test',
            'type' => 'data'
        );

        $response = $this->getJson('POST', '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode($data));
        $this->assertSame(201, $response['response']['code']);
        $this->assertFalse(isset($response['resource']['content']));
        $this->assertSame('awaiting_content', $response['resource']['status']);

        $resourceId = $response['resource']['id'];
        $apiPath = substr($response['contentUploadUrl'], strlen('http://localhost'));

        $response = $this->getJson('POST', $apiPath.'?_key=45678isafgd56789asfgdhf4567', [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array(
            'uri' => 'http://www.google.com/'
        )));

        $this->assertSame(200, $response['response']['code']);
        $this->assertTrue(isset($response['resource']['content']['files']));
        $this->assertTrue(0 < count(isset($response['resource']['content']['files'])));
        $this->assertSame('normal', $response['resource']['status']);
    }
}
