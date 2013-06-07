<?php

namespace Ayamel\ClientBundle\Loader;

use Ayamel\ClientBundle\Client;

class ConfigClientLoader implements ClientLoaderInterface
{
    protected $clients = array();
    
    public function __construct($data = array())
    {
        foreach ($data as $item)
        {
            $c = new Client();
            $c->id = $item['id'] ?: null;
            $c->name = $item['name'] ?: null;
            $c->apiKey = $item['apiKey'] ?: null;
            
            $this->clients[] = $c;
        }
    }
    
    public function getClients()
    {
        return $this->clients;
    }
    
    public function getClient($id)
    {
        foreach ($this->clients as $client) {
            if ($id === $client->id) {
                return $client;
            }
        }
        
        return false;
    }
    
    public function getClientByApiKey($key)
    {
        foreach ($this->clients as $client) {
            if ($key === $client->apiKey) {
                return $client;
            }
        }
        
        return false;
    }
}
