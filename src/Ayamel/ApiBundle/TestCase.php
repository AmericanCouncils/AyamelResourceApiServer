<?php

namespace Ayamel\ApiBundle;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class TestCase extends WebTestCase
{
	
    protected function getResponse($method, $uri, $params = array(), $files = array(), $server = array(), $content = null, $changehistory = true)
    {
        $client = static::createClient(array(
            'environment' => 'test',
            'debug' => true
        ));
        
        $client->request($method, $uri, $params, $files, $server, $content, $changehistory);
        
        return $client->getResponse();
    }
    
    protected function getJson($method, $uri, $params = array(), $files = array(), $server = array(), $content = null, $changehistory = true)
    {
        return json_decode($this->getResponse($method, $uri, $params, $files, $server, $content, $changehistory)->getContent());
    }
    
	protected function runCommand($string)
    {
        throw new \Exception("Not yet implemented.");
    }
	
}
