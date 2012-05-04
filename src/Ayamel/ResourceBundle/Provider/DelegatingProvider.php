<?php

namespace Ayamel\ResourceBundle\Provider;

/**
 * This class manages multiple providers, returning resource objects from the first
 * provider that can handle a given uri.
 *
 * @author Evan Villemez
 */
class DelegatingProvider implements ProviderInterface {
    
    protected $providers = array();
    
    public function getKey() {
        return "delegator";
    }
    
    /**
     * Make sure that local file paths are treated with the 'file://' scheme.
     *
     * @param string $scheme 
     * @return string
     */
    protected function checkScheme($scheme) {
        if(0 === strpos($scheme, "/")) {
            return 'file';
        }

        return $scheme;
    }
    
    /**
     * {@inhertdoc}
     */
    public function handlesScheme($scheme) {
        $scheme = $this->checkScheme($scheme);
        foreach($this->providers as $provider) {
            if($provider->handlesScheme($scheme)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
    * {@inheritdoc}
    */
    public function createResourceFromUri($uri) {
        $exp = explode("://", $uri);
        $scheme = $exp[0];
        
        //TODO: take into account local file paths, transform to file://
        
        $scheme = $this->checkScheme($scheme);
        
        foreach($this->providers as $provider) {
            if($provider->handlesScheme($scheme)) {
                if($resource = $provider->createResourceFromUri($uri)) {
                    return $resource;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Add a provider for deriving resources.
     *
     * @param ProviderInterface $provider 
     * @return void
     */
    public function addProvider(ProviderInterface $provider) {
        $this->providers[$provider->getKey()] = $provider;
    }
    
    /**
     * Remove a provider by key
     *
     * @param string $key - key of provider to remove
     * @return void
     */
    public function removeProvider($key) {
        if(isset($this->providers[$key])) {
            unset($$this->providers[$key]);
        }
    }
    
    /**
     * Get a specific provider instance by key
     *
     * @param string $key - key of provider to get
     * @return ProviderInterface, or FALSE if it doesn't exist
     */
    public function getProvider($key) {
        return isset($this->providers[$key]) ? $this->providers[$key] : false;
    }
    
    /**
     * Return true/false if provider exists
     *
     * @param string $key 
     * @return boolean
     */
    public function hasProvider($key) {
        return isset($this->providers[$key]);
    }
    
    /**
     * Set the array of providers, will remove any previously added providers.
     *
     * @param array $providers 
     * @return void
     */
    public function setProviders(array $providers) {
        $this->providers = array();
        foreach($providers as $provider) {
            $this->addProvider($provider);
        }
    }
    
    /**
     * Get array of all all registered providers
     *
     * @return array
     */
    public function getProviders() {
        return $this->providers;
    }
}
