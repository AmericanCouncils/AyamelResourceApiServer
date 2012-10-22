<?php

namespace Ayamel\TranscodingBundle\Tests;

use Ayamel\TranscodingBundle\PresetMapper;
use Ayamel\ResourceBundle\Document\FileReference;

class PresetMapperTest extends \PHPUnit_Framework_TestCase
{

    protected function getMapperConfig()
    {
        return array(
			'video/mp4' => array(
			    array('preset' => 'handbrake.classic', 'tag' => 'medium', 'extension' => 'mp4', 'representation' => 'transcoding', 'quality' => 2),
			    array('preset' => 'handbrake.ipod', 'tag' => 'medium', 'extension' => 'mp4', 'representation' => 'transcoding', 'quality' => 1),
			    array('preset' => 'imagine.resize', 'tag' => 'medium', 'extension' => 'mp4', 'representation' => 'transcoding', 'quality' => 3),
			),
			'video/x-ms-wmv' => array(
			    array('preset' => 'handbrake.classic', 'tag' => 'medium', 'extension' => 'mp4', 'representation' => 'transcoding', 'quality' => 2),
			    array('preset' => 'handbrake.ipod', 'tag' => 'medium', 'extension' => 'mp4', 'representation' => 'transcoding', 'quality' => 1),
			    array('preset' => 'imagine.resize', 'tag' => 'medium', 'extension' => 'mp4', 'representation' => 'transcoding', 'quality' => 3),
			),
            'video/quicktime' => array(
			    array('preset' => 'handbrake.classic', 'tag' => 'medium', 'extension' => 'mp4', 'representation' => 'transcoding', 'quality' => 2),
			    array('preset' => 'handbrake.ipod', 'tag' => 'medium', 'extension' => 'mp4', 'representation' => 'transcoding', 'quality' => 1),
			    array('preset' => 'imagine.resize', 'tag' => 'medium', 'extension' => 'mp4', 'representation' => 'transcoding', 'quality' => 3),
            ),
            'video/x-msvideo' => array(
			    array('preset' => 'handbrake.classic', 'tag' => 'medium', 'extension' => 'mp4', 'representation' => 'transcoding', 'quality' => 2),
			    array('preset' => 'handbrake.ipod', 'tag' => 'medium', 'extension' => 'mp4', 'representation' => 'transcoding', 'quality' => 1),
			    array('preset' => 'imagine.resize', 'tag' => 'medium', 'extension' => 'mp4', 'representation' => 'transcoding', 'quality' => 3),
            ),
            'video/x-flv' => array(
			    array('preset' => 'handbrake.classic', 'tag' => 'medium', 'extension' => 'mp4', 'representation' => 'transcoding', 'quality' => 2),
			    array('preset' => 'handbrake.ipod', 'tag' => 'medium', 'extension' => 'mp4', 'representation' => 'transcoding', 'quality' => 1),
			    array('preset' => 'imagine.resize', 'tag' => 'medium', 'extension' => 'mp4', 'representation' => 'transcoding', 'quality' => 3),
            ),
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
        return new PresetMapper($this->getMapperConfig());
    }

	public function testInstantiate()
	{
		$m = new PresetMapper;
		$this->assertNotNull($m);
		$this->assertTrue($m instanceof PresetMapper);
	}
    
    public function testCanTranscodeFileReference()
    {
        $this->assertTrue($this->createTestMapper()->canTranscodeFileReference($this->createTestReference('video/x-ms-wmv')));
        $this->assertFalse($this->createTestMapper()->canTranscodeFileReference($this->createTestReference('application/pdf')));
    }
    
    public function testGetPresetsForMimeType()
    {
        $expected = array('handbrake.classic','handbrake.ipod','imagine.resize');
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
                
        $this->assertSame($expected, $this->createTestMapper()->getMimeTypesForPreset('imagine.resize'));
    }
    
    public function getPresetMappingsForFileReference()
    {
        $this->assertSame($this->getMapperConfig(), $this->createTestMapper()->getPresetMappingsForFileReference(
            $this->createTestReference('video/quicktime')
        ));
        
        $this->assertFalse($this->createTestMapper()->getPresetMappingsForFileReference(
            $this->createTestReference('application/pdf')
        ));
    }

}