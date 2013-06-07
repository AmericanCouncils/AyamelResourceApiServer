<?php

namespace Ayamel\ApiBundle\Tests;
use Ayamel\ApiBundle\ApiTestCase;

class ResourceIntegrationTest extends ApiTestCase
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
            'languages' => array('eng', 'zho'),
            'subjectDomains' => array('food', 'culture', 'history'),
            'functionalDomains' => array('verbs', 'adjectives', 'conjugation'),
            'visibility' => array('test_client', 'client1', 'client2'),
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
            'id' => 'test_client',
            'name' => "The Test Client",
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
        $this->assertSame('awaiting_content', $json['resource']['status']);
        $this->assertSame($data['title'], $json['resource']['title']);
        $this->assertSame($data['type'], $json['resource']['type']);
        $this->assertSame($data['description'], $json['resource']['description']);
        $this->assertSame($data['keywords'], $json['resource']['keywords']);
        $this->assertSame($data['languages'], $json['resource']['languages']);
        $this->assertSame($data['subjectDomains'], $json['resource']['subjectDomains']);
        $this->assertSame($data['functionalDomains'], $json['resource']['functionalDomains']);
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
            'subjectDomains' => array('food', 'culture', 'history'),
            'functionalDomains' => array('verbs', 'adjectives', 'conjugation'),
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
            'subjectDomains' => array('food', 'culture', 'history'),
            'functionalDomains' => array('verbs', 'adjectives', 'conjugation'),
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
        $this->assertSame('awaiting_content', $json['resource']['status']);
        $this->assertSame($data['title'], $json['resource']['title']);
        $this->assertSame($data['type'], $json['resource']['type']);
        $this->assertSame($data['description'], $json['resource']['description']);
        $this->assertSame($data['keywords'], $json['resource']['keywords']);
        $this->assertSame($data['subjectDomains'], $json['resource']['subjectDomains']);
        $this->assertSame($data['functionalDomains'], $json['resource']['functionalDomains']);
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

        //use these in subsequent tests
        $dateAdded = $json['resource']['dateAdded'];
        $resourceId = $json['resource']['id'];

        //now modify the resource

        sleep(1);       //sleeping one second to force dateModified to be different
        $changes = array(
            'title' => "I CHANGED YOU!",
            'subjectDomains' => array('food','bard'),
            'functionalDomains' => array('adjectives', 'conjugation', 'nouns'),
            'client' => array(
                'user' => array(
                    'id' => 'transferred',
                    'url' => 'http://foo.bar'
                )
            )
        );
        $expectedClient = array(
            'id' => '127.0.0.1',
            'user' => array(
                'id' => 'transferred',
                'url' => 'http://foo.bar'
            )
        );

        $modified = $this->getJson("PUT", '/api/v1/resources/'.$resourceId, array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode($changes));

        $this->assertSame(200, $modified['response']['code']);
        $this->assertSame('awaiting_content', $modified['resource']['status']);
        $this->assertSame($resourceId, $modified['resource']['id']);
        $this->assertSame($changes['title'], $modified['resource']['title']);
        $this->assertSame($data['type'], $modified['resource']['type']);
        $this->assertSame($data['description'], $modified['resource']['description']);
        $this->assertSame($data['keywords'], $modified['resource']['keywords']);
        $this->assertSame($changes['subjectDomains'], $modified['resource']['subjectDomains']);
        $this->assertSame($changes['functionalDomains'], $modified['resource']['functionalDomains']);
        $this->assertSame($data['visibility'], $modified['resource']['visibility']);
        $this->assertSame($data['copyright'], $modified['resource']['copyright']);
        $this->assertSame($data['license'], $modified['resource']['license']);
        $this->assertSame($data['origin'], $modified['resource']['origin']);
        $this->assertSame($expectedClient, $modified['resource']['client']);
        $this->assertTrue(isset($modified['resource']['dateAdded']));
        $this->assertSame($dateAdded, $modified['resource']['dateAdded']);
        $this->assertTrue(isset($modified['resource']['dateModified']));
        //now modified and added times should differ
        $this->assertFalse($modified['resource']['dateModified'] === $modified['resource']['dateAdded']);
        $this->assertFalse(isset($modified['resource']['content']));
        $this->assertFalse(isset($modified['resource']['relations']));
        $this->assertFalse(isset($modified['content_upload_url']));

        //setting a field to null should remove it
        sleep(1);   //sleeping one second to force dateModified to be different
        $prevDateModified = $modified['resource']['dateModified'];
        $changes2 = array(
            'title' => 'changed again',
            'subjectDomains' => null,
            'description' => null,
        );
        $modified = $this->getJson("PUT", '/api/v1/resources/'.$resourceId, array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode($changes2));

        $this->assertSame(200, $modified['response']['code']);
        $this->assertSame('awaiting_content', $modified['resource']['status']);
        $this->assertSame($resourceId, $modified['resource']['id']);
        $this->assertSame($changes2['title'], $modified['resource']['title']);
        $this->assertSame($data['type'], $modified['resource']['type']);
        $this->assertFalse(isset($modified['resource']['description']));
        $this->assertSame($data['keywords'], $modified['resource']['keywords']);
        $this->assertFalse(isset($modified['resource']['subjectDomains']));
        $this->assertSame($changes['functionalDomains'], $modified['resource']['functionalDomains']);
        $this->assertSame($data['visibility'], $modified['resource']['visibility']);
        $this->assertSame($data['copyright'], $modified['resource']['copyright']);
        $this->assertSame($data['license'], $modified['resource']['license']);
        $this->assertSame($data['origin'], $modified['resource']['origin']);
        $this->assertSame($expectedClient, $modified['resource']['client']);
        $this->assertTrue(isset($modified['resource']['dateAdded']));
        $this->assertSame($dateAdded, $modified['resource']['dateAdded']);
        $this->assertTrue(isset($modified['resource']['dateModified']));
        //now modified and added times should differ
        $this->assertFalse($prevDateModified === $modified['resource']['dateModified']);
        $this->assertFalse(isset($modified['resource']['content']));
        $this->assertFalse(isset($modified['resource']['relations']));
        $this->assertFalse(isset($modified['content_upload_url']));
    }

    public function testDeleteResource()
    {
        $data = array(
            'title' => 'A test to remember',
            'description' => 'An amazing description',
            'type' => 'data',
            'keywords' => 'foo, bar, baz',
            'subjectDomains' => array('food', 'culture', 'history'),
            'functionalDomains' => array('verbs', 'adjectives', 'conjugation'),
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
        $this->assertSame('awaiting_content', $json['resource']['status']);
        $this->assertSame($data['title'], $json['resource']['title']);
        $this->assertSame($data['type'], $json['resource']['type']);
        $this->assertSame($data['description'], $json['resource']['description']);
        $this->assertSame($data['keywords'], $json['resource']['keywords']);
        $this->assertSame($data['subjectDomains'], $json['resource']['subjectDomains']);
        $this->assertSame($data['functionalDomains'], $json['resource']['functionalDomains']);
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

        $resourceId = $json['resource']['id'];

        $modified = $this->getJson("DELETE", '/api/v1/resources/'.$resourceId, array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), null);

        $this->assertSame(200, $modified['response']['code']);
        $this->assertSame($resourceId, $modified['resource']['id']);
        $this->assertSame('deleted', $modified['resource']['status']);
        $this->assertTrue(isset($modified['resource']['dateDeleted']));
        $this->assertFalse(isset($modified['resource']['title']));
        $this->assertFalse(isset($modified['resource']['description']));
        $this->assertFalse(isset($modified['resource']['type']));
        $this->assertFalse(isset($modified['resource']['keywords']));
        $this->assertFalse(isset($modified['resource']['subjectDomains']));
        $this->assertFalse(isset($modified['resource']['functionalDomains']));
        $this->assertFalse(isset($modified['resource']['visibility']));
        $this->assertFalse(isset($modified['resource']['copyright']));
        $this->assertFalse(isset($modified['resource']['license']));
        $this->assertFalse(isset($modified['resource']['origin']));
        $this->assertFalse(isset($modified['resource']['client']));
        $this->assertFalse(isset($modified['resource']['dateModified']));
        $this->assertFalse(isset($modified['resource']['content']));
        $this->assertFalse(isset($modified['resource']['relations']));
    }

    public function testGetDeletedResource()
    {
        $data = array(
            'title' => 'A test to remember',
            'type' => 'data',
            'description' => 'An amazing description'
        );

        $body = json_encode($data);
        $json = $this->getJson("POST", '/api/v1/resources', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), $body);

        $this->assertSame(201, $json['response']['code']);
        $resourceId = $json['resource']['id'];
        $this->assertSame($data['title'], $json['resource']['title']);
        $this->assertSame($data['description'], $json['resource']['description']);

        //delete it
        $modified = $this->getJson("DELETE", '/api/v1/resources/'.$resourceId, array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ));

        $this->assertTrue(isset($modified['resource']['dateDeleted']));
        $this->assertFalse(isset($modified['resource']['title']));
        $this->assertFalse(isset($modified['resource']['description']));

        //try to get/put/delete again - expect a 410 and deleted resource object

        $response = $this->getResponse('GET', '/api/v1/resources/'.$resourceId);
        $this->assertSame(410, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame(410, $content['response']['code']);
        $this->assertTrue(isset($content['resource']['dateDeleted']));
        $this->assertSame('deleted', $content['resource']['status']);

        $response = $this->getResponse('PUT', '/api/v1/resources/'.$resourceId);
        $this->assertSame(410, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame(410, $content['response']['code']);
        $this->assertTrue(isset($content['resource']['dateDeleted']));
        $this->assertSame('deleted', $content['resource']['status']);

        $response = $this->getResponse('DELETE', '/api/v1/resources/'.$resourceId);
        $this->assertSame(410, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame(410, $content['response']['code']);
        $this->assertTrue(isset($content['resource']['dateDeleted']));
        $this->assertSame('deleted', $content['resource']['status']);
    }

    public function testDenyAccessToResource()
    {
        $this->markTestSkipped('API authentication not yet implemented.  All resources are public for now.');
    }

}
