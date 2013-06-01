<?php

namespace Ayamel\ResourceBundle\Tests;

use Ayamel\ApiBundle\ApiTestCase;
use Symfony\Component\DependencyInjection\Container;
use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\ContentCollection;
use Ayamel\ResourceBundle\Document\FileReference;

/**
 * Test that file attribute validators are being applied properly
 */
class FileReferenceValidationTest extends ApiTestCase
{
    public function testThrowExceptionValidatingUnknownMime()
    {
        $v = $this->getContainer()->get('validator');
        
        $ref = new FileReference();
        $ref->setDownloadUri("http://example.org/foo.mp3");
        $ref->setBytes(100);
        $ref->setRepresentation('original');
        $ref->setQuality(0);
        $ref->setMimeType('fake/mime-type');
        $ref->setAttributes(array(
            'foo' => 3,
            'bar' => 4
        ));
        
        
        $errors = $v->validate($ref);
        $this->assertSame(1, count($errors));
        $this->assertSame('mimeType', $errors[0]->getPropertyPath());
    }
    
    public function testValidateVideoAttributes()
    {
        $v = $this->getContainer()->get('validator');

        $ref = new FileReference();
        $ref->setDownloadUri("http://example.org/foo.mp3");
        $ref->setBytes(100);
        $ref->setRepresentation('original');
        $ref->setQuality(0);
        $ref->setMimeType('video/mp4');
        $ref->setAttributes(array(
            'duration' => 1000,
            'resolutionX' => 600,
            'resolutionY' => 400,
            'averageBitrate' => 44000
        ));
        
        $errors = $v->validate($ref);
        $this->assertSame(0, count($errors));
        
        $ref->setAttributes(array(
            'duration' => 3.14159,
            'resolutionX' => 600,
            'resolutionY' => 400,
            'averageBitrate' => 44000
        ));
            
        $errors = $v->validate($ref);
        $this->assertSame(1, count($errors));
    }
    
    public function testValidateAudioAttributes()
    {
        $v = $this->getContainer()->get('validator');

        $ref = new FileReference();
        $ref->setDownloadUri("http://example.org/foo.mp3");
        $ref->setBytes(100);
        $ref->setRepresentation('original');
        $ref->setQuality(0);
        $ref->setMimeType('audio/mp3');
        $ref->setAttributes(array(
            'duration' => 1000,
            'averageBitrate' => 44000
        ));
        
        $errors = $v->validate($ref);
        $this->assertSame(0, count($errors));
        
        $ref->setAttributes(array(
            'duration' => 3.14159,
            'averageBitrate' => 44000
        ));
            
        $errors = $v->validate($ref);
        $this->assertSame(1, count($errors));
    }

}
