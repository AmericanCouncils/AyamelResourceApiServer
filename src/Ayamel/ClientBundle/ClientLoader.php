<?php

namespace Ayamel\ClientBundle;

use Symfony\Component\HttpFoundation\Request;

class ClientLoader
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