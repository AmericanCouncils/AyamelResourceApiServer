<?php

namespace Ayamel\ClientBundle\Tests;

use Ayamel\ClientBundle\Loader\ClientLoaderInterface;
use Ayamel\ClientBundle\Loader\ConfigClientLoader;
use Ayamel\ClientBundle\Client;

class ConfigClientLoaderTest extends \PHPUnit_Framework_TestCase
{

    protected function getLoader()
    {
        return new ConfigClientLoader(array(
            array(
                'id' => 'test_client',
                'name' => "Test name",
                'apiKey' => "dddddddddddddd"
            ),
            array(
                'id' => 'test_client2',
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
