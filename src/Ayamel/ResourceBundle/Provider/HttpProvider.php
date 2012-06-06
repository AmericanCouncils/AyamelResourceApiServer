<?php

namespace Ayamel\ResourceBundle\Provider;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handler for http/https
 *
 * @author Evan Villemez
 */
class HttpProvider extends AbstractFilePathProvider {
    
    /**
     * {@inheritdoc}
     */
    function getKey() {
        return 'http';
    }
    
    /**
     * {@inheritdoc}
     */
    function handlesScheme($scheme) {
        return in_array(strtolower($scheme), array('http','https'));
    }
    
    /**
     * Do some special checks for file types.
     *
     * {@inheritdoc}
     */
    public function createResourceFromUri($uri) {
        //TODO: use HEAD request to get mime/size

        try {
            $r = parent::createResourceFromUri($uri);
        } catch (\InvalidArgumentException $e) {
            throw new HttpException(424, $e->getMessage());
        }
        
        //if file type is original, mark it as such
        foreach($r->content->getFiles() as $file) {
            if($file->getOriginal()) {
                //file references reporting as 'binary' are most likely webpages without an extension
                $file->setRepresentation("original;0");
            }
        }
        
        return $r;
    }

}