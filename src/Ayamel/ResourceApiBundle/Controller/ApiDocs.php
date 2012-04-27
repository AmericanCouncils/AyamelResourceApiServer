<?php

namespace Ayamel\ResourceApiBundle\Controller;

use Ayamel\ResourceApiBundle\Controller\ApiController;
use Ayamel\ResourceBundle\Document\Resource;

class ApiDocs extends ApiController {
    
    public function indexAction() {
        return $this->render("AyamelResourceApiBundle:Default:docs.html.twig");
    }
	
}
