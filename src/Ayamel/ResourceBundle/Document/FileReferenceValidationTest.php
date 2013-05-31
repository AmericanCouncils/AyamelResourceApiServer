<?php

namespace Ayamel\ApiBundle\Tests;

use Ayamel\ApiBundle\ApiTestCase;
use Symfony\Component\DependencyInjection\Container;
use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\ContentCollection;
use Ayamel\ResourceBundle\Document\FileReference;

/**
 * These tests ensure that the custom validators for file reference attributes
 * are working correctly.
 */
class FileReferenceValidationTest extends ApiTestCase
{
    public function testThrowExceptionValidatingUnknownMime()
    {
        $v = $this->getContainer()->get('validator');
        
        $ref = new FileReference();
        $ref->setMimeType('fake/mime-type');
        $ref->setAttributes(array(
            'foo' => 3,
            'bar' => 4
        ));
        $resource->content->addFile($ref);
        
        //TODO: set expected exception
        $v->validate($resource);
    }
    
    
}
