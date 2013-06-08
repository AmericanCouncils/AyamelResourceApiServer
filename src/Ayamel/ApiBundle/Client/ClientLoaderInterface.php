<?php

namespace Ayamel\ApiBundle\Client;

use Symfony\Component\HttpFoundation\Request;

/**
 * An instance of ClientLoaderInterface can load API client instances from a source.
 *
 * @package AyamelClientBundle
 * @author Evan Villemez
 */
interface ClientLoaderInterface
{
    public function getClients();
    
    public function getClient($id);
    
    public function getClientByApiKey($key);
}
