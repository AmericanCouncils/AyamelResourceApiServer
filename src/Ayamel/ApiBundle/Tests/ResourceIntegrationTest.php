<?php

namespace Ayamel\ApiBundle\Tests;
use Ayamel\ApiBundle\TestCase;

class ResourceIntegrationTest extends TestCase
{
	
	public function testAccessNonExistingResource()
	{
		//get/put/delete on non-existing resource
        $response = $this->getResponse('GET', '/api/v1/resources/5');
        $json = json_decode($response->getContent(), true);
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame(404, $json['response']['code']);

        $response = $this->getResponse('PUT', '/api/v1/resources/5');
        $json = json_decode($response->getContent(), true);
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame(404, $json['response']['code']);

        $response = $this->getResponse('DELETE', '/api/v1/resources/5');
        $json = json_decode($response->getContent(), true);
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame(404, $json['response']['code']);
	}
    
    public function testSupressCodes()
    {
        $response = $this->getResponse('GET', '/api/v1/resources/5', array(
            '_suppress_codes' => 'true'
        ));
        $json = json_decode($response->getContent(), true);
            
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(404, $json['response']['code']);
    }
	
    //NOTE: Keep this particular test up-to-date with all available fields
    //that can be set by the client
	public function testCreateNewResource()
	{
        $data = array(
            'title' => 'A test to remember',
            'description' => 'An amazing description',
            'type' => 'data',
            'keywords' => 'foo, bar, baz',
            'categories' => array('food', 'culture', 'history'),
            'visibility' => array('client1', 'client2'),
            'copyright' => "Copyright text 2013",
            'license' => 'Public Domain',
            'origin' => array(
                'creator' => 'Leonardo da Vinci',
                'location' => 'Italy',
                'date' => 'Late 2039',
                'format' => 'Oil on circuit board',
                'note' => '... you never know.',
                'uri' => 'http://thefuture.com/'
            ),
            'client' => array(
                'user' => array(
                    'id' => 'theTester',
                    'url' => 'http://example.com/users/theTester'
                )
            ),
        );
        
        //api automatically injects the client id, and it can't be set by the caller
        $expectedClient = array(
            'id' => '127.0.0.1',
            'user' => array(
                'id' => 'theTester',
                'url' => 'http://example.com/users/theTester'
            )
        );
        
        $body = json_encode($data);
        $json = $this->getJson("POST", '/api/v1/resources', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), $body);
        
