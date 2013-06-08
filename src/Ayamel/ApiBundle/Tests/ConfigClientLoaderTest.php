<?php

namespace Ayamel\ApiBundle\Tests;

use Ayamel\ApiBundle\Client\ClientLoaderInterface;
use Ayamel\ApiBundle\Client\ConfigClientLoader;
use Ayamel\ApiBundle\Client\Client;

class ConfigClientLoaderTest extends \PHPUnit_Framework_TestCase
{

    protected function getLoader()
    {
        return new ConfigClientLoader(array(
            'test_client' => array(
                'name' => "Test name",
                'apiKey' => "dddddddddddddd"
            ),
            'test_client2' => array(
                'name' => "Test name",
                'apiKey' => "ffffffffffffffff"
            )
        ));
    }

    public function testInstantiate()
    {
        $l = new ConfigClientLoader();
        $this->assertTrue($l instanceof ClientLoaderInterface);
    }
    
    public function testGetClients()
    {
        $l = $this->getLoader();
        
        $clients = $l->getClients();
        $this->assertSame(2, count($clients));
        
        foreach($clients as $client) {
            $this->assertTrue($client instanceof Client);
        }
    }
    
    public function testGetClient()
    {
        $l = $this->getLoader();
        $c = $l->getClient('test_client2');
        $this->assertTrue($c instanceof Client);
        $this->assertSame('test_client2', $c->id);
        
        $this->assertFalse($l->getClient('foo'));
    }
    
    public function testGetClientByApiKey()
    {
        $l = $this->getLoader();
        $c = $l->getClientByApiKey('ffffffffffffffff');
        $this->assertTrue($c instanceof Client);
        $this->assertSame('test_client2', $c->id);
        $this->assertFalse($l->getClientByApiKey('foo'));
    }
}
