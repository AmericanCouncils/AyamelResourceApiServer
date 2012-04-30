<?php

namespace Ayamel\ResourceBundle\Provider;

/**
 * Provider for local files
 *
 * @author Evan Villemez
 */
class LocalFilepathProvider extends AbstractFilePathProvider {
    
    /**
     * {@inheritdoc}
     */
    function getKey() {
        return 'local';
    }
    
    /**
     * {@inheritdoc}
     */
    function handlesScheme($scheme) {
        if(0 === strpos($scheme, "/")) {
            return true;
        }
        
        return ("file" === strtolower($scheme));
    }
    
}