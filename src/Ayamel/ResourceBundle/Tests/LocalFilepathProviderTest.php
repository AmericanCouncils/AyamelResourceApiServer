<?php

namespace Ayamel\ResourceBundle\Tests;

use Ayamel\ResourceBundle\Provider\LocalFilepathProvider;
use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\FileReference;

class LocalFilepathProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testLocalFilepathProvider()
    {
        $provider = new LocalFilepathProvider(array(
            'document' => array('text/plain', 'text/html')
        ), 'data');

        //existing file
        $r = $provider->createResourceFromUri(__DIR__."/example.txt");
        $this->assertTrue($r instanceof Resource);
        $files = $r->content->getFiles();
        $this->assertSame(1, count($files));
        $this->assertTrue($files[0] instanceof FileReference);

        //nonexisting file
        $r = $provider->createResourceFromUri(__DIR__."/foo.txt");
        $this->assertFalse($r);
    }
}
