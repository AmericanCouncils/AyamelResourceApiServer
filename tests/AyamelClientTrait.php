<?php

use Guzzle\Http\Client;

/**
 * Helpers for testing the API.
 */
trait AyamelClientTrait
{
    private $ayamelClient = null;
    
    protected function getAyamelClient()
    {
        if ($this->ayamelClient) {
            return $this->ayamelClient;
        }
        
        return $this->ayamelClient = $this->createAyamelClient();
    }
    
    protected function getJson($route, $filters = [])
    {
        $res = $this->callAyamel('GET', $route, $filters, ['Accept' => 'application/json']);
        
        return json_decode($res->getBody(true), true);
    }
    
    protected function deleteJson($route, $filters = [])
    {
        $res = $this->callAyamel('DELETE', $route, $filters, ['Accept' => 'application/json']);
        
        return json_decode($res->getBody(true), true);
    }

    protected function putJson($route, $data = [], $filters = [])
    {
        $res = $this->callAyamel('PUT', $route, $filters, [
            'Accept' => 'application/json', 
            'Content-Type' => 'application/json'
        ], json_encode($data));
        
        return json_decode($res->getBody(true), true);
    }
    
    protected function postJson($route, $data = [], $filters = [])
    {
        $res = $this->callAyamel('POST', $route, $filters, [
            'Accept' => 'application/json', 
            'Content-Type' => 'application/json'
        ], json_encode($data));
        
        return json_decode($res->getBody(true), true);
    }
    
    protected function callAyamel($method, $route, $filters = [], $headers = [], $body = null, $options = [])
    {
        //add a couple of parameters to every request
        $filters['_key'] = AYAMEL_CLIENT_API_KEY;
        $filters['_suppress_codes'] = 'true'; //Guzzle throws exceptions for a response not in the 200-300 range, meh
        
        //assemble query string
        $queryFilters = [];
        foreach ($filters as $key => $val) {
            $queryFilters[] = urlencode((string) $key) .'='. urlencode((string) $val);
        }
        
        $url = $route.'?'.implode('&', $queryFilters);
        
        return $this->getAyamelClient()->createRequest($method, $url, $headers, $body, $options)->send();
    }
    
    protected function createAyamelClient()
    {
        if (!defined('AYAMEL_DOMAIN') || !defined('AYAMEL_CLIENT_API_KEY')) {
            throw new RuntimeException("You must provide values for AYAMEL_DOMAIN and AYAMEL_CLIENT_API_KEY in config.ini in order to run these tests.");
        }
        
        $client = new Client(AYAMEL_DOMAIN);
        
        return $client;
    }
}
