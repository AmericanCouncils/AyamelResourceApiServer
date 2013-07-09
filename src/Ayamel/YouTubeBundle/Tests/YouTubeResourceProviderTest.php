<?php

namespace Ayamel\YouTubeBundle\Tests;

use Ayamel\YouTubeBundle\YouTubeResourceProvider;
use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\Origin;
use Ayamel\ResourceBundle\Document\OEmbed;

class YouTubeResourceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testHandleScheme()
    {
        $provider = new YouTubeResourceProvider();
        $this->assertTrue($provider->handlesScheme('youtube'));
    }

    public function testDeriveYouTubeResource()
    {
        $provider = new YouTubeResourceProvider();
        $r = $provider->createResourceFromUri('youtube://txqiwrbYGrs');
        $this->assertTrue($r instanceof Resource);
        $this->assertSame('David After Dentist', $r->getTitle());
        $this->assertSame('video', $r->getType());
        $this->assertSame('youtube', $r->getLicense());
        $this->assertFalse(is_null($r->getDescription()));
        $this->assertFalse(is_null($r->getSubjectDomains()));

        //origin
        $this->assertTrue($r->origin instanceof Origin);
        $this->assertSame('booba1234', $r->origin->getCreator());
        $this->assertFalse(is_null($r->origin->getDate()));
        $this->assertSame("YouTube Video", $r->origin->getFormat());

        //oembed
        $this->assertTrue($r->content->getOembed() instanceof OEmbed);
        $this->assertFalse(is_null($r->content->getOembed()));
        $this->assertSame('David After Dentist', $r->content->getOembed()->title);
    }
}
