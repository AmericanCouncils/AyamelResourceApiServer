<?php

namespace Ayamel\ResourceBundle\Provider;

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
    protected $resourceTypes = [];

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
    public function __construct(array $resourceTypes = null, $nullType = 'data')
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
        throw new \RuntimeException(sprintf("%s not implemented.", __METHOD__));
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
        foreach ($this->resourceTypes as $type => $extensions) {
            if (in_array($extension, $extensions)) {
                return $type;
            }
        }

        return $this->nullExtensionType;
    }
}
