<?php

namespace Ayamel\ResourceApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Ayamel\ResourceApiBundle\ApiTester;

class Homepage extends Controller
{
	public function indexAction(Request $request) {
		$form = $this->buildForm($request);

		if($request->getMethod() === 'POST') {
			if($form->isValid()) {
				//get form values
				$base_url = $form['base_url']->getData();
				$route = $form['route']->getData();
				$method = strtolower($form['method']->getData());
				$data = (null !== $form['client_data']->getData()) ? json_decode($form['client_data']->getData()) : null;
				
				//call api
				$api = new ApiTester($base_url);
				$result = $api->$method($route, $data);
				
				//set result
				$responseDebug = $api->debugLastQuery();
			}
		} else {			
			$responseDebug = "None";
		}
		
		//return page template
		return $this->render("AyamelResourceApiBundle:Default:home.html.twig", array(
			'form' => $form->createView(),
			'response_debug' => $responseDebug,
		));
	}
	
	protected function buildForm(Request $request) {
		$builder = $this->createFormBuilder();
		
		//build the form
		$form = $builder
			->add('base_url', 'text', array(
				'label' => "Base Api Url: ",
				'data' => $request->getScheme()."://".$request->getHost().$request->getBaseUrl()."/api/v1/rest",
				'required' => true,
			))->add('method', 'choice', array(
				'label' => "Http Method: ",
				'choices' => array(
					"GET" => "GET",
					"POST" => "POST",
					"PUT" => "PUT",
					"DELETE" => "DELETE"
				),
			))->add('route', 'text', array(
				'label' => "Api Route: ",
				'required' => true,
				'data' => '/resources/1234'
			))->add('client_data', 'textarea', array(
				"label" => "Client Data (JSON): ",
				"data" => '{"key": "value"}'
			))->getForm();
		
		//bind request if it was submitted
		if($request->getMethod() === 'POST') {
			$form->bindRequest($request);
		}
		
		return $form;
	}
}
