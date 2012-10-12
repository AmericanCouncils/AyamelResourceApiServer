<?php

namespace Ayamel\ResourceBundle\Provider;

/**
 * A UriHandlerInterface instance is a class that handles a specific scheme or schemes, providing the ability
 * to derive full resource object structures from string Uris.
 *
 * @author Evan Villemez
 */
interface ProviderInterface {
    
    /**
     * Return a unique string key to identify this provider
     *
     * @return string
     */
    function getKey();
    
    /**
     * Return boolean whether or not the given scheme can be handled by this instance.
     *
     * @param string $scheme 
     * @return boolean
     */
    function handlesScheme($scheme);
    
    /**
     * Build a full Resource object structure, providing as many fields as possible, from a given string uri.
     *
     * @param string $uri 
     * @return Ayamel\ResourceBundle\Document\Resource or false
     */
    function createResourceFromUri($uri);
    
}