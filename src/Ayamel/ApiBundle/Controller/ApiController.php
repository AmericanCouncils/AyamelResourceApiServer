<?php

namespace Ayamel\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ayamel\ResourceBundle\Document\Resource;

class ApiController extends Controller
{
	
	protected function getApiClient() {
		throw new \Exception(__METHOD__." not yet implemented.");
	}
	
	protected function getApiDispatcher() {
		return $this->container->get('ayamel_api_dispatcher');
	}
	
	protected function resourceToJson(Resource $resource) {
		return $resource;
	}
	
}
