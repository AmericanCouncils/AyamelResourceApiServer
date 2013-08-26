<?php

use Ayamel\ApiBundle\ApiTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Process\Process;

class VideoTranscodeTest extends ApiTestCase
{

    /**
     * high quality original, hits all presets
     *
     * @group transcoding
     */
    public function testTranscodeHighQualityVideo()
    {
        //$this->markTestSkipped('must determine proper presets first');
    }

    /**
     * low quality original, most presets filtered out
     *
     * @group transcoding
     */
    public function testTranscodeLowQualityVideo()
    {
        //$this->markTestSkipped();
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
        $testFilePath = __DIR__."/sample_files/low.mp4";
        $uploadedFile = new UploadedFile(
            $testFilePath,
            'low.mp4',
            'video/mp4',
            filesize($testFilePath)
        );

        $content = $this->getJson('POST', $uploadUrl.'?_key=45678isafgd56789asfgdhf4567', array(), array('file' => $uploadedFile));
        $this->assertSame(202, $content['response']['code']);
        $this->assertSame('awaiting_processing', $content['resource']['status']);
        $this->assertSame($data['title'], $content['resource']['title']);
        $this->assertTrue(isset($content['resource']['content']));
        $this->assertTrue(isset($content['resource']['content']['files']));
        $this->assertSame(1, count($content['resource']['content']['files']));

        $resource = $this->getContainer()->get('ayamel.transcoding.manager')->transcodeResource($resourceId);
        $this->assertTrue(isset($resource->content));
        $files = $resource->content->getFiles();
        $this->assertSame(5, count($files));
        foreach ($files as $file) {
            $attrs = $file->getAttributes();
            $this->assertSame('4:3', $attrs['aspectRatio']);
            $this->assertTrue($attrs['frameSize']['height'] < 720);
        }
    }
}
