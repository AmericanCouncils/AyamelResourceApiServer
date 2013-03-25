<?php

namespace Ayamel\ApiBundle\Tests;
use Ayamel\ApiBundle\TestCase;

class ResourceIntegrationTest extends TestCase
{
	
	public function testInteractionsWithNonExistingResource()
	{
		//get/put/delete on non-existing resource
        $response = $this->getJson('GET', '/api/v1/resources/5');
        $this->assertSame(404, $response->response->code);
	}
    
    public function testSupressCodes()
    {
        $response = $this->getResponse('GET', '/api/v1/resources/5', array(
            '_suppress_codes' => 'true'
        ));
        $json = json_decode($response->getContent());
            
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(404, $json->response->code);
    }
	
	public function testCreateNewResource()
	{
		
	}
    
    public function testUploadContent()
    {
        
    }
    
    public function testModifyResource()
    {
        
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
