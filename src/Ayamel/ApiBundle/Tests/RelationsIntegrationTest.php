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
        $data = $this->createTestResource();
        $this->assertFalse(isset($data['resource']['relations']));
        $id = $data['resource']['id'];
        
        $relationData = array(
            'objectId' => ''
        );
        
        $response = $this->getResponse('POST', '/api/v1/resources/'.$id.'/relations', array(), array(), array(
            'CONTENT_TYPE' => 'application/json'
        ), json_encode($relationData));
        
        $this->assertSame(201, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        
        
	}
    
    public function testCreateRelationForNonExistingObject()
    {
        //400
    }
    
    public function testCreateRelationForUnauthorizedObject()
    {
        $this->markTestSkipped("Requires auth to be implemented.");
        //403
    }
    
	public function testGetRelations()
	{
        
	}

	public function testFilterRelationsByType()
	{
        
	}
    
    public function testGetRelationsForAuthorizedObjects()
    {
        $this->markTestSkipped("Requires auth to be implemented.");
    }
	
	public function testDeleteRelation()
	{
        
	}
}
