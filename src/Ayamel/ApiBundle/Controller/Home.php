<?php
namespace Ayamel\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class Home extends Controller
{

    public function buildHomePage()
    {
        return $this->render("AyamelApiBundle::home.html.twig");
    }

}