        $this->assertSame(201, $json['response']['code']);
        $this->assertTrue(is_string($json['resource']['id']));
        $this->assertSame($data['title'], $json['resource']['title']);
        $this->assertSame($data['type'], $json['resource']['type']);
        $this->assertSame($data['description'], $json['resource']['description']);
        $this->assertSame($data['keywords'], $json['resource']['keywords']);
        $this->assertSame($data['categories'], $json['resource']['categories']);
        $this->assertSame($data['visibility'], $json['resource']['visibility']);
        $this->assertSame($data['copyright'], $json['resource']['copyright']);
        $this->assertSame($data['license'], $json['resource']['license']);
        $this->assertSame($data['origin'], $json['resource']['origin']);
        $this->assertSame($expectedClient, $json['resource']['client']);
        $this->assertTrue(isset($json['resource']['dateAdded']));
        $this->assertTrue(isset($json['resource']['dateModified']));
        $this->assertSame($json['resource']['dateModified'], $json['resource']['dateAdded']);
        $this->assertFalse(isset($json['resource']['content']));
        $this->assertFalse(isset($json['resource']['relations']));
        $this->assertTrue(isset($json['content_upload_url']));
	}
    
    public function testCreateNewResourceWithInvalidData()
    {
        $data = array(
            'title' => 'A test to remember',
            'description' => 'An amazing description',
            'type' => 'data',
            'keywords' => 'foo, bar, baz',
            'categories' => array('food', 'culture', 'history'),
            'visibility' => array('client1', 'client2'),
            'copyright' => "Copyright text 2013",
            'license' => 'Public Domain',
            'origin' => array(
                'creator' => 'Leonardo da Vinci',
                'location' => 'Italy',
                'date' => 'Late 2039',
                'format' => 'Oil on circuit board',
                'note' => '... you never know.',
                'uri' => 'http://thefuture.com/'
            ),
            'client' => array(
                'id' => 'h4x0r3d',
                'user' => array(
                    'id' => 'theTester',
                    'url' => 'http://example.com/users/theTester'
                )
            ),
        );
        
        $body = json_encode($data);
        $response = $this->getResponse("POST", '/api/v1/resources', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), $body);
        
        $this->assertSame(400, $response->getStatusCode());
    }
    
    public function testModifyResource()
    {
        $data = array(
            'title' => 'A test to remember',
            'description' => 'An amazing description',
            'type' => 'data',
            'keywords' => 'foo, bar, baz',
            'categories' => array('food', 'culture', 'history'),
            'visibility' => array('client1', 'client2'),
            'copyright' => "Copyright text 2013",
            'license' => 'Public Domain',
            'origin' => array(
                'creator' => 'Leonardo da Vinci',
                'location' => 'Italy',
                'date' => 'Late 2039',
                'format' => 'Oil on circuit board',
                'note' => '... you never know.',
                'uri' => 'http://thefuture.com/'
            ),
            'client' => array(
                'user' => array(
                    'id' => 'theTester',
                    'url' => 'http://example.com/users/theTester'
                )
            ),
        );
        
        //api automatically injects the client id, and it can't be set by the caller
        $expectedClient = array(
            'id' => '127.0.0.1',
            'user' => array(
                'id' => 'theTester',
                'url' => 'http://example.com/users/theTester'
            )
        );
        
        $body = json_encode($data);
        $json = $this->getJson("POST", '/api/v1/resources', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), $body);
        
        $this->assertSame(201, $json['response']['code']);
        $this->assertTrue(is_string($json['resource']['id']));
        $this->assertSame($data['title'], $json['resource']['title']);
        $this->assertSame($data['type'], $json['resource']['type']);
        $this->assertSame($data['description'], $json['resource']['description']);
        $this->assertSame($data['keywords'], $json['resource']['keywords']);
        $this->assertSame($data['categories'], $json['resource']['categories']);
        $this->assertSame($data['visibility'], $json['resource']['visibility']);
        $this->assertSame($data['copyright'], $json['resource']['copyright']);
        $this->assertSame($data['license'], $json['resource']['license']);
        $this->assertSame($data['origin'], $json['resource']['origin']);
        $this->assertSame($expectedClient, $json['resource']['client']);
        $this->assertTrue(isset($json['resource']['dateAdded']));
        $this->assertTrue(isset($json['resource']['dateModified']));
        $this->assertSame($json['resource']['dateModified'], $json['resource']['dateAdded']);
        $this->assertFalse(isset($json['resource']['content']));
        $this->assertFalse(isset($json['resource']['relations']));
        $this->assertTrue(isset($json['content_upload_url']));
        
        //now modify the resource
        $resourceId = $json['resource']['id'];
        $modified = $this->getJson("PUT", '/api/v1/resources/'.$resourceId, array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array(
            'title' => "I CHANGED YOU!"
        )));
        
        $this->assertSame(200, $modified['response']['code']);
        $this->assertSame($resourceId, $modified['resource']['id']);
        $this->assertSame('I CHANGED YOU!', $modified['resource']['title']);
        $this->assertSame($data['type'], $modified['resource']['type']);
        $this->assertSame($data['description'], $modified['resource']['description']);
        $this->assertSame($data['keywords'], $modified['resource']['keywords']);
        $this->assertSame($data['categories'], $modified['resource']['categories']);
        $this->assertSame($data['visibility'], $modified['resource']['visibility']);
        $this->assertSame($data['copyright'], $modified['resource']['copyright']);
        $this->assertSame($data['license'], $modified['resource']['license']);
        $this->assertSame($data['origin'], $modified['resource']['origin']);
        $this->assertSame($expectedClient, $modified['resource']['client']);
        $this->assertTrue(isset($modified['resource']['dateAdded']));
        $this->assertTrue(isset($modified['resource']['dateModified']));
        //now modified and added times should differ
        $this->assertFalse($modified['resource']['dateModified'] === $modified['resource']['dateAdded']);
        $this->assertFalse(isset($modified['resource']['content']));
        $this->assertFalse(isset($modified['resource']['relations']));
        $this->assertTrue(isset($modified['content_upload_url']));
        
        //setting a field to null should remove it
        //TODO
    }
    
    public function testDeleteResource()
    {
        
    }
    
    public function testGetDeletedResource()
    {
        
    }
    
    public function testDenyAccessToResource()
    {
        
    }

}
