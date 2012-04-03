<?php

namespace Ayamel\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class Homepage extends Controller
{
	public function indexAction() {
		return $this->render("AyamelApiBundle:Default:home.html.twig");
	}
}
