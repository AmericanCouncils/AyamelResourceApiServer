<?php

namespace Ayamel\ResourceApiBundle;

/**
 * Simple class to wrap curl for testing resource api routes.  If JSON is returned by the api, the structure will be automatically decoded, returning a standard PHP object.
 *
 * Example Usage:
 * 	//get a resource
 * 	$api = new ApiTester('http://localhost/api/v1/rest');
 * 	$result = $api->get('/resources/23456asdf2sf3');
 * 	$code = $result->response->code;
 * 	$resource = $result->resource;
 * 	$title = $resource->title;
 *
 *	//modify the resource
 *	$data = array(
 * 		'title' => "I changed the title",				//changes title
 * 		'description' => "... and the description",		//changes description
 * 		'keywords' => null								//will remove values for 'keywords'
 * 	);
 *	$api->put('/resources/23456asdf2sf3', $data);
 *
 */
class ApiTester {
	
	protected $base_url = false;
	protected $last_code = false;
	protected $last_type = false;
	protected $last_result = false;
	protected $query_time = false;
	protected $queryParams = false;
	
	public function __construct($base_url = null, $queryParams = array()) {
		if($base_url) {
			$this->base_url = $base_url;
		}
		$this->queryParams = $queryParams;
	}
	
	public function setBaseUrl($string) {
		$this->base_url = $string;
	}
	
	public function getLastResponseCode() {
		return $this->last_code;
	}

	public function getLastResponseType() {
		return $this->last_type;
	}
	
	public function getLastQueryTime() {
		return $this->query_time;
	}
	
	public function get($uri, $data = null, $params = array()) {
		return $this->call('GET', $uri, $data, $params);
	}
	
	public function put($uri, $data = null, $params = array()) {
		return $this->call('PUT', $uri, $data, $params);
	}
	
	public function post($uri, $data = null, $params = array()) {
		return $this->call('POST', $uri, $data, $params);
	}
	
	public function delete($uri, $data = null, $params = array()) {
		return $this->call('DELETE', $uri, $data, $params);
	}
	
	public function debugLastQuery() {
		return
"<h3>Query Debug</h3>
<pre>
    Query Time: ".$this->getLastQueryTime()." ms
    Response Code: ".$this->getLastResponseCode()."
    Response Type: ".$this->getLastResponseType()."
    Response Body: 

".print_r($this->last_result, true)."
</pre>";
	}
	
	protected function call($method, $uri, $data = null, $params = array()) {
		//if not fully qualified, prepend base_url, strip slashes
		if(0 !== strpos(strtolower($uri), 'http') && $this->base_url) {
			$uri = rtrim($this->base_url."/".ltrim($uri, "/"), "/");
		}
		
		//encode data if any
		if($data !== null) {
			if(!$data = @json_encode($data)) {
				$data = http_build_query($data);
			}
		}

		//build query string parameters if specified
		$queryParams = array_merge($this->queryParams, $params);
		$q = (!empty($queryParams)) ? http_build_query($queryParams) : null;
		
		$uri .= $q;

		//build curl object
        $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		//set http request method
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		
		//send encoded data if exists
		if(null !== $data) {
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
 	   
		//execute, store query data, and return response
		$startTime = microtime(true);
		$content = curl_exec($ch);
		$this->query_time = (microtime(true)-$startTime) * 1000;
		$this->last_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$this->last_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
		$this->last_result = $content;
		
		//return result, json_decoding if possible
		return ($data = @json_decode($content)) ? $data : $content;
	}
}
