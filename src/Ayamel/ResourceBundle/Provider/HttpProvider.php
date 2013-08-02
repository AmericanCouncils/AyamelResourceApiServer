<?php

namespace Ayamel\ResourceBundle\Provider;

use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\ContentCollection;
use Ayamel\ResourceBundle\Document\FileReference;

/**
 * Handler for http/https
 *
 * @author Evan Villemez
 */
class HttpProvider extends AbstractFilePathProvider
{
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'http';
    }

    /**
     * {@inheritdoc}
     */
    public function handlesScheme($scheme)
    {
        return in_array(strtolower($scheme), array('http','https'));
    }

    /**
     * Do some special checks for file types.
     *
     * {@inheritdoc}
     */
    public function createResourceFromUri($uri)
    {
        $ref = FileReference::createFromDownloadUri($uri);

        //curl stuff to make a HEAD request to specified file
        $call = curl_init();
        curl_setopt($call, CURLOPT_HEADER, 0);
        curl_setopt_array($call, array(
            CURLOPT_URL => $uri,
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER => true,             // include response headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING => "",             // handle compressed
            CURLOPT_USERAGENT => "test",        // who am i
            CURLOPT_AUTOREFERER => true,        // set referer on redirect
            CURLOPT_NOBODY => true,             // make a HEAD request
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT => 120,             // timeout on response
            CURLOPT_MAXREDIRS => 5
        ));
        $data = curl_exec($call);
        $code = curl_getinfo($call, CURLINFO_HTTP_CODE);
        $bytes = curl_getinfo($call, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        $mime = curl_getinfo($call, CURLINFO_CONTENT_TYPE);
        curl_close($call);

        //check for failure
        if (!$data || (200 !== $code)) {
            return false;
        }

        //set file reference data
        $mime = (null === $mime) ? 'application/octet-stream' : $mime;
        $bytes = ($bytes >= 0) ? $bytes : 0;
        $ref->setMime($mime);
        $exp = explode(';', $mime);
        $ref->setMimeType($exp[0]);
        $ref->setBytes($bytes);
        $ref->setRepresentation('original');
        $ref->setQuality(0);

        $r = new Resource();
        $r->setType($this->guessTypeFromExtension($this->getPathExtension($uri)));
        $r->setTitle($this->getFilenameFromPath($uri));
        $r->setContent(new ContentCollection);
        $r->content->addFile($ref);

        return $r;
    }

}
