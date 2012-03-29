<?php
namespace Ayamel\ResourceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Content manipulation for resource objects.
 */    
class ResourceContentApiV1Controller extends Controller {

	public function getResourceContentCreationTokenAction() {
		
	}

	public function postResourceContentUploadAction($id, $token) {
		//check token
	}
    
}
