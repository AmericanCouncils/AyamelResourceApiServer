<?php

namespace Ayamel\ResourceApiBundle\Filesystem;

/**
 * Implements local file storage for Resource objects.
 *
 * @author Evan Villemez
 */
class LocalFilesystem implements FilesystemInterface {
	
    /**
     * @var string - the base filepath used for all resource file storage
     */
    protected $rootDir;
    
    /**
     * @var string - a secret string used for generating secret hashes unique to a file and resource id
     */
    protected $secret;
    
    /**
     * undocumented function
     *
     * @param string $dir 
     * @param string $secret 
     * @author Evan Villemez
     */
    public function __construct($dir, $secret = 'changeme') {
        $this->rootDir = $dir;
        $this->secret = $secret;
    }
    
    /**
    * {@inheritdoc}
     */
    public function generateBaseDirectoryForId($id) {
        $secret = $this->createFileSecretForId($id);
        $hash1 = $secret[1].$secret[3].$secret[5];
        $hash2 = $secret[4].$secret[9].$secret[11];
        
        return $this->rootDir.DIRECTORY_SEPARATOR.$hash1.DIRECTORY_SEPARATOR.$hash2;
    }

    /**
     * {@inheritdoc}
     */
    public function generateBasePathForId($id) {
        return $this->generateBaseDirectoryForId($id).DIRECTORY_SEPARATOR.$this->generateBaseFilenameForId($id);
    }
    
    /**
     * {@inheritdoc}
     */
    public function generateBaseFilenameForId($id) {
        return $id."_".$this->createFileSecretForId($id)."_";
    }

    /**
     * {@inheritdoc}
     */
    public function removeFile($path) {
        return unlink($path);
    }
    
    /**
     * {@inheritdoc}
     */
    public function removeFilesForId($id) {
        foreach($this->getFilesForId($id) as $path) {
            $this->removeFile($path);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function getFilesForId($id) {
        $pattern = $this->generateBasePathForId($id).DIRECTORY_SEPARATOR.$this->generateBaseFilenameForId($id)."*";
        $files = array();

        foreach(glob($pattern) as $path) {
            $files[] = FileReference::createFromLocalPath($path);
        }

        return $files;
    }
    
    /**
     * {@inheritdoc}
     */
    public function addFileForId($id, FileReference $file, $newBasename = null, $copy = false, $onConflict = FilesystemInterface::CONFLICT_OVERWRITE) {
        if(!file_exists($file->getInternalUri())) {
            throw new \InvalidArgumentException("File missing, or not available on local file system.");
        }

        //make the containing dir if it doesn't exist
        $base = $this->generateBasePathForId($id);
        $dir = dirname($base);
        if(!file_exists($dir)) {
            if(!mkdir($dir, 0755, true)) {
                throw new \RuntimeException(sprintf("%s could not create a required directory for file storage.", __CLASS__));
            }
        }
        
        //generate the new absolute file name
        $filename = ($newBasename) ? $base.$newBasename : $base.basename($file->getInternalUri());
        
        //check for conflicts
        if(file_exists($filename)) {
            if($onConflict === FilesystemInterface::CONFLICT_EXCEPTION) {
                throw new \RuntimeException(sprintf("%s Cannot overwrite a pre-existing file.", __CLASS__));
            }
        }

        //copy or move file to new location
        if($copy) {
            if(copy($file->getInternalUri(), $filename)) {
                return FileReference::createFromLocalPath($filename);
            }
        } else {
            if(rename($file->getInternalUri(), $filename)) {
                return FileReference::createFromLocalPath($filename);
            }
        }
        
        return false;
    }
    
    protected function createFileSecretForId($id) {
        //TODO: finish this
        return substr($hash, 3, 15);
    }
    
    
}