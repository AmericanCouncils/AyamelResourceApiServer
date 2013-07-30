<?php

namespace Ayamel\MediaInfoBundle\Tests;

use Ayamel\ApiBundle\ApiTestCase;
use Ayamel\MediaInfoBundle\MediaInfoAnalyzer;
use Ayamel\FilesystemBundle\Analyzer\DelegatingAnalyzer;
use Ayamel\ResourceBundle\Document\FileReference;

class MediaInfoAnalyzerTest extends ApiTestCase
{

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

    public function testAnalyzeFileReference()
    {
        $c = $this->getContainer();
        
        //make sure mediainfo is actually accessible on the system
        $mediainfoPath = $c->getParameter('ac_media_info.path');
        if (!file_exists($mediainfoPath)) {
            $this->markTestSkipped("mediainfo cli utility is not accessible on this system.");
        }
        
        $analyzer = $c->get('ayamel.filesystem.mediainfo_analyzer');
        
        $ref = FileReference::createFromLocalPath(__DIR__.'/sample.jpg');
        
        $attrs = $ref->getAttributes();
        $this->assertTrue(empty($attrs));

        $analyzer->analyzeFile($ref);
        $attrs = $ref->getAttributes();

        $this->assertFalse(empty($attrs));
        $this->assertTrue(isset($attrs['frameSize']['width']));
        $this->assertTrue(isset($attrs['frameSize']['height']));
        $this->assertSame(50, $attrs['frameSize']['width']);
        $this->assertSame(50, $attrs['frameSize']['height']);
    }

}
