<?php

namespace Ayamel\ApiBundle\Client;

class ConfigClientLoader implements ClientLoaderInterface
{
    protected $clients = array();

    public function __construct($data = array())
    {
        foreach ($data as $id => $item) {
            $c = new Client();
            $c->id = $id;
            $c->name = $item['name'] ?: null;
            $c->apiKey = $item['apiKey'] ?: null;

            $this->clients[$id] = $c;
        }
    }

    public function getClients()
    {
        return $this->clients;
    }

    public function getClient($id)
    {
        return isset($this->clients[$id]) ? $this->clients[$id] : false;
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
