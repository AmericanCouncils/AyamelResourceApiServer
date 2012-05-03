<?php

namespace Ayamel\ResourceBundle\Provider;

/**
 * Handler for http/https
 *
 * @author Evan Villemez
 */
class HttpProvider extends AbstractFilePathProvider {
    
    /**
     * {@inheritdoc}
     */
    function getKey() {
        return 'http';
    }
    
    /**
     * {@inheritdoc}
     */
    function handlesScheme($scheme) {
        return in_array(strtolower($scheme), array('http','https'));
    }
    
}