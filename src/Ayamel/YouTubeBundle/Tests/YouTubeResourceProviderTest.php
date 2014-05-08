<?php

namespace Ayamel\YouTubeBundle\Tests;

use Ayamel\YouTubeBundle\YouTubeResourceProvider;
use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\Origin;
use Ayamel\ResourceBundle\Document\OEmbed;
use Ayamel\ApiBundle\ApiTestCase;

class YouTubeResourceProviderTest extends ApiTestCase
{
    public function testHandleScheme()
    {
        $provider = new YouTubeResourceProvider();
        $this->assertTrue($provider->handlesScheme('youtube'));
    }

    public function testDeriveYouTubeResource()
    {
        $provider = new YouTubeResourceProvider();
        $r = $provider->createResourceFromUri('youtube://txqiwrbYGrs');
        $this->assertTrue($r instanceof Resource);
        $this->assertSame('David After Dentist', $r->getTitle());
        $this->assertSame('video', $r->getType());
        $this->assertSame('youtube', $r->getLicense());
        $this->assertFalse(is_null($r->getDescription()));
        $this->assertFalse(is_null($r->getSubjectDomains()));

        //origin
        $this->assertTrue($r->origin instanceof Origin);
        $this->assertSame('booba1234', $r->origin->getCreator());
        $this->assertFalse(is_null($r->origin->getDate()));
        $this->assertSame("YouTube Video", $r->origin->getFormat());

        //oembed
        $this->assertTrue($r->content->getOembed() instanceof OEmbed);
        $this->assertFalse(is_null($r->content->getOembed()));
        $this->assertSame('David After Dentist', $r->content->getOembed()->title);
    }

    public function testScanYouTubeResource()
    {
        $response = $this->getJson('GET', '/api/v1/resources/scan?uri=youtube://txqiwrbYGrs&_key=45678isafgd56789asfgdhf4567');

        $this->assertSame(203, $response['response']['code']);
    }

    public function testPersistYouTubeResource()
    {
        $response = $this->getJson('POST', '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'title' => 'YouTube Video',
            'type' => 'video'
        ]));
        $this->assertSame(201, $response['response']['code']);
        
        $id = $response['resource']['id'];
        $uploadUrl = substr($response['contentUploadUrl'], strlen('http://localhost'));

        $response = $this->getJson('POST', $uploadUrl.'?_key=45678isafgd56789asfgdhf4567', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'uri' => 'youtube://txqiwrbYGrs'
        ]));
        
        $this->assertSame(200, $response['response']['code']);
    }

    public function testPersistYouTubeResourceWithoutApiKeyOnUpload()
    {
        $response = $this->getJson('GET', '/api/v1/resources/scan?uri=youtube://txqiwrbYGrs&_key=45678isafgd56789asfgdhf4567');
        $response = $this->getJson('POST', '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($response['resource']));

        $this->assertSame(201, $response['response']['code']);
        $this->assertFalse(isset($response['resource']['content']));

        $id = $response['resource']['id'];
        $uploadUrl = substr($response['contentUploadUrl'], strlen('http://localhost'));

        $response = $this->getJson('POST', $uploadUrl, [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'uri' => 'youtube://txqiwrbYGrs'
        ]));

        $this->assertSame(200, $response['response']['code']);
    }
}
