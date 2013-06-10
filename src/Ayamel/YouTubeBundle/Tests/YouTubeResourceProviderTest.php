<?php

namespace Ayamel\YouTubeBundle\Tests;

use Ayamel\ApiBundle\ApiTestCase;

class YouTubeResourceProviderTest extends ApiTestCase
{
    public function testHandleScheme()
    {
        $this->assertTrue($this->getContainer()->handlesScheme('youtube'));
    }
    
    public function testDeriveYouTubeResource()
    {
        $r = $this->getContainer()->deriveResourceFromUri('youtube://ddddddddddd');
        
    }
}
