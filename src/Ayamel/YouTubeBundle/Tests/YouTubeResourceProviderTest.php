<?php

namespace Ayamel\YouTubeBundle\Tests;

use Ayamel\ApiBundle\ApiTestCase;

class YouTubeResourceProviderTest extends ApiTestCase
{
    public function testHandleScheme()
    {
        $this->assertTrue($this->getContainer()->get('ayamel.resource.provider')->handlesScheme('youtube'));
    }
    
    public function testDeriveYouTubeResource()
    {
        $r = $this->getContainer()->get('ayamel.resource.provider')->createResourceFromUri('youtube://txqiwrbYGrs');
        $this->assertSame('David After Dentist', $r->getTitle());
        $this->assertSame('video', $r->getType());
        $this->assertSame('youtube', $r->getLicense());
        $this->assertFalse(is_null($r->getDescription()));
        $this->assertFalse(is_null($r->getSubjectDomains()));
        
        //origin
        $this->assertSame('booba1234', $r->origin->getCreator());
        $this->assertFalse(is_null($r->origin->getDate()));
        $this->assertSame("YouTube Video", $r->origin->getFormat());
        
        //oembed
        $this->assertFalse(is_null($r->content->getOembed()));
        $this->assertSame('David After Dentist', $r->content->getOembed()->title);
    }
}
