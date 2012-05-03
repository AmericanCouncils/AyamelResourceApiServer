<?php

namespace Ayamel\ResourceApiBundle\Filesystem;

use Ayamel\ResourceBundle\Document\FileReference;

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
     * Constructor requires a root directory (should be an absolute path), and a secret for use in hash generation.
     *
     * @param string $dir - Absolute local file system path for use as root point.
     * @param string $secret - A secret to use when generating hashes.
     */
    public function __construct($dir, $secret = 'changeme') {
        $this->rootDir = $dir;
        $this->secret = $secret;
    }
    
    /**
     * {@inheritdoc}
     *
     * Uses md5() of the received id to generate a 3 level hashed directory structure, using 2 hex characters per directory (0-9, a-f).  This yields
     * 16,777,216 possible directories, with no more than 256 subdirectories per containing directory, and should guarantee relatively uniform file distribution among them.
     */
    public function generateBaseDirectoryForId($id) {
        $hash = md5((string) $id);
        $end = strlen($hash);
        $hashDirs = $hash[$end-1].$hash[$end-2].DIRECTORY_SEPARATOR.$hash[$end-3].$hash[$end-4].DIRECTORY_SEPARATOR.$hash[$end-5].$hash[$end-6];
        
        return $this->rootDir.DIRECTORY_SEPARATOR.$hashDirs;
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
    public function removeFileForId($id, $name) {
        return $this->removeFile($this->generateBasePathForId($id).$name);
    }
    
    /**
     * {@inheritdoc}
     */
    public function removeFilesForId($id) {
        foreach($this->getFilesForId($id) as $ref) {
            $this->removeFile($ref->getInternalUri());
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function getFilesForId($id) {
        $pattern = $this->generateBasePathForId($id)."*";
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
        if(!is_dir($dir)) {
            if(!mkdir($dir, 0755, true)) {
                throw new \RuntimeException(sprintf("%s could not create a required directory for file storage.", __CLASS__));
            }
        }
        
        //generate the new absolute file name
        $filename = ($newBasename) ? $base.$newBasename : $base.basename($file->getInternalUri());
        
        //check for conflicts
        if(is_file($filename)) {
            if($onConflict === FilesystemInterface::CONFLICT_EXCEPTION) {
                throw new \RuntimeException(sprintf("%s cannot overwrite a pre-existing file.", __CLASS__));
            } else {
                unlink($filename);
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
        
    /**
     * {@inheritdoc}
     */
    public function getCount($includeDirectories = false) {
        $ds = DIRECTORY_SEPARATOR;
        $fCount = 0;
        $dCount = 0;

        //TODO: this may scale terribly with lots of files, might want consider implementing with readdir instead
        foreach(scandir($this->rootDir) as $lvl1) {
            if($lvl1 === '.' || $lvl1 === '..') continue;
            foreach(scandir($this->rootDir.$ds.$lvl1) as $lvl2) {
                if($lvl2 === '.' || $lvl2 === '..') continue;
                foreach(scandir($this->rootDir.$ds.$lvl1.$ds.$lvl2) as $lvl3) {
                    if($lvl3 === '.' || $lvl3 === '..') continue;
                    foreach(scandir($this->rootDir.$ds.$lvl1.$ds.$lvl2.$ds.$lvl3) as $file) {
                        if($file === '.' || $file === '..') continue;
                        $fCount++;
                    }
                    $dCount++;
                }
                $dCount++;
            }
            $dCount++;
        }
        
        return ($includeDirectories) ? $fCount + $dCount : $fCount;
    }    
    
    public function createFileSecretForId($id) {
        $hash = sha1($id.$this->secret);
        return substr($hash, 3, 15);
    }

}