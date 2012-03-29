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
        $dm = $this->get('doctrine.odm.mongodb.document_manager');
        return array(
            'id' => $id,
            'status' => 'ok'
        );
	}
    
    public function postResourceAction() {
        $resource = new Resource();
        $resource->setData(json_decode($this->getRequest()->getContent()));

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