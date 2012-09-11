<?php

namespace Ayamel\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ayamel\ResourceBundle\Document\Resource;

/**
 * A base API Controller to provide convenience methods for actions commonly performed in various places in the Ayamel Resource API.
 *
 * @author Evan Villemez
 */
abstract class ApiController extends Controller
{   
    /**
     * Get the client system for the current api request.
     *
     * @return TBD //TODO
     */
    protected function getApiClient() {
        throw new \Exception(__METHOD__." not yet implemented.");
        
        //if it's already built, get it
        if($this->container->has('ayamel.api.client')) {
            return $this->container->get('ayamel.api.client');
        }
        
        //otherwise build the client, and set
        $client = null;
        $this->container->set('ayamel.api.client', $client);
        
        return $client;
    }
    
    /**
     * Shortcut to create HttpExceptions.  Default status messages will automatically be used if no error message is specified.
     *
     * @return Symfony\Component\HttpKernel\Exception\HttpException
     */
    protected function createHttpException($code = 500, $message = null) {
        return new \Symfony\Component\HttpKernel\Exception\HttpException($code, $message);
    }
    
    /**
     * Get a resource by ID, assuming it's for an api user request.  Throw 404 and 403 exceptions if necessary.
     *
     * @param string $id    id of requested resource
     * @throws Symfony\Component\HttpKernel\Exception\HttpException(404) if resource is not found.
     * @throws Symfony\Component\HttpKernel\Exception\HttpException(403) if resource is private and requesting client is not the owner.
     * @return Ayamel\ResourceBundle\Document\Resource
     */
    protected function getRequestedResourceById($id) {

        //get repository and find requested object
		$resource = $this->container->get('ayamel.resource.manager')->getResourceById($id);

        //throw not found exception if necessary
        if(!$resource) {
            throw $this->createHttpException(404, "The requested resource does not exist.");
        }
        
        //throw access denied exception if resource isn't public and client doesn't own it
        if(!empty($restrictions = $resource->getRestrictions())) {
//          if(!in_array($this->getApiClient()->getKey(), $resource->getRestrictions())) {
                throw $this->createHttpException(403, "You are not authorized to view the requested resource.");
//          }
        }
        
        return $resource;
    }
    
    /**
     * Get an array of Resources by their ids.  Needs to handle authentication for multiple objects.  Error on one forces error on all.
     *
     * @param array $ids 
     * @return array
     * @throws Symfony\Component\HttpKernel\Exception\HttpException(403) if resource is private and requesting client is not the owner.
     */
    protected function getRequestedResourcesByIds(array $ids)
    {
        //TODO: 
    }
    
    protected function returnDeletedResource(Resource $resource) {
        return array(
            'response' => array(
                'code' => 410,
            ),
            'resource' => $resource,
        );
    }
}
