<?php

namespace Ayamel\MediaInfoBundle\Tests;

use Ayamel\ApiBundle\ApiTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaInfoApiIntegrationTest extends ApiTestCase
{

    public function testUploadFileFillsInAttributes()
    {
        $mediainfoPath = $this->getContainer()->getParameter('ac_media_info.path');
        if (!file_exists($mediainfoPath)) {
            $this->markTestSkipped("mediainfo cli utility is not accessible on this system.");
        }

        $apiKey = '45678isafgd56789asfgdhf4567';
        $json = $this->getJson('POST', '/api/v1/resources?_key='.$apiKey, [], [], array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array(
            'title' => 'audio mediainfo test',
            'type' => 'audio'
        )));

        $this->assertSame(201, $json['response']['code']);
        $uploadUrl = substr($json['contentUploadUrl'], strlen('http://localhost'));

        $testFilePath = __DIR__."/subclip.mp3";
        $uploadedFile = new UploadedFile(
            $testFilePath,
            'subclip.mp3',
            'audio/mpeg',
            filesize($testFilePath)
        );

        $resp = $this->getJson('POST', $uploadUrl.'?_key='.$apiKey, [], array('file' => $uploadedFile));

        $this->assertSame(202, $resp['response']['code']);
        $this->assertTrue(isset($resp['resource']['content']['files']));
        $this->assertTrue(1 === count($resp['resource']['content']['files']));
        $file = $resp['resource']['content']['files'][0];

        //the upload file should have attributes set by mediainfo
        $this->assertSame('audio/mpeg', $file['mimeType']);
        $this->assertSame(filesize($testFilePath), $file['bytes']);
        $this->assertTrue(isset($file['attributes']));
        $this->assertSame(2, $file['attributes']['channels']);
        $this->assertSame(1, $file['attributes']['duration']);
        $this->assertSame(64000, $file['attributes']['bitrate']);
    }

}
