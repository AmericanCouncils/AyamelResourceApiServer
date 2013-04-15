<?php

namespace Ayamel\ApiBundle\Tests;

use Ayamel\ApiBundle\TestCase;

class RelationsIntegrationTest extends TestCase
{
	
    protected function createTestResource()
    {
        return $this->getJson('POST', '/api/v1/resources', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array(
            'title' => 'testing',
            'description' => '...and more testing'
        )));
    }
    
	public function testCreateRelations()
	{
        $r1 = $this->createTestResource();
        $r2 = $this->createTestResource();
        
        $this->assertFalse(isset($r1['resource']['relations']));
        $this->assertFalse(isset($r2['resource']['relations']));
        $subjectId = $r1['resource']['id'];
        $objectId = $r2['resource']['id'];
        
        $relationData = array(
            'objectId' => $objectId,
            'type' => 'requires',
            'attributes' => array(
                'foo' => 'bar'
            ),
            'client' => array(
                'user' => array(
                    'id' => 'user1',
                    'url' => 'http://example.com/users/user1'
                )
            )
        );
        
        $response = $this->getResponse('POST', '/api/v1/resources/'.$subjectId.'/relations', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode($relationData));
        
        $this->assertSame(201, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        
        //check the relation
        $this->assertTrue(is_string($data['relation']['id']));
        $this->assertSame($subjectId, $data['relation']['objectId']);
        $this->assertSame($objectId, $data['relation']['objectId']);
        $this->assertSame($relationData['type'], $data['relation']['type']);
        $this->assertSame($relationData['attributes'], $data['relation']['attributes']);
        $this->assertSame($relationData['client']['user'], $relationData['relation']['client']['user']);
        $this->assertTrue(isset($data['relation']['client']['id']));
        
        //check both subject resource
        $res1 = $this->getJson('GET', '/api/v1/resources/'.$subjectId, array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ));
        
        $this->assertTrue(isset($res1['resource']['relations']));
        $this->assertSame(1, count($res1['resource']['relations']));
        $this->assertSame($subjectId, $res1['resource']['relations'][0]['subjectId']);
        $this->assertSame($objectId, $res1['resource']['relations'][0]['objectId']);
        $this->assertSame($relationData['type'], $res1['resource']['relations'][0]['type']);
        $this->assertSame($relationData['attributes'], $res1['resource']['relations'][0]['attributes']);
        $this->assertSame($relationData['client']['user'], $res1['resource']['relations'][0]['client']['user']);
        $this->assertSame($data['relation']['client']['id'], $res1['resource']['relations'][0]['client']['id']);
        
        //check object resource
        $res2 = $this->getJson('GET', '/api/v1/resources/'.$subjectId, array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ));
        
        $this->assertTrue(isset($res2['resource']['relations']));
        $this->assertSame(1, count($res2['resource']['relations']));
        $this->assertSame($subjectId, $res2['resource']['relations'][0]['subjectId']);
        $this->assertSame($objectId, $res2['resource']['relations'][0]['objectId']);
        $this->assertSame($relationData['type'], $res2['resource']['relations'][0]['type']);
        $this->assertSame($relationData['attributes'], $res2['resource']['relations'][0]['attributes']);
        $this->assertSame($relationData['client']['user'], $res2['resource']['relations'][0]['client']['user']);
        $this->assertSame($data['relation']['client']['id'], $res2['resource']['relations'][0]['client']['id']);
        
	}
    
    public function testCreateRelationForNonExistingObject()
    {
        //non-existing subject
        $response = $this->getResponse('POST', '/api/v1/resources/3333333/relations', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array(
            'objectId' => '33333334',
            'type' => 'requires',
            'attributes' => array(
                'foo' => 'bar'
            ),
        )));
        $this->assertSame(404, $response->getStatusCode());
        
        //non-existing object
        $subject = $this->createTestResource();
        $subId = $subject['resource']['id'];
        
        $response = $this->getResponse('POST', '/api/v1/resources/'.$subId.'/relations', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode(array(
            'objectId' => '33333334',
            'type' => 'requires',
            'attributes' => array(
                'foo' => 'bar'
            ),
        )));
        
        $this->assertSame(400, $response->getStatusCode());
    }
    
    public function testCreateRelationForUnauthorizedObject()
    {
        $this->markTestSkipped("Requires auth to be implemented.");
        //expect 403
    }
    
	public function testGetRelations()
	{
        $resource = $this->createTestResourceWithRelations();
        $resourceId = $resource['resource']['id'];
        
        $data = $this->getJson('GET', '/api/v1/resources/'.$resourceId.'/relations', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ));

        $this->assertSame(2, count($data['relations']));
        
        $this->markTestSkipped("Add more assertions here.");
	}

	public function testFilterRelationsByType()
	{
        $this->markTestSkipped('TODO');
	}
    
    public function testGetRelationsForAuthorizedObjects()
    {
        $this->markTestSkipped("Requires auth to be implemented.");
    }
	
	public function testDeleteRelation()
	{
        $res1 = $this->createTestResource();
        $res2 = $this->createTestResource();
        $subjectId = $res1['resource']['id'];
        $objectId = $res2['resource']['id'];
        $relationData = array(
            'objectId' => $objectId,
            'type' => 'requires',
            'attributes' => array(
                'foo' => 'bar'
            ),
            'client' => array(
                'user' => array(
                    'id' => 'user1',
                    'url' => 'http://example.com/users/user1'
                )
            )
        );
        
        //check created relation
        $rel = $this->getJson('POST', '/api/v1/resources/'.$subjectId, array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode($relationData));
        $this->assertTrue(isset($rel['relation']['id']));
        $relationId = $rel['relation']['id'];
        
        //check both resources, should both have the relation
        $subject = $this->getJson('GET', '/api/v1/resources/'.$subjectId, array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ));
        $object = $this->getJson('GET', '/api/v1/resources/'.$objectId, array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ));
        $this->assertTrue(isset($subject['resource']['relations'][0]['id']));
        $this->assertTrue(isset($object['resource']['relations'][0]['id']));
        $this->assertSame(
            $subject['resource']['relations'][0]['id'],
            $object['resource']['relations'][0]['id']
        );
        
        //delete the relation
        $response = $this->getResponse('DELETE', '/api/v1/resources/'.$subjectId.'/relations/'.$relationId, array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ));
        $this->assertSame(200, $response->getStatusCode());
        
        
        //check resources again, neither should have relations
        $subject = $this->getJson('GET', '/api/v1/resources/'.$subjectId, array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ));
        $object = $this->getJson('GET', '/api/v1/resources/'.$objectId, array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ));
        $this->assertFalse(isset($subject['resource']['relations']));
        $this->assertFalse(isset($object['resource']['relations']));
	}
}
