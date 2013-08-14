<?php

namespace Ayamel\TranscodingBundle\Tests;

use Ayamel\TranscodingBundle\PresetMapper;
use Ayamel\ResourceBundle\Document\FileReference;

class PresetMapperTest extends \PHPUnit_Framework_TestCase
{

    protected function getPresets()
    {
        return array(
            'video_to_mp4_low' => array('preset_service' => 'handbrake.classic', 'tag' => 'medium', 'extension' => 'mp4', 'representation' => 'transcoding', 'quality' => 2),
            'video_to_mp4_sd' => array('preset_service' => 'handbrake.ipod', 'tag' => 'medium', 'extension' => 'mp4', 'representation' => 'transcoding', 'quality' => 1),
            'video_to_thumbnail' => array('preset_service' => 'imagine.resize', 'tag' => 'medium', 'extension' => 'mp4', 'representation' => 'transcoding', 'quality' => 3)
        );
    }

    protected function getMap()
    {
        return array(
            'video/mp4' => array('video_to_mp4_low', 'video_to_mp4_sd', 'video_to_thumbnail'),
            'video/x-ms-wmv' => array('video_to_mp4_low', 'video_to_mp4_sd', 'video_to_thumbnail'),
            'video/quicktime' => array('video_to_mp4_low', 'video_to_mp4_sd', 'video_to_thumbnail'),
            'video/x-msvideo' => array('video_to_mp4_low', 'video_to_mp4_sd', 'video_to_thumbnail'),
            'video/x-flv' => array('video_to_mp4_low', 'video_to_mp4_sd', 'video_to_thumbnail')
        );
    }

    protected function createTestReference($mime)
    {
        $ref = new FileReference;
        $ref->setMimeType($mime);
        $ref->setMime($mime);

        return $ref;
    }

    protected function createTestMapper()
    {
        return new PresetMapper($this->getPresets(), $this->getMap());
    }

    public function testInstantiate()
    {
        $m = new PresetMapper;
        $this->assertNotNull($m);
        $this->assertTrue($m instanceof PresetMapper);
    }

    public function testGetPreset()
    {
        $data = $this->createTestMapper()->getPreset('video_to_mp4_low');
        $this->assertSame('handbrake.classic', $data['preset_service']);
        $this->assertFalse($this->createTestMapper()->getPreset('does_not_exist'));
    }

    public function testCanTranscodeFileReference()
    {
        $this->assertTrue($this->createTestMapper()->canTranscodeFileReference($this->createTestReference('video/x-ms-wmv')));
        $this->assertFalse($this->createTestMapper()->canTranscodeFileReference($this->createTestReference('application/pdf')));
    }

    public function testGetPresetsForMimeType()
    {
        $expected = array('video_to_mp4_low', 'video_to_mp4_sd', 'video_to_thumbnail');
        $this->assertSame($expected, $this->createTestMapper()->getPresetsForMimeType('video/mp4'));
        $this->assertFalse($this->createTestMapper()->getPresetsForMimeType('application/pdf'));
    }

    public function testGetMimeTypesForPreset()
    {
        $expected = array(
            'video/mp4',
            'video/x-ms-wmv',
            'video/quicktime',
            'video/x-msvideo',
            'video/x-flv',
        );

        $this->assertSame($expected, $this->createTestMapper()->getMimeTypesForPreset('video_to_mp4_low'));
    }

    public function getPresetMappingsForFileReference()
    {
        $this->assertSame($this->getPresets(), $this->createTestMapper()->getPresetMappingsForFileReference(
            $this->createTestReference('video/quicktime')
        ));

        $this->assertFalse($this->createTestMapper()->getPresetMappingsForFileReference(
            $this->createTestReference('application/pdf')
        ));
    }

}
