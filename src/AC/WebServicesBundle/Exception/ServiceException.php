<?php

namespace AC\WebServicesBundle\Exception;

/**
 * Services exceptions accept a string key as argument, with their messages and response codes being defined in your app configuration.
 *
 * @author Evan Villemez
 */
class ServiceException extends \Exception {
    protected $key;
    
    public function __construct($key) {
        $this->key = $key;
        parent::__construct();
    }
    
    public function getKey() {
        return $this->key;
    }
}