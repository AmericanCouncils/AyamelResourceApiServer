<?php

use Ayamel\ApiBundle\ApiTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Process\Process;

/**
 * This tests initiating the transcode for a Resource by:
 * 
 * - using the TranscodingManager directly
 * - running the TranscodeResourceCommand from the CLI
 * - asynchronously via the RabbitMQ consumer
 *
 * @package AyamelTranscodingBundle
 * @author Evan Villemez
 */
class BasicTranscodeTest extends ApiTestCase
{
    
    public function testTranscodeManagerTranscodeResource()
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
        $testFilePath = __DIR__."/sample_files/lorem.txt";
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

        $resource = $this->getContainer()->get('ayamel.transcoding.manager')->transcodeResource($resourceId);
        $this->assertTrue(isset($resource->content));
        $files = $resource->content->getFiles();
        $this->assertSame(2, count($files));
    }
    
    public function testTranscodeResourceCommand()
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
        $testFilePath = __DIR__."/sample_files/lorem.txt";
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

        //now run transcode command directly, the --force flag makes it run immediately, instead
        //of dispatching the transcode job into the queue to be handled by rabbit
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
    }
    
    public function testTranscodeViaRabbitMQ()
    {
        //start process asynchronously
        $rabbitProcess = new Process('ayamel rabbitmq:consumer transcoding --messages=1');
        $rabbitProcess->start();
        $this->assertTrue($rabbitProcess->isRunning());
        
        //upload resource
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

        //upload the test file
        $testFilePath = __DIR__."/sample_files/lorem.txt";
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
        
        //wait for the rabbit process to exit after it has
        //transcoded the resource, then make some assertions
        $tester = $this;
        $rabbitProcess->wait(function() use ($tester, $resourceId, $rabbitProcess) {
            $tester->assertFalse($rabbitProcess->isRunning());
            $data = $tester->getJson('GET', '/api/v1/resources/'.$resourceId);
            $tester->assertSame(200, $data['response']['code']);
            $tester->assertSame('normal', $data['resource']['status']);
            $tester->assertTrue(isset($data['resource']['content']['files']));
            $files = $data['resource']['content']['files'];
            $tester->assertSame(2, count($files));
        });
    }
}
