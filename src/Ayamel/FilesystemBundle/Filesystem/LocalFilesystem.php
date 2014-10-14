<?php

namespace Ayamel\FilesystemBundle\Filesystem;

use Ayamel\ResourceBundle\Document\FileReference;

/**
 * Implements local file storage for Resource objects.
 *
 * @author Evan Villemez
 */
class LocalFilesystem implements FilesystemInterface
{
    /**
     * @var string - the base filepath used for all resource file storage
     */
    protected $rootDir;

    /**
     * @var string - a secret string used for generating secret hashes unique to a file and resource id
     */
    protected $secret;

    /**
     * @var string - the root public uri from which files in this system are accessible from the web
     */
    protected $publicRootUri;

    /**
     * Constructor requires a root directory (should be an absolute path), and a secret for use in hash generation.
     *
     * @param string $dir    - Absolute local file system path for use as root point.
     * @param string $secret - A secret to use when generating hashes.
     */
    public function __construct($dir, $secret = 'changeme', $publicRootUri = false)
    {
        $this->rootDir = $dir;
        $this->secret = $secret;
        $this->publicRootUri = $publicRootUri;
    }

    /**
     * {@inheritdoc}
     *
     * This implementation uses an md5() hash of the received id to generate a 3 level hashed directory structure, using 2 hex characters per directory (0-9, a-f).  This yields
     * 16,777,216 possible directories, with no more than 256 subdirectories per containing directory, and should guarantee relatively uniform file distribution among them.
     */
    public function generateBaseDirectoryForId($id)
    {
        $hash = md5((string) $id);
        $end = strlen($hash);
        $hashDirs = $hash[$end-1].$hash[$end-2].DIRECTORY_SEPARATOR.$hash[$end-3].$hash[$end-4].DIRECTORY_SEPARATOR.$hash[$end-5].$hash[$end-6];

        return $this->rootDir.DIRECTORY_SEPARATOR.$hashDirs;
    }

    /**
     * {@inheritdoc}
     */
    public function generateBasePathForId($id)
    {
        return $this->generateBaseDirectoryForId($id).DIRECTORY_SEPARATOR.$this->generateBaseFilenameForId($id);
    }

    /**
     * {@inheritdoc}
     */
    public function generateBaseFilenameForId($id)
    {
        return $id."_".$this->createFileSecretForId($id)."_";
    }

    /**
     * {@inheritdoc}
     */
    public function getIdForFile(FileReference $ref)
    {
        if (!$uri = $ref->getInternalUri()) {
            return false;
        }
        $exp = explode("_", $ref->getInternalUri());

        return $exp[0];
    }

    /**
     * {@inheritdoc}
     */
    public function removeFile(FileReference $ref)
    {
        if (!$uri = $ref->getInternalUri()) {
            return false;
        }

        return unlink($uri);
    }

    /**
     * {@inheritdoc}
     */
    public function removeFileForId($id, $name)
    {
        return unlink($this->generateBasePathForId($id).$name);
    }

    /**
     * {@inheritdoc}
     */
    public function removeFilesForId($id)
    {
        $removed = 0;
        foreach ($this->getFilesForId($id) as $ref) {
            if ($this->removeFile($ref)) {
                $removed++;
            }
        }

        return $removed;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileForId($id, $name)
    {
        $path = $this->generateBasePathForId($id).$name;

        return file_exists($path) ? FileReference::createFromLocalPath($path) : false;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilesForId($id)
    {
        $pattern = $this->generateBasePathForId($id)."*";
        $files = [];

        foreach (glob($pattern) as $path) {
            $files[] = FileReference::createFromLocalPath($path);
        }

        return $files;
    }

    /**
     * {@inheritdoc}
     */
    public function hasFileForId($id, $name)
    {
        return file_exists($this->generateBasePathForId($id).$name);
    }

    /**
     * {@inheritdoc}
     */
    public function addFileForId($id, FileReference $file, $newBasename = null, $copy = false, $onConflict = FilesystemInterface::CONFLICT_OVERWRITE)
    {
        if (!file_exists($file->getInternalUri())) {
            throw new \InvalidArgumentException("File missing, or not available on local file system.");
        }

        //make the containing dir if it doesn't exist
        $base = $this->generateBasePathForId($id);
        $dir = dirname($base);
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0775, true)) {
                throw new \RuntimeException(sprintf("%s could not create a required storage directory [%s].", __CLASS__, $dir));
            }
        }

