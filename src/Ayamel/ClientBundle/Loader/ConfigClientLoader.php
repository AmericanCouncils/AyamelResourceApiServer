<?php

namespace Ayamel\ClientBundle\Loader;

use Symfony\Component\HttpFoundation\Request;

class ConfigClientLoader implements ClientLoaderInterface
{
    protected $data;
    
    public function __construct($clientData = array())
    {
        $this->data = $data;
    }
    
    public function loadClientByRequest(Request $req)
    {
        $key = $req->query->get('_api_key', false);
        if ($key) {
            
        }
    }
}