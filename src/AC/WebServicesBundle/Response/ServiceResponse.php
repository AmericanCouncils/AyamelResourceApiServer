<?php

namespace AC\WebServicesBundle\Response;

class ServiceResponse {
    
    protected $statusCode;
    
    protected $responseData;
    
    protected $responseHeaders;
    
    public function __construct($data, $code = 200, $headers = array()) {
        $this->responseData = $data;
        $this->statusCode = $code;
        $this->responseHeaders = $headers;
    }
    
    public static function create($data, $code = 200, $headers = array()) {
        return new static($data, $code, $headers);
    }
    
    public function getStatusCode() {
        return $this->statusCode;
    }
    
    public function setStatusCode($code) {
        $this->statusCode = $code;
    }

    public function getResponseData() {
        return $this->responseData;
    }
    
    public function setResponseData($data) {
        $this->responseData = $data;
    }
    
    public function getResponseHeaders() {
        return $this->responseHeaders;
    }
    
    public function setResponseHeaders(array $array) {
        $this->responseHeaders = $array;
    }
    
    public function setResponseHeader($key, $val) {
        $this->responseHeaders[$key] = $val;
    }
    
}
