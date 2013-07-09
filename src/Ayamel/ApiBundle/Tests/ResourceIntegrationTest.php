<?php

namespace Ayamel\ApiBundle\Tests;
use Ayamel\ApiBundle\ApiTestCase;

class ResourceIntegrationTest extends ApiTestCase
{

    protected function createExampleResource(array $data, $apiKey = '45678isafgd56789asfgdhf4567')
    {
        $json = $this->getJson('POST', '/api/v1/resources?_key='.$apiKey, array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode($data));

        if (201 !== $json['response']['code']) {
            throw new \RuntimeException("Could not create example Resource.");
        }

        return $json['resource'];
    }

    public function testAccessNonExistingResource()
    {
        //get/put/delete on non-existing resource
        $response = $this->getResponse('GET', '/api/v1/resources/5');
        $json = json_decode($response->getContent(), true);
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame(404, $json['response']['code']);

        $response = $this->getResponse('PUT', '/api/v1/resources/5');
        $json = json_decode($response->getContent(), true);
        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame(401, $json['response']['code']);

        $response = $this->getResponse('PUT', '/api/v1/resources/5?_key=45678isafgd56789asfgdhf4567');
        $json = json_decode($response->getContent(), true);
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame(404, $json['response']['code']);

        $response = $this->getResponse('DELETE', '/api/v1/resources/5');
        $json = json_decode($response->getContent(), true);
        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame(401, $json['response']['code']);
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
            'languages' => array(
                'iso639_3' => array('eng', 'zho'),
                'bcp47' => array('en', 'en-GB')
            ),
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
            'clientUser' => array(
                'id' => 'theTester',
                'url' => 'http://example.com/users/theTester'
            ),
        );

        //api automatically injects the client id, and it can't be set by the caller
        $expectedClient = array(
            'id' => 'test_client',
            'name' => "The Test Client",
        );

        $body = json_encode($data);
        $json = $this->getJson("POST", '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
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
        $this->assertSame($data['clientUser'], $json['resource']['clientUser']);
        $this->assertSame($expectedClient, $json['resource']['client']);
        $this->assertTrue(isset($json['resource']['dateAdded']));
        $this->assertTrue(isset($json['resource']['dateModified']));
        $this->assertSame($json['resource']['dateModified'], $json['resource']['dateAdded']);
        $this->assertFalse(isset($json['resource']['content']));
        $this->assertFalse(isset($json['resource']['relations']));
        $this->assertTrue(isset($json['contentUploadUrl']));
    }

