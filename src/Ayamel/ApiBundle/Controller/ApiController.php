<?php

namespace Ayamel\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ayamel\ResourceBundle\Document\Resource;

abstract class ApiController extends Controller
{
	
	protected function getApiClient() {
		throw new \Exception(__METHOD__." not yet implemented.");
		
		$client = null;
		$this->container->set('ayamel.api.client', $client);
		return $client;
	}
	
	protected function createHttpException($code = 500, $message = null) {
		return new \Symfony\Component\HttpKernel\Exception\HttpException($code, $message);
	}
}
