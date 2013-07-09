<?php

namespace Ayamel\ApiBundle\Client;

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
