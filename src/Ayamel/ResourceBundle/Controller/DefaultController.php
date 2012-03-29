<?php

namespace Ayamel\ResourceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    
    public function indexAction()
    {
		$response = new \Symfony\Component\HttpFoundation\Response;

		//cache page for 10 seconds
		$response->setPublic();
		$response->setMaxAge(10);
		
		//set themed content
		$response->setContent($this->render('AyamelResourceBundle:Default:index.html.twig'));

        return $response;
    }
}
