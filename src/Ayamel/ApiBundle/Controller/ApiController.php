<?php

namespace Ayamel\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ayamel\ResourceBundle\Document\Resource;
use AC\WebServicesBundle\Response\ServiceResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
/**
 * A base API Controller to provide convenience methods for actions commonly performed in various places in the Ayamel Resource API.
 *
 * @author Evan Villemez
 */
abstract class ApiController extends Controller
{
    protected function getRepo($class)
    {
        return $this->container->get('doctrine_mongodb')->getManager()->getRepository($class);
    }

    protected function getDocManager()
    {
        return $this->container->get('doctrine_mongodb')->getManager();
    }

    protected function requireAuthentication()
    {
        if (!$key = $this->getRequest()->get('_key', false)) {
            throw new HttpException(401, "Valid API key required.");
        }
        
        if (!$client = $this->container->get('ayamel.client_loader')->getClientByApiKey($key)) {
            throw new HttpException(401, "Valid API key required.");
        }
    }

    protected function requireResourceOwner(Resource $resource)
    {
        if ($this->getApiClient()->id !== $resource->getClient()->getId()) {
            throw new HttpException(403, "You do not own the requested Resource.");
        }
    }

    /**
     * Get the client system based on the API key passed in the current
     * request.
     *
     * @return Client|false
     */
    protected function getApiClient()
    {
        if (!$key = $this->container->get('request')->get('_key', false)) {
            return false;
        }
        
        return $this->container->get('ayamel.client_loader')->getClientByApiKey($key);
    }

    /**
     * Shortcut to create HttpExceptions.  Default status messages will automatically be used if no error message is specified.
     *
     * @return Symfony\Component\HttpKernel\Exception\HttpException
     */
    protected function createHttpException($code = 500, $message = null)
    {
        return new HttpException($code, $message);
    }

    /**
     * Shortcut to create a ServiceResponse
     *
     * @param  string          $data
     * @param  string          $code
     * @param  array           $headers
     * @param  string          $template
     * @return ServiceResponse
     */
    protected function createServiceResponse($data, $code, $headers = array(), $template = null)
    {
        return new ServiceResponse($data, $code, $headers, $template);
    }

    /**
     * Get a resource by ID, assuming it's for an api user request.  Throw 404 and 403 exceptions if necessary.
     *
     * @param  string                                                    $id id of requested resource
     * @throws Symfony\Component\HttpKernel\Exception\HttpException(404) if resource is not found.
     * @throws Symfony\Component\HttpKernel\Exception\HttpException(401) if resource is private and no client API key was provided.
     * @throws Symfony\Component\HttpKernel\Exception\HttpException(403) if resource is private and requesting client is not the owner.
     * @return Ayamel\ResourceBundle\Document\Resource
     */
    protected function getRequestedResourceById($id)
    {
        //get repository and find requested object
        $resource = $this->getRepo('AyamelResourceBundle:Resource')->find($id);

        //throw not found exception if necessary
        if (!$resource) {
            throw new HttpException(404, "The requested resource does not exist.");
        }

        //throw access denied exception if resource has visibility restrictions
        $visibility = $resource->getVisibility();
        if (!empty($visibility)) {
            if (!$client = $this->getApiClient()) {
                throw new HttpException(401, "Valid API key required.");
            }
            
            if (($client->id !== $resource->getClient()->getId()) || !in_array($client->id, $visibility)) {
                throw new HttpException(403, "Not authorized.");
            }
        }

        return $resource;
    }
    
    /**
     * Filter an array of Resources to exclude Resources not visible to the requesting client.
     *
     * @param array Array of Resources
     * @return array Array of Resources
     */
    protected function filterVisibleResources(array $resources)
    {
        $visibleResources = array();
        $client = $this->getApiClient();
        
        foreach ($resources as $resource) {
            if (!$client && null === $resource->getVisibility()) {
                $visibleResources[] = $resource;
            } else {
                if (in_array($client->id, $resource->getVisibility())) {
                    $visibleResources[] = $resource;
                }
            }
        }
        
        return $visibleResources;
    }

    protected function returnDeletedResource(Resource $resource)
    {
        //TODO: deleted resources can be cached indefinitely, implement this
        return new ServiceResponse(array('resource' => $resource), 410);
    }
}