    public function testCreateNewResourceIgnoresReadOnlyAndInvalidFields()
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
            'dateDeleted' => 132435654,
            'foooooo' => 'bar',
            'origin' => array(
                'creator' => 'Leonardo da Vinci',
                'location' => 'Italy',
                'date' => 'Late 2039',
                'format' => 'Oil on circuit board',
                'note' => '... you never know.',
                'uri' => 'http://thefuture.com/'
            ),
            'clientUser' => array(
                'id' => 'theTester',
                'url' => 'http://example.com/users/theTester'
            ),
            'client' => array(
                'id' => 'h4x0r3d',
            ),
        );

        $body = json_encode($data);
        $response = $this->getJson("POST", '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), $body);

        $this->assertSame(201, $response['response']['code']);
        $this->assertSame('test_client', $response['resource']['client']['id']);
        $this->assertFalse(isset($response['resource']['dateDeleted']));
    }

    public function testGetResource()
    {
        $data = array(
            'title' => 'test',
            'type' => 'data'
        );
        $response = $this->getJson('POST', '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode($data));

        $this->assertSame(201, $response['response']['code']);
        $id = $response['resource']['id'];

        $response = $this->getJson('GET', '/api/v1/resources/'.$id);
        $this->assertSame(200, $response['response']['code']);
        $this->assertSame('test', $response['resource']['title']);
        $this->assertSame('test_client', $response['resource']['client']['id']);
        $this->assertTrue(isset($response['resource']['dateAdded']));
        $this->assertTrue(isset($response['resource']['dateModified']));
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
            'visibility' => array('test_client', 'client2'),
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
            'clientUser' => array(
                'id' => 'theTester',
                'url' => 'http://example.com/users/theTester'
            )
        );

        //api automatically injects the client id, and it can't be set by the caller
        $expectedClient = array(
            'id' => 'test_client',
            'name' => "The Test Client"
        );

        $body = json_encode($data);
        $json = $this->getJson("POST", '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
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
        $this->assertSame($data['clientUser'], $json['resource']['clientUser']);
        $this->assertSame($expectedClient, $json['resource']['client']);
        $this->assertTrue(isset($json['resource']['dateAdded']));
        $this->assertTrue(isset($json['resource']['dateModified']));
        $this->assertSame($json['resource']['dateModified'], $json['resource']['dateAdded']);
        $this->assertFalse(isset($json['resource']['content']));
        $this->assertFalse(isset($json['resource']['relations']));
        $this->assertTrue(isset($json['contentUploadUrl']));

        //use these in subsequent tests
        $dateAdded = $json['resource']['dateAdded'];
        $resourceId = $json['resource']['id'];

        //now modify the resource

        sleep(1);       //sleeping one second to force dateModified to be different
        $changes = array(
            'title' => "I CHANGED YOU!",
            'subjectDomains' => array('food','bard'),
            'functionalDomains' => array('adjectives', 'conjugation', 'nouns'),
            'clientUser' => array(
                'id' => 'transferred',
                'url' => 'http://foo.bar'
            )
        );

        $modified = $this->getJson("PUT", '/api/v1/resources/'.$resourceId."?_key=45678isafgd56789asfgdhf4567", array(), array(), array(
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
        $this->assertSame($changes['clientUser'], $modified['resource']['clientUser']);
        $this->assertSame($expectedClient, $modified['resource']['client']);
        $this->assertTrue(isset($modified['resource']['dateAdded']));
        $this->assertSame($dateAdded, $modified['resource']['dateAdded']);
        $this->assertTrue(isset($modified['resource']['dateModified']));
        //now modified and added times should differ
        $this->assertFalse($modified['resource']['dateModified'] === $modified['resource']['dateAdded']);
        $this->assertFalse(isset($modified['resource']['content']));
        $this->assertFalse(isset($modified['resource']['relations']));
        $this->assertFalse(isset($modified['contentUploadUrl']));

        //setting a field to null should remove it
        sleep(1);   //sleeping one second to force dateModified to be different
        $prevDateModified = $modified['resource']['dateModified'];
        $changes2 = array(
            'title' => 'changed again',
            'subjectDomains' => null,
            'description' => null,
        );
        $modified = $this->getJson("PUT", '/api/v1/resources/'.$resourceId."?_key=45678isafgd56789asfgdhf4567", array(), array(), array(
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
        $this->assertFalse(isset($modified['contentUploadUrl']));
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
            'visibility' => array('test_client', 'client2'),
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
            'clientUser' => array(
                'id' => 'theTester',
                'url' => 'http://example.com/users/theTester'
            )
        );

        //api automatically injects the client id, and it can't be set by the caller
        $expectedClient = array(
            'id' => 'test_client',
            'name' => "The Test Client",
        );

        $body = json_encode($data);
        $json = $this->getJson("POST", '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
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
        $this->assertSame($data['clientUser'], $json['resource']['clientUser']);
        $this->assertSame($expectedClient, $json['resource']['client']);
        $this->assertTrue(isset($json['resource']['dateAdded']));
        $this->assertTrue(isset($json['resource']['dateModified']));
        $this->assertSame($json['resource']['dateModified'], $json['resource']['dateAdded']);
        $this->assertFalse(isset($json['resource']['content']));
        $this->assertFalse(isset($json['resource']['relations']));
        $this->assertTrue(isset($json['contentUploadUrl']));

        $resourceId = $json['resource']['id'];

        $modified = $this->getJson("DELETE", '/api/v1/resources/'.$resourceId."?_key=45678isafgd56789asfgdhf4567", array(), array(), array(
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
        $json = $this->getJson("POST", '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), $body);

        $this->assertSame(201, $json['response']['code']);
        $resourceId = $json['resource']['id'];
        $this->assertSame($data['title'], $json['resource']['title']);
        $this->assertSame($data['description'], $json['resource']['description']);

        //delete it
        $modified = $this->getJson("DELETE", '/api/v1/resources/'.$resourceId."?_key=45678isafgd56789asfgdhf4567", array(), array(), array(
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

        $response = $this->getResponse('PUT', '/api/v1/resources/'.$resourceId."?_key=45678isafgd56789asfgdhf4567");
        $this->assertSame(410, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame(410, $content['response']['code']);
        $this->assertTrue(isset($content['resource']['dateDeleted']));
        $this->assertSame('deleted', $content['resource']['status']);

        $response = $this->getResponse('DELETE', '/api/v1/resources/'.$resourceId."?_key=45678isafgd56789asfgdhf4567");
        $this->assertSame(410, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame(410, $content['response']['code']);
        $this->assertTrue(isset($content['resource']['dateDeleted']));
        $this->assertSame('deleted', $content['resource']['status']);
    }

    public function testResourceVisibility()
    {
        $json = $this->getJson("POST", '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array('title'=>'foo', 'type'=>'data', 'visibility' => array('test_client2'))));
        $this->assertSame(201, $json['response']['code']);

        $json = $this->getJson("POST", '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ));
    }

    //ensure authenticated client
    public function testRequireApiKeyAuthentication()
    {
        $json = $this->getJson("POST", '/api/v1/resources', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array('title'=>'foo', 'type'=>'data')));
        $this->assertSame(401, $json['response']['code']);

        //create w/ invalid key
        $json = $this->getJson("POST", '/api/v1/resources?_key=fakekey', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array('title'=>'foo', 'type'=>'data')));
        $this->assertSame(401, $json['response']['code']);

        //no key
        $json = $this->getJson("PUT", '/api/v1/resources/5', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array('title'=>'foo', 'type'=>'data')));
        $this->assertSame(401, $json['response']['code']);
        $json = $this->getJson("DELETE", '/api/v1/resources/5', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array('title'=>'foo', 'type'=>'data')));
        $this->assertSame(401, $json['response']['code']);

        //invalid key
        $json = $this->getJson("PUT", '/api/v1/resources/5?_key=fakekey', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array('title'=>'foo', 'type'=>'data')));
        $this->assertSame(401, $json['response']['code']);
        $json = $this->getJson("DELETE", '/api/v1/resources/5?_key=fakekey', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array('title'=>'foo', 'type'=>'data')));
        $this->assertSame(401, $json['response']['code']);
    }

    public function testRequireResourceOwner()
    {
        //create/modify public resource
        $json = $this->getJson("POST", '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array('title'=>'foo', 'type'=>'data')));
        $this->assertSame(201, $json['response']['code']);
        $id = $json['resource']['id'];
        $json = $this->getJson("PUT", '/api/v1/resources/'.$id, array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array('type'=>'audio')));
        $this->assertSame(401, $json['response']['code']);
        $json = $this->getJson("PUT", '/api/v1/resources/'.$id.'?_key=fakekey', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array('type'=>'audio')));
        $this->assertSame(401, $json['response']['code']);

        //create and modify resource as non owner
        $json = $this->getJson("POST", '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array('title'=>'foo', 'type'=>'data')));
        $this->assertSame(201, $json['response']['code']);
        $id = $json['resource']['id'];
        $json = $this->getJson("PUT", '/api/v1/resources/'.$id."?_key=55678isafgd56789asfgdhf4568", array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array('type'=>'audio')));
        $this->assertSame(403, $json['response']['code']);
        $json = $this->getJson("DELETE", '/api/v1/resources/'.$id."?_key=55678isafgd56789asfgdhf4568", array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array('type'=>'audio')));
        $this->assertSame(403, $json['response']['code']);
    }

    public function testFilterResources()
    {
        $this->clearDatabase();

        $r1 = $this->createExampleResource(array(
            'title' => 'R1',
            'type' => 'data'
        ));
        $r2 = $this->createExampleResource(array(
            'title' => 'R2',
            'type' => 'data',
            'clientUser' => array(
                'id' => 'evan'
            )
        ));
        $r3 = $this->createExampleResource(array(
            'title' => 'R3',
            'type' => 'data',
            'clientUser' => array(
                'id' => 'nave'
            )
        ));
        $r4 = $this->createExampleResource(array(
            'title' => 'R4',
            'type' => 'data',
            'visibility' => array('test_client')
        ));
        $r5 = $this->createExampleResource(array(
            'title' => 'R5',
            'type' => 'video',
            'languages' => array(
                'iso639_3' => array('eng', 'ara', 'arq'),
                'bcp47' => array('en', 'ar')
            )
        ));

        //get
        $data = $this->getJson('GET', '/api/v1/resources');
        $this->assertSame(200, $data['response']['code']);
        $this->assertTrue(isset($data['resources']));
        $count = count($data['resources']);
        $this->assertSame(4, count($data['resources']));
        $this->assertSame('R5', $data['resources'][$count - 1]['title']);

        //get w/ skip
        $data = $this->getJson('GET', '/api/v1/resources?skip=1');
        $this->assertSame(200, $data['response']['code']);
        $this->assertTrue(isset($data['resources']));
        $this->assertSame(3, count($data['resources']));
        $this->assertSame('R3', $data['resources'][1]['title']);

        //get w/ limit
        $data = $this->getJson('GET', '/api/v1/resources?limit=2');
        $this->assertSame(200, $data['response']['code']);
        $this->assertTrue(isset($data['resources']));
        $this->assertSame(2, count($data['resources']));
        $this->assertSame('R2', $data['resources'][1]['title']);

        //get w/ client
        $data = $this->getJson('GET', '/api/v1/resources?_key=45678isafgd56789asfgdhf4567');
        $this->assertSame(200, $data['response']['code']);
        $this->assertTrue(isset($data['resources']));
        $this->assertSame(5, count($data['resources']));
        $data = $this->getJson('GET', '/api/v1/resources?_key=55678isafgd56789asfgdhf4568');
        $this->assertSame(200, $data['response']['code']);
        $this->assertTrue(isset($data['resources']));
        $this->assertSame(0, count($data['resources']));

        //get w/ clientUser
        $data = $this->getJson('GET', '/api/v1/resources?clientUser=evan');
        $this->assertSame(200, $data['response']['code']);
        $this->assertSame(1, count($data['resources']));
        $data = $this->getJson('GET', '/api/v1/resources?clientUser=evan,nave');
        $this->assertSame(200, $data['response']['code']);
        $this->assertSame(2, count($data['resources']));

        //get w/ type
        $data = $this->getJson('GET', '/api/v1/resources?type=video');
        $this->assertSame(200, $data['response']['code']);
        $this->assertSame(1, count($data['resources']));
        $data = $this->getJson('GET', '/api/v1/resources?type=video,data');
        $this->assertSame(200, $data['response']['code']);
        $this->assertSame(4, count($data['resources']));

        //get w/ id
        $data = $this->getJson('GET', '/api/v1/resources?id='.$r2['id']);
        $this->assertSame(200, $data['response']['code']);
        $this->assertSame(1, count($data['resources']));
        $data = $this->getJson('GET', '/api/v1/resources?id='.$r2['id'].','.$r5['id']);
        $this->assertSame(200, $data['response']['code']);
        $this->assertSame(2, count($data['resources']));

        //get w/ languages
        //bcp47 code
        $data = $this->getJson('GET', '/api/v1/resources?languages=en');
        $this->assertSame(200, $data['response']['code']);
        $this->assertSame(1, count($data['resources']));
        $this->assertSame($r5['id'], $data['resources'][0]['id']);
        //iso639-3 code
        $data = $this->getJson('GET', '/api/v1/resources?languages=arq');
        $this->assertSame(200, $data['response']['code']);
        $this->assertSame(1, count($data['resources']));
        $this->assertSame($r5['id'], $data['resources'][0]['id']);
        //both codes
        $data = $this->getJson('GET', '/api/v1/resources?languages=ar,arq');
        $this->assertSame(200, $data['response']['code']);
        $this->assertSame(1, count($data['resources']));
        $this->assertSame($r5['id'], $data['resources'][0]['id']);

    }
}
