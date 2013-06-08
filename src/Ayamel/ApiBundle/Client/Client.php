<?php

namespace Ayamel\ApiBundle\Client;

use Ayamel\ResourceBundle\Document\Client as ClientDoc;

class Client
{
    public $id;
    
    public $name;
    
    public $apiKey;
    
    public $url;
    
    /**
     * Creates the MongoDB client document nested in Resource documents.
     *
     * @return Ayamel\ResourceBundle\Document\Client
     */
    public function createClientDocument()
    {
        $doc = new ClientDoc();
        
        $doc->setId($this->id);
        $doc->setName($this->name);

        if (isset($this->url)) {
            $doc->setUri($this->url);
        }
        
        return $doc;
    }
}
