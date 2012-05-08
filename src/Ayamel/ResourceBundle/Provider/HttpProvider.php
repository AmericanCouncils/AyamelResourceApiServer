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
        try {
            $r = parent::createResourceFromUri($uri);
        } catch (\InvalidArgumentException $e) {
            throw new HttpException(424, $e->getMessage());
        }
        
        //if file type is "binary", this is likely a web page instead
        foreach($r->content->getFiles() as $file) {
            if($file->getOriginal() && $file->getType() === 'binary') {
                //file references reporting as 'binary' are most likely webpages without an extension
                $file->setType("webpage");
                $r->setType("webpage");
            }
        }
        
        return $r;
    }

}