        //generate the new absolute file name
        $filename = ($newBasename) ? $base.$newBasename : $base.basename($file->getInternalUri());

        //check for conflicts
        if (is_file($filename)) {
            if ($onConflict === FilesystemInterface::CONFLICT_EXCEPTION) {
                throw new \RuntimeException(sprintf("%s cannot overwrite a pre-existing file.", __CLASS__));
            } else {
                unlink($filename);
            }
        }

        //copy or move file to new location
        if ($copy) {
            if (@copy($file->getInternalUri(), $filename)) {
                $file->setInternalUri($filename);
                chmod($filename, 0664);

                return $this->ensurePaths($file);
            } else {
                throw new \RuntimeException(sprintf("Could not copy file [%s] to [%s].", $file->getInternalUri(), $filename));
            }
        } else {
            if (@rename($file->getInternalUri(), $filename)) {
                $file->setInternalUri($filename);
                chmod($filename, 0664);

                return $this->ensurePaths($file);
            } else {
                throw new \RuntimeException(sprintf("Could not move file [%s] to [%s].", $file->getInternalUri(), $filename));
            }
        }

        //consider throwing exception... throw new \RuntimeException(sprintf("File movement error: %s", error_get_last()));
        return false;
    }

    /**
     * If this filesystem has a public uri set, make sure it's set in the file reference accordingly.
     *
     * @param  FileReference $ref
     * @return FileReference
     */
    protected function ensurePaths(FileReference $ref)
    {
        //check for a public uri corresponding to the local root dir
        if ($this->publicRootUri) {
            $localPath = $ref->getInternalUri();

            $filePath = implode("/", array_diff(explode("/", $localPath), explode("/", $this->rootDir)));

            $ref->setDownloadUri($this->publicRootUri.'/'.$filePath);
        }

        return $ref;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount($return = FilesystemInterface::COUNT_FILES)
    {
        $ds = DIRECTORY_SEPARATOR;
        $fCount = 0;
        $dCount = 0;

        //check if we're in php 5.4 or higher, if so, we can avoid sorting because it's irrelevant here
        $sortOrder = (defined('SCANDIR_SORT_NONE')) ? SCANDIR_SORT_NONE : 0;

        //TODO: this may scale terribly with lots of files, it may be faster by implementing readdir instead of scandir
        foreach (scandir($this->rootDir, $sortOrder) as $lvl1) {
            if($lvl1 === '.' || $lvl1 === '..') continue;
            foreach (scandir($this->rootDir.$ds.$lvl1, $sortOrder) as $lvl2) {
                if($lvl2 === '.' || $lvl2 === '..') continue;
                foreach (scandir($this->rootDir.$ds.$lvl1.$ds.$lvl2, $sortOrder) as $lvl3) {
                    if($lvl3 === '.' || $lvl3 === '..') continue;
                    foreach (scandir($this->rootDir.$ds.$lvl1.$ds.$lvl2.$ds.$lvl3, $sortOrder) as $file) {
                        $fCount++;
                    }
                    $fCount-=2;     //subtract 2 to take into account the '.' and '..' links, this will be faster than checking explicitly in large directories
                    $dCount++;
                }
                $dCount++;
            }
            $dCount++;
        }

        switch ($return) {
            case FilesystemInterface::COUNT_FILES : return ($fCount);
            case FilesystemInterface::COUNT_DIRECTORIES : return ($dCount);
            case FilesystemInterface::COUNT_ALL : return ($fCount + $dCount);
            case FilesystemInterface::COUNT_BOTH : return array('files' => $fCount, 'directories' => $dCount);
            default : return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getStats()
    {
        return array(
            'root_dir' => $this->rootDir,
            'root_uri' => $this->publicRootUri,
            'secret' => "Check config files for secret"
        );
    }

    public function createFileSecretForId($id)
    {
        $hash = sha1($id.$this->secret);

        return substr($hash, 3, 15);
    }

}
