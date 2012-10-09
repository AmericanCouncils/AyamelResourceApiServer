<?php

namespace AC\WebServicesBundle\Response;

/**
 * Note that this does not extend the HttpFoundation Response for a reason, so there is some duplicate functionality.
 *
 * @package ACWebServicesBundle
 * @author Evan Villemez
 */
class ServiceResponse {
    
    protected $statusCode;
    
    protected $responseData;
    
    protected $responseHeaders;
    
    protected $template;
    
    public function __construct($data, $code = 200, $headers = array(), $template = null) {
        $this->responseData = $data;
        $this->statusCode = $code;
        $this->responseHeaders = $headers;
        $this->template = $template;
    }
    
    public static function create($data, $code = 200, $headers = array(), $template = null)
    {
        return new static($data, $code, $headers, $template);
    }
    
    public function getResponseData()
    {
        return $this->responseData;
    }
    
    public function setResponseData($data)
    {
        $this->responseData = $data;
    }
    
    public function getResponseHeaders()
    {
        return $this->responseHeaders;
    }
    
    public function setResponseHeaders(array $array)
    {
        $this->responseHeaders = $array;
    }
    
    public function setResponseHeader($key, $val)
    {
        $this->responseHeaders[$key] = $val;
    }
    
    public function getTemplate()
    {
        return $this->template;
    }
    
    public function setTemplate($template)
    {
        $this->template = $template;
    }
    
    
}
