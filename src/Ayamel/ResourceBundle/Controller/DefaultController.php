<?php

namespace Ayamel\ResourceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    
    public function indexAction()
    {
		$response = new \Symfony\Component\HttpFoundation\Response;
		$response->setContent($this->render('AyamelResourceBundle:Default:index.html.twig'));

		//set cache age and stuff
		$response->setPublic();
		$response->setMaxAge(10);

        return $response;
    }
}
