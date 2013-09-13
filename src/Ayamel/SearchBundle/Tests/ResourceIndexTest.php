<?php

use Ayamel\ApiBundle\ApiTestCase;
use Guzzle\Http\Client;

class ResourceIndexTest extends ApiTestCase
{
    public function testCreateIndex()
    {
        $this->runCommand('fos:elastica:reset');

        //index should exist
        $client = new Client('http://127.0.0.1:9200');
        $response = $client->get('/ayamel/resource/_mapping')->send();
        $this->assertSame(200, $response->getStatusCode());
    }
}
