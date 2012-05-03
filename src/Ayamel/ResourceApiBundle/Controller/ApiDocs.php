<?php

namespace Ayamel\ResourceApiBundle\Controller;

use Ayamel\ResourceApiBundle\Controller\ApiController;
use Ayamel\ResourceBundle\Document\Resource;

/**
 * Render documentation page.  This probably will be preplaced with the NelmioApiDocsBundle.
 *
 * @author Evan Villemez
 */
class ApiDocs extends ApiController {
    
    public function indexAction() {
        return $this->render("AyamelResourceApiBundle:Default:docs.html.twig");
    }
	
}
