<?php
namespace Ayamel\ResourceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Basic Resource object CRUD - note that actual content is handled by a separate set of controllers
 */    
class ResourceApiV1Controller extends Controller {

    /**
     * Get a resource object by id
     */
	public function getResourceAction($id) {
		//current date
		$date = date('now');
		
		//build a new resource
        $resource = new \Ayamel\ResourceBundle\Document\Resource();
		$resource->setDateAdded($date);
		$resource->setDateModified($date);
		$resource->setTitle("Foo resource");
		$resource->setDescription("I love resources lol");

        //save it
        $dm = $this->get('doctrine.odm.mongodb.document_manager');
        $dm->persist($resource);
        $dm->flush();
        
		//at this point the resource object should now have an id
		
        return array(
            'resource' => $resource,
        );		
	}
    
    public function postResourceAction() {
        $resource = new Resource();
        $resource->setData(json_decode($this->getRequest()->getContent()));

        //
        $dm = $this->get('doctrine.odm.mongodb.document_manager');
        $dm->persist($resource);
        $dm->flush();
        
        return array(
            'resource' => $resource,
        );
    }

    /**
     * Modify a resource object by id
     */
	public function putResourceAction($id) {
		return array(
		    'id' => $id,
            'status' => 'modified',
		);
	}
    
    /**
     * Remove a resource by ID.  Note that not all data is deleted, there will still be minimal structure maintained for deleted objects.
     */
    public function deleteResourceAction($id) {
        return array(
            'id' => $id,
            'status' => 'deleted',
        );
    }
    
    /**
     * Search available objects.
     */
    public function getSearchAction() {
        //TODO: connect to elastic search
        $ops = $this->getRequest()->query->all();
        
        return $ops;
    }
}