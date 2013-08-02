<?php

namespace Ayamel\ApiBundle\Tests;
use Ayamel\ApiBundle\ApiTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ContentUploadIntegrationTest extends ApiTestCase
{

    //a series of test as this is a one-time-use url
    public function testGetUploadUrl()
    {
        $data = array(
            'title' => 'test',
            'type' => 'data'
        );

        $response = $this->getJson('POST', '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
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

        //no apikey
        $response = $this->getResponse('POST', $uploadUrl);
        $this->assertSame(401, $response->getStatusCode());

        //invalid key
        $response = $this->getResponse('POST', $uploadUrl.'?_key=55678isafgd56789asfgdhf4568');
        $this->assertSame(403, $response->getStatusCode());

    }

    public function testUploadContentAsRemoteFilesArray()
    {
        $data = array(
            'title' => 'test',
            'type' => 'data'
        );

        $response = $this->getJson('POST', '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
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
        $response = $this->getJson('POST', $apiPath.'?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
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

        $response = $this->getJson('POST', '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
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

        $content = $this->getJson('POST', $uploadUrl.'?_key=45678isafgd56789asfgdhf4567', array(), array('file' => $uploadedFile));

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

    public function testUploadAndTranscodeFile()
    {
        $data = array(
            'title' => 'test',
            'type' => 'data'
        );

        $response = $this->getJson('POST', '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode($data));
        $this->assertSame(201, $response['response']['code']);
        $this->assertFalse(isset($response['resource']['content']));
        $this->assertSame('awaiting_content', $response['resource']['status']);
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

        $content = $this->getJson('POST', $uploadUrl.'?_key=45678isafgd56789asfgdhf4567', array(), array('file' => $uploadedFile));

        $this->assertSame(202, $content['response']['code']);
        $this->assertSame('awaiting_processing', $content['resource']['status']);
        $this->assertSame($data['title'], $content['resource']['title']);
        $this->assertTrue(isset($content['resource']['content']));
        $this->assertTrue(isset($content['resource']['content']['files']));
        $this->assertSame(1, count($content['resource']['content']['files']));
        $data = $content['resource']['content']['files'][0];
        $this->assertTrue(isset($data['downloadUri']));
        $this->assertSame('text/plain', $data['mime']);
        $this->assertSame('text/plain', $data['mimeType']);
        $this->assertSame(filesize($testFilePath), $data['bytes']);

        //now run transcode command directly
        $this->runCommand(sprintf('api:resource:transcode %s --force', $resourceId));

        //now get resource - expect 2 files and changed status
        $json = $this->getJson('GET', '/api/v1/resources/'.$resourceId.'?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ));

        $expected = array(
            'mime' => 'text/plain',
            'mimeType' => 'text/plain',
            'representation' => 'transcoding',
            'quality' => 0,
            'bytes' => filesize($testFilePath)
        );

        $this->assertSame(200, $json['response']['code']);
        $this->assertSame('normal', $json['resource']['status']);
        $this->assertSame(2, count($json['resource']['content']['files']));
        $transcoded = $json['resource']['content']['files'][1];
        $this->assertSame($expected['mime'], $transcoded['mime']);
        $this->assertSame($expected['mimeType'], $transcoded['mimeType']);
        $this->assertSame($expected['representation'], $transcoded['representation']);
        $this->assertSame($expected['quality'], $transcoded['quality']);
        $this->assertSame($expected['bytes'], $transcoded['bytes']);
        $this->assertTrue(isset($transcoded['downloadUri']));

        //hit one-time url again to make sure it expired
        $response = $this->getResponse('POST', $uploadUrl.'?_key=45678isafgd56789asfgdhf4567', array(), array('file' => $uploadedFile));
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testUploadContentAsGenericUri()
    {
        $data = array(
            'title' => 'test',
            'type' => 'data'
        );

        $response = $this->getJson('POST', '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode($data));
        $this->assertFalse(isset($response['resource']['content']));
        $this->assertSame('awaiting_content', $response['resource']['status']);

        $resourceId = $response['resource']['id'];
        $apiPath = substr($response['contentUploadUrl'], strlen('http://localhost'));

        $response = $this->getJson('POST', $apiPath.'?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
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
