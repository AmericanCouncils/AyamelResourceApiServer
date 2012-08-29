<?php

namespace Ayamel\ApiBundle\Cache;

use Doctrine\Common\Cache\CacheProvider;

//TODO: update to implement hashing structure

/**
 * A simple file-based Doctrine cache implementation.
 *
 * @author Evan Villemez
 */
class FileCache extends CacheProvider
{
    protected $dir;
    
    /**
     * Must receive a root directory to use for file storage.
	 * Will attempt to create the given directory if it doesn't exist.
     *
     * @param string $dir 
     */
    public function __construct($dir) {
        $this->dir = $dir;
		if(!is_dir($dir)) {
			mkdir($dir, 0755, true);
		}
    }
    
    /**
     * {@inheritdoc}
     */
    protected function doFetch($id)
    {
        $file = $this->getFileForId($id);
        if(!file_exists($file)) {
            return false;
        }
        
        $contents = unserialize(file_get_contents($file));

        if($contents['expires'] !== 0 && time() > $contents['expires']) {
            $this->delete($id);
            return false;
        }
        
        return $contents['data'];
    }

    /**
     * {@inheritdoc}
     */
    protected function doContains($id)
    {
        return file_exists($this->getFileForId($id));
    }

    /**
     * {@inheritdoc}
     */
    protected function doSave($id, $data, $lifeTime = 0)
    {
        $expires = ($lifeTime === 0) ? 0 : time() + $lifeTime;
        
        $contents = array(
            'data' => $data,
            'expires' => $expires
        );

        return file_put_contents($this->getFileForId($id), serialize($contents));
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($id)
    {
        return @unlink($this->getFileForId($id));
    }

    /**
     * {@inheritdoc}
     */
    protected function doFlush()
    {
        $match = $this->dir.DIRECTORY_SEPARATOR."*.cache";
        foreach(glob($match) as $path) {
            unlink($path);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetStats()
    {
        return null;
    }
    
    protected function getFileForId($id) {
        return $this->dir.DIRECTORY_SEPARATOR.$id.".cache";
    }
    
}