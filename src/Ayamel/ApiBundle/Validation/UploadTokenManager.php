<?php

namespace Ayamel\ApiBundle\Validation;

use Doctrine\Common\Cache\Cache;

/**
 * Manages upload tokens for resource content.
 *
 * @author Evan Villemez
 */
class UploadTokenManager {
    
    /**
     * @var string\Doctrine\Common\Cache\Cache
     */
    protected $cache;
    
    /**
     * Prefix to use for token keys
     *
     * @var string
     */
    protected $cache_key_prefix = "ayamel_upload_token_";
    
    /**
     * Time to live for upload tokens in seconds, default 1 hour
     *
     * @var int
     */
    protected $token_ttl = 3600;
    
    /**
     * Secret used in generating hashed upload token
     *
     * @var string
     */
    protected $secret;
    
    /**
     * Constructor, needs a cache mechanism and secret to use for token generation
     *
     * @param CacheInterface $cache Cache backend used for storing tokens.
     * @param string $secret Secret used in generating token strings.
     */
    public function __construct(Cache $cache, $secret = "changeme") {
        $this->cache = $cache;
    }
    
    /**
     * Set time in seconds for how long tokens should be considered valid.
     *
     * @param int $seconds 
     * @return void
     */
    public function setTokenTtl($seconds) {
        $this->token_ttl = (int) $seconds;
    }
    
    /**
     * Return boolean if a token has been set for a given id.
     *
     * @param string $id 
     * @return boolean
     */
    public function hasTokenForId($id) {
        return $this->cache->contains($this->getTokenCacheKey($id));
    }
    
    /**
     * Retrieve the upload token for a given id.
     *
     * @param string $id 
     * @return string or boolean false if it doesn't exist
     */
    public function getTokenForId($id) {
        return $this->cache->fetch($this->getTokenCacheKey($id));
    }
    
    /**
     * Create, store, and return a new upload token for a given id.
     *
     * @param string $id 
     * @return string
     */
    public function createTokenForId($id) {
        $token = $this->generateTokenForId($id);
        $this->cache->save($this->getTokenCacheKey($id), $token, $this->token_ttl);
        return $token;
    }
    
    /**
     * Attempt to use and remove a given token for a given id.  Throws exception if any problems are detected.
     *
     * @param string $id 
     * @param string $token 
     * @throws InvalidArgumentException if a token does not exist, was expired, or does not match.
     * @return true on success
     */
    public function useTokenForId($id, $token) {
        //does token exist?
        if(!$this->hasTokenForId($id)) {
            throw new \InvalidArgumentException("The specified resource does not have an upload token.");
        }
        
        //can we actually get it? (not expired)
        if(!$storedToken = $this->getTokenForId($id)) {
            throw new \InvalidArgumentException("The token for the specified ID could not be retrived or was expired.");
        }
        
        //does token match?
        if($storedToken !== $token) {
            throw new \InvalidArgumentException("The upload token was invalid.");
        }
        
        //remove it
        $this->removeTokenForId($id);
        
        return true;
    }

    /**
     * Remove a stored upload token for a given id
     *
     * @param string $id 
     * @return void
     */
    public function removeTokenForId($id) {
        return $this->cache->delete($this->getTokenCacheKey($id));
    }
    
    /**
     * Clear all stored upload tokens, it's best not to share the underlying cache instance with anyone else
     * because of this
     */
    public function clearTokens() {
        return $this->cache->deleteAll();
    }

    protected function generateTokenForId($id) {
        $string = $id.microtime(true).$this->secret;
        return sha1($string);
    }
    
    protected function getTokenCacheKey($id) {
        return $this->cache_key_prefix.$id;
    }
}