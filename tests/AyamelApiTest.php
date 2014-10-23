<?php

class AyamelApiTest extends PHPUnit_Framework_TestCase
{
    use AyamelClientTrait;
    
    public function testGetApiDocsPage()
    {
        $res = $this->callAyamel('GET', '/api/v1/docs');
        $this->assertSame(200, $res->getStatusCode());
    }
    
    public function testGetResources()
    {
        $res = $this->getJson('/api/v1/resources');
        
        $this->assertSame(200, $res['response']['code']);
        $this->assertTrue(isset($res['limit']));
        $this->assertTrue(isset($res['skip']));
        $this->assertTrue(isset($res['total']));
    }
    
    public function testGetRelations()
    {
        $res = $this->getJson('/api/v1/relations');
        
        $this->assertSame(200, $res['response']['code']);
        $this->assertTrue(isset($res['limit']));
        $this->assertTrue(isset($res['skip']));
        $this->assertTrue(isset($res['total']));
    }
    
    public function testGetNonExistentResource()
    {
        $res = $this->getJson('/api/v1/resources/does-not-exist');
        $this->assertSame(404, $res['response']['code']);
    }
        
    public function testResourceSearch()
    {
        $res = $this->getJson('/api/v1/resources/search');
        
        $this->assertSame(200, $res['response']['code']);
        $this->assertTrue(isset($res['result']['query']['limit']));
        $this->assertTrue(isset($res['result']['query']['skip']));
        $this->assertTrue(isset($res['result']['query']['total']));
        $this->assertTrue(isset($res['result']['hits']));
    }
    
    public function testResourceYouTubeScan()
    {
        $res = $this->getJson('/api/v1/resources/scan', ['uri' => 'youtube://txqiwrbYGrs']);
        $this->assertSame(203, $res['response']['code']);
    }
    
    public function testResourceLifecycle()
    {
        $res = $this->postJson('/api/v1/resources', [
            'title' => 'test',
            'type' => 'data',
            'visibility' => [AYAMEL_CLIENT_ID]
        ]);
        $this->assertSame(201, $res['response']['code']);

        $resourceId = $res['resource']['id'];
        $uploadUrl = $res['contentUploadUrl'];
        
        //modify resource
        $res = $this->putJson("/api/v1/resources/$resourceId", ['title' => 'foo']);
        $this->assertSame(200, $res['response']['code']);
        
        //get resource
        $res = $this->getJson("/api/v1/resources/$resourceId");
        $this->assertSame(200, $res['response']['code']);
        $this->assertSame('foo', $res['resource']['title']);
        
        //send content references
        $this->putJson($uploadUrl, ['remoteFiles'=> [[
            'downloadUri' => 'https://www.google.com/',             //api actually tries to query the file, so this url is likely to work in tests
            'representation' => 'original',
            'quality' => 1,
            'mime' => 'text/html; encoding=utf-8',
            'mimeType' => 'text/html'
        ]]]);
        $this->assertSame(200, $res['response']['code']);
        
        //get again
        $res = $this->getJson("/api/v1/resources/$resourceId");
        $this->assertSame(200, $res['response']['code']);
        
        //delete resource
        $res = $this->deleteJson("/api/v1/resources/$resourceId");
        $this->assertSame(200, $res['response']['code']);
        
        //get again, 410
        $res = $this->getJson("/api/v1/resources/$resourceId");
        $this->assertSame(410, $res['response']['code']);
    }

    public function testRelationLifecycle()
    {
        //create two dummy resources
        $res0 = $this->postJson('/api/v1/resources', [
            'title' => 'test',
            'type' => 'data',
            'visibility' => [AYAMEL_CLIENT_ID]
        ]);
        $this->assertSame(201, $res0['response']['code']);
        $res1 = $this->postJson('/api/v1/resources', [
            'title' => 'test2',
            'type' => 'data',
            'visibility' => [AYAMEL_CLIENT_ID]
        ]);
        $this->assertSame(201, $res1['response']['code']);

        $subId = $res0['resource']['id'];
        $objId = $res1['resource']['id'];
        
        //add relation between them
        $rel = $this->postJson('/api/v1/relations', [
            'type' => 'part_of',
            'subjectId' => $subId,
            'objectId' => $objId
        ]);
        $this->assertSame(201, $rel['response']['code']);
        $relId = $rel['relation']['id'];
        
        //ensure resources have relation
        $res = $this->getJson("/api/v1/resources/$subId", ['relations' => 'true']);
        $this->assertSame(1, count($res['resource']['relations']));
        $res = $this->getJson("/api/v1/resources/$objId", ['relations' => 'true']);
        $this->assertSame(1, count($res['resource']['relations']));
        
        //delete relation
        $res = $this->deleteJson("/api/v1/relations/$relId");
        var_dump($relId); ob_flush();
        $this->assertSame(200, $res['response']['code']);
        
        //ensure resources do not have relation
        $res = $this->getJson("/api/v1/resources/$subId", ['relations' => 'true']);
        $this->assertSame(0, count($res['resource']['relations']));
        $res = $this->getJson("/api/v1/resources/$objId", ['relations' => 'true']);
        $this->assertSame(0, count($res['resource']['relations']));
        
        $this->markTestIncomplete();
    }
}
