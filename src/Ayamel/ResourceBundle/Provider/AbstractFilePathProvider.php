<?php

namespace Ayamel\ResourceBundle\Provider;

use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\ContentCollection;
use Ayamel\ResourceBundle\Document\FileReference;

/**
 * Provider for deriving resource objects from generic file paths of any scheme.
 *
 * @author Evan Villemez
 */
abstract class AbstractFilePathProvider implements ProviderInterface
{
    /**
     * Default resurce and file types to use for specific file extensions. Can be overriden from constructor.
     *
     * @var array
     */
    protected $resourceTypes = array();

    /**
     * Type to use for paths without a file extension.
     *
     * @var string
     */
    protected $nullExtensionType;

    /**
     * Constructor allows overriding the default file extension to resource type definitions.
     *
     * @param array $resourceTypes
     */
    public function __construct(array $resourceTypes = null, $nullType = 'binary')
    {
        if (!is_null($resourceTypes)) {
            $this->resourceTypes = $resourceTypes;
        }

        $this->nullExtensionType = $nullType;
    }

    /**
    * {@inheritdoc}
     */
    public function getKey()
    {
        throw new \RuntimeException(sprintf("%s not implemented.", __METHOD__));
    }

    /**
    * {@inheritdoc}
     */
    public function handlesScheme($scheme)
    {
        throw new \RuntimeException(sprintf("%s not implemented.", __METHOD__));
    }

    /**
    * {@inheritdoc}
     */
    public function createResourceFromUri($uri)
    {
        return $this->createNewResourceFromUri($uri);
    }

    /**
     * Create the basic resource with one file entry for content.
     *
     * @author Evan Villemez
     */
    protected function createNewResourceFromUri($uri)
    {
        $exp = explode("://", $uri);
        if (1 === count($exp)) {
            $scheme = 'file';
            $path = $uri;
        } else {
            $scheme = $exp[0];
            $path = $exp[1];
        }

        //does the file actually exist?
        //TODO: change this to use a curl head request and get other file info as well
        //http://stackoverflow.com/questions/2610713/get-mime-type-of-external-file-using-curl-and-php#4
        if (!$handle = @fopen($uri, "r")) {
            throw new \InvalidArgumentException(sprintf("Resource at [%s] could not be found.", $uri));
        }
        fclose($handle);

        //create original file reference
        $file = ($scheme === 'file') ? FileReference::createFromLocalPath($uri) : FileReference::createFromDownloadUri($uri);
        $file->setOriginal(true);

        //build new resource
        $r = new Resource;
        $type = $this->guessTypeFromExtension($this->getPathExtension($path));
        $r->setType($type);
        $r->setTitle($this->getFilenameFromPath($path));
        $r->setContent(new ContentCollection);
        $r->content->addFile($file);

        return $r;
    }

    /**
     * Get the filename from the path to be used as the name for the resource object.
     *
     * @param  string $path
     * @return string
     */
    protected function getFilenameFromPath($path)
    {
        return basename($path);
    }

    /**
     * Get the extension from the file path, or null if none exists
     *
     * @param  string  $path
     * @return string, or null
     */
    protected function getPathExtension($path)
    {
        $exp = explode(".", $path);
        if (1 === count($exp)) {
            return null;
        }

        return strtolower(end($exp));
    }

    /**
     * Use provided file extension mappings to guess the generic type of a file from it's extension.
     *
     * @param  string $extension
     * @return string
     */
    protected function guessTypeFromExtension($extension)
    {
        return (isset($this->resourceTypes[$extension])) ? $this->resourceTypes[$extension] : $this->nullExtensionType;
    }
}
