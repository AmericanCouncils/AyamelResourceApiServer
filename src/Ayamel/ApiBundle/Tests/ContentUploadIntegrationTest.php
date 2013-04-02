<?php

namespace Ayamel\ApiBundle\Tests;
use Ayamel\ApiBundle\TestCase;

class ContentUploadIntegrationTest extends TestCase
{
    
    public function testGetUploadUrl()
    {
        
    }

    public function testUploadContentAsRemoteFilesArray()
    {
        $data = array(
            'title' => 'empty'
        );
        
        $response = $this->getJson('POST', '/api/v1/resources', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode($data));
        $this->assertFalse(isset($response['resource']['content']));
        
        $resourceId = $response['resource']['id'];
        $apiPath = substr($response['content_upload_url'], strlen('http://localhost'));
        
        //TODO: make sure these are validated properly w/ object validator
        //TODO: there really needs to be validation on the attribute fields as well
        $data = array(
            'remoteFiles' => array(
                array(
                    'downloadUri' => 'http://example.com/files/test.mp4',
                    'streamUri' => 'http://streaming.example.com/test',
                    'representation' => 'original',
                    'quality' => 1,
                    'mime' => 'video/mp4',
                    'mimeType' => 'video/mp4; encoding=binary',
                    'attributes' => array(
                        'key' => 'val',
                        'foo' => 'bar'
                    )
                ),
                array(
                    'downloadUri' => 'http://example.com/files/test.low.mp4',
                    'streamUri' => 'http://streaming.example.com/test.low',
                    'representation' => 'transcoding',
                    'quality' => 0,
                    'mime' => 'video/mp4',
                    'mimeType' => 'video/mp4; encoding=binary',
                    'attributes' => array(
                        'key' => 'val',
                        'foo' => 'bar'
                    )
                )
            )
        );
        $response = $this->getJson('POST', $apiPath, array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode($data));
        
        $this->assertSame(200, $response['response']['code']);
        $this->assertSame($data['remoteFiles'], $response['resource']['content']['files']);
    }
    
    public function testUploadContentAsFile()
    {
        
    }
    
}
