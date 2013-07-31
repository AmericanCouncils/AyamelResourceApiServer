<?php

namespace Ayamel\ResourceBundle\Tests;

use Ayamel\ApiBundle\ApiTestCase;
use Ayamel\ResourceBundle\Document\FileReference;

/**
 * Test that file attribute validators are being applied properly
 */
class FileReferenceValidationTest extends ApiTestCase
{
    public function testIgnoresFilesWithNoAttributes()
    {
        $v = $this->getContainer()->get('validator');
        $ref = new FileReference();
        $ref->setDownloadUri("http://example.org/foo.mp3");
        $ref->setBytes(100);
        $ref->setRepresentation('original');
        $ref->setQuality(0);
        $ref->setMimeType('fake/mime-type');

        $errors = $v->validate($ref);
        $this->assertSame(0, count($errors));
    }

    public function testFailsOnExtraAttributeKeys()
    {
        $v = $this->getContainer()->get('validator');
        $ref = new FileReference();
        $ref->setDownloadUri("http://example.org/foo.mp4");
        $ref->setBytes(100);
        $ref->setRepresentation('original');
        $ref->setQuality(0);
        $ref->setMimeType('video/mp4');
        $ref->setAttributes(array(
            'duration' => 23,
            'foo' => 'bar'
        ));

        $errors = $v->validate($ref);
        $this->assertSame(1, count($errors));
    }

    public function testFailValidatingUnknownMimeWithAttributes()
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

    public function testValidateGenericVideoAttributes()
    {
        $v = $this->getContainer()->get('validator');

        //valid attributes
        $ref = new FileReference();
        $ref->setDownloadUri("http://example.org/foo.mp4");
        $ref->setBytes(100);
        $ref->setRepresentation('original');
        $ref->setQuality(0);
        $ref->setMimeType('video/mp4');
        $ref->setAttributes(array(
            'duration' => 1000,
            'frameSize' => array(
                'width' => 600,
                'height' => 400
            ),
            'bitrate' => 44000,
            'frameRate' => 60,
            'aspectRatio' => '16:9'
        ));
        $errors = $v->validate($ref);
        $this->assertSame(0, count($errors));

        //bad frame size
        $ref->setAttribute('frameSize', array(
            'height' => 3444,
        ));
        $errors = $v->validate($ref);
        $this->assertSame(1, count($errors));

        //bad duration
        $ref->setAttributes(array(
            'duration' => 3.14159,
            'frameSize' => array(
                'width' => 600,
                'height' => 400
            ),
            'bitrate' => 44000
        ));
        $errors = $v->validate($ref);
        $this->assertSame(1, count($errors));

        //bad aspect ratio
        $ref->setAttributes(array(
            'aspectRatio' => '23f.3:3',
            'bitrate' => 44000
        ));
        $errors = $v->validate($ref);
        $this->assertSame(1, count($errors));

    }

    public function testValidateGenericAudioAttributes()
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
            'bitrate' => 44000,
            'channels' => 4
        ));

        $errors = $v->validate($ref);
        $this->assertSame(0, count($errors));

        $ref->setAttributes(array(
            'duration' => 3.14159,
            'bitrate' => 44000
        ));

        $errors = $v->validate($ref);
        $this->assertSame(1, count($errors));
    }

    public function testValidateGenericImageAttributes()
    {
        $v = $this->getContainer()->get('validator');

        $ref = new FileReference();
        $ref->setDownloadUri("http://example.org/foo.png");
        $ref->setBytes(100);
        $ref->setRepresentation('original');
        $ref->setQuality(0);
        $ref->setMimeType('image/png');
        $ref->setAttributes(array(
            'units' => "px",
            'frameSize' => array(
                'width' => 600,
                'height' => 400
            ),
            'aspectRatio' => '16:9',
            'time' => 4,
        ));

        $errors = $v->validate($ref);
        $this->assertSame(0, count($errors));

        $ref = new FileReference();
        $ref->setDownloadUri("http://example.org/foo.png");
        $ref->setBytes(100);
        $ref->setRepresentation('original');
        $ref->setQuality(0);
        $ref->setMimeType('image/png');
        $ref->setAttributes(array(
            'frameSize' => array(
                'width' => 600,
                'height' => 400
            ),
            'aspectRatio' => '16:9',
            'time' => 4,
        ));

        $errors = $v->validate($ref);
        $this->assertSame(0, count($errors));
        $this->assertSame('px', $ref->getAttribute('units'));
    }

    public function testValidateGenericArchiveAttributes()
    {
        $v = $this->getContainer()->get('validator');

        $ref = new FileReference();
        $ref->setDownloadUri("http://example.org/foo.zip");
        $ref->setBytes(100);
        $ref->setRepresentation('original');
        $ref->setQuality(0);
        $ref->setMimeType('application/zip');
        $ref->setAttributes(array());

        $errors = $v->validate($ref);
        $this->assertSame(0, count($errors));
    }
    public function testValidateGenericDataAttributes()
    {
        $v = $this->getContainer()->get('validator');

        $ref = new FileReference();
        $ref->setDownloadUri("http://example.org/foo.xml");
        $ref->setBytes(100);
        $ref->setRepresentation('original');
        $ref->setQuality(0);
        $ref->setMimeType('application/xml');
        $ref->setAttributes(array());

        $errors = $v->validate($ref);
        $this->assertSame(0, count($errors));

    }
    public function testValidateGenericDocumentAttributes()
    {
        $v = $this->getContainer()->get('validator');

        $ref = new FileReference();
        $ref->setDownloadUri("http://example.org/foo.pdf");
        $ref->setBytes(100);
        $ref->setRepresentation('original');
        $ref->setQuality(0);
        $ref->setMimeType('application/pdf');
        $ref->setAttributes(array(
            'pages' => 4
        ));

        $errors = $v->validate($ref);
        $this->assertSame(0, count($errors));

    }
    public function testValidateCaptionAttributes()
    {
        $v = $this->getContainer()->get('validator');

        $ref = new FileReference();
        $ref->setDownloadUri("http://example.org/foo.vtt");
        $ref->setBytes(100);
        $ref->setRepresentation('original');
        $ref->setQuality(0);
        $ref->setMimeType('text/vtt');
        $ref->setAttributes(array(
            'duration' => 4
        ));

        $errors = $v->validate($ref);
        $this->assertSame(0, count($errors));
    }
}
