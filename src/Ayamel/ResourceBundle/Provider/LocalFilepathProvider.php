<?php

namespace Ayamel\ResourceBundle\Provider;

use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\ContentCollection;
use Ayamel\ResourceBundle\Document\FileReference;

 /**
 * Provider for local files
 *
 * @author Evan Villemez
 */
class LocalFilepathProvider extends AbstractFilePathProvider
{
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'local';
    }

    /**
     * {@inheritdoc}
     */
    public function handlesScheme($scheme)
    {
        if (0 === strpos($scheme, "/")) {
            return true;
        }

        return ("file" === strtolower($scheme));
    }

    public function createResourceFromUri($uri)
    {
        //take into account file:///path/to/file vs /path/to/file
        $exp = explode("://", $uri);
        if (1 === count($exp)) {
            $scheme = 'file';
            $path = $uri;
        } else {
            $scheme = $exp[0];
            $path = $exp[1];
        }

        if (!file_exists($path)) {
            return false;
        }

        //create original file reference
        $file = FileReference::createFromLocalPath($uri);
        $file->setRepresentation('original');
        $file->setQuality(0);

        //finfo guess mime
        $finfo = new \finfo(FILEINFO_MIME);
        $mime = $finfo->file($path);
        $file->setMime($mime);
        $exp = explode(';', $mime);
        $file->setMimeType($exp[0]);

        //size
        $file->setBytes(filesize($path));

        //build new resource
        $r = new Resource();
        $r->setType($this->guessTypeFromExtension($this->getPathExtension($path)));
        $r->setTitle($this->getFilenameFromPath($path));
        $r->setContent(new ContentCollection);
        $r->content->addFile($file);

        return $r;
    }

}
