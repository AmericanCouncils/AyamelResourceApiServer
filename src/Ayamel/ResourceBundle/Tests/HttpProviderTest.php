<?php

namespace Ayamel\ResourceBundle\Tests;

use Ayamel\ResourceBundle\Provider\HttpProvider;
use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\FileReference;

class HttpProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testHttpProvider()
    {
        $provider = new HttpProvider(array(
            'document' => array('text/plain', 'text/html')
        ), 'data');

        //existing uri
        $r = $provider->createResourceFromUri('http://www.google.com/');
        $this->assertTrue($r instanceof Resource);
        $files = $r->content->getFiles();
        $this->assertSame(1, count($files));
        $this->assertTrue($files[0] instanceof FileReference);

        //nonexisting uri
        $r = $provider->createResourceFromUri("http://www.example.com/does/not/exist.zip");
        $this->assertFalse($r);
    }
}
