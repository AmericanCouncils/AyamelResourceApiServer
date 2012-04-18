<?php

namespace Ayamel\ResourceApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class Homepage extends Controller
{
	public function indexAction() {
		
		//TODO: build and process form, implement the logic from apitest.php
		
		return $this->render("AyamelResourceApiBundle:Default:home.html.twig");
	}
}
