<?php
namespace Ayamel\ResourceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ResourceApiController extends Controller {

	public function getResourceAction($id) {
		return array('id' => $id);
	}

	public function putResourceAction($id, $data = array()) {
		return array('id' => $id, 'data' => $data);
	}
}