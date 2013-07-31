<?php

namespace Ayamel\MediaInfoBundle\Tests;

use Ayamel\ApiBundle\ApiTestCase;
use Ayamel\MediaInfoBundle\MediaInfoAnalyzer;
use Ayamel\FilesystemBundle\Analyzer\DelegatingAnalyzer;
use Ayamel\ResourceBundle\Document\FileReference;

class MediaInfoAnalyzerTest extends ApiTestCase
{

    protected function ensureTestable()
    {
        $mediainfoPath = $this->getContainer()->getParameter('ac_media_info.path');
        if (!file_exists($mediainfoPath)) {
            $this->markTestSkipped("mediainfo cli utility is not accessible on this system.");
        }
    }

    public function testGetMediaInfoAnalyzer()
    {
        $analyzer = $this->getContainer()->get('ayamel.filesystem.mediainfo_analyzer');
        $this->assertTrue($analyzer instanceof MediaInfoAnalyzer);
    }

    public function testRegisterMediaInfoAnalyzer()
    {
        $delegator = $this->getContainer()->get('ayamel.filesystem.analyzer');
        if (!$delegator instanceof DelegatingAnalyzer) {
            return;
        }

        $found = false;
        $analyzers = $delegator->getAnalyzers();
        $this->assertTrue(0 < count($analyzers));
        foreach ($delegator->getAnalyzers() as $analyzer) {
            if ($analyzer instanceof MediaInfoAnalyzer) {
                $found = true;
            }
        }

        $this->assertTrue($found);
    }

    public function testAnalyzeImageReference()
    {
        $c = $this->getContainer();

        //make sure mediainfo is actually accessible on the system
        $this->ensureTestable();

        $analyzer = $c->get('ayamel.filesystem.mediainfo_analyzer');

        $ref = FileReference::createFromLocalPath(__DIR__.'/sample.jpg');

        $attrs = $ref->getAttributes();
        $this->assertTrue(empty($attrs));

        $analyzer->analyzeFile($ref);
        $attrs = $ref->getAttributes();

        $this->assertSame('image/jpeg', $ref->getMimeType());
        $this->assertFalse(empty($attrs));
        $this->assertSame(50, $attrs['frameSize']['width']);
        $this->assertSame(50, $attrs['frameSize']['height']);
    }

    public function testAnalyzeAudioReference()
    {
        $c = $this->getContainer();

        //make sure mediainfo is actually accessible on the system
        $this->ensureTestable();

        $analyzer = $c->get('ayamel.filesystem.mediainfo_analyzer');

        $ref = FileReference::createFromLocalPath(__DIR__.'/subclip.mp3');

        $attrs = $ref->getAttributes();
        $this->assertTrue(empty($attrs));

        $analyzer->analyzeFile($ref);
        $attrs = $ref->getAttributes();

        $this->assertSame('audio/mpeg', $ref->getMimeType());
        $this->assertFalse(empty($attrs));
        $this->assertSame(1, $attrs['duration']);
        $this->assertSame(64000, $attrs['bitrate']);
        $this->assertSame(2, $attrs['channels']);
    }

    public function testAnalyzeVideoReference()
    {
        $c = $this->getContainer();

        //make sure mediainfo is actually accessible on the system
        $this->ensureTestable();

        $analyzer = $c->get('ayamel.filesystem.mediainfo_analyzer');

        $ref = FileReference::createFromLocalPath(__DIR__.'/subclip.mov');

        $attrs = $ref->getAttributes();
        $this->assertTrue(empty($attrs));

        $analyzer->analyzeFile($ref);
        $attrs = $ref->getAttributes();

        $this->assertSame('video/mp4', $ref->getMimeType());
        $this->assertFalse(empty($attrs));
        $this->assertSame(1, $attrs['duration']);
        $this->assertTrue(isset($attrs['bitrate']));
        $this->assertTrue(is_int($attrs['bitrate']));
        $this->assertTrue(isset($attrs['frameSize']));
        $this->assertSame(854, $attrs['frameSize']['width']);
        $this->assertSame(480, $attrs['frameSize']['height']);
        $this->assertSame(25, $attrs['frameRate']);
        $this->assertSame('16:9', $attrs['aspectRatio']);
    }

}
