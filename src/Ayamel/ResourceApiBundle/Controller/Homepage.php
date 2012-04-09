<?php

namespace Ayamel\ResourceApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class Homepage extends Controller
{
	public function indexAction() {
		return $this->render("AyamelResourceApiBundle:Default:home.html.twig");
	}
}
