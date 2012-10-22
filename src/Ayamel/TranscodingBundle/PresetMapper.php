<?php

namespace Ayamel\TranscodingBundle;

use Ayamel\ResourceBundle\Document\FileReference;

/**
 * The PresetMapper maps file mime types to transcoding presets, along
 * with other metadata that is needed by the Resource Library.
 *
 * The PresetMapper can answer questions about specific resources to
 * determine which presets are applicable to which files.
 *
 * The PresetMapper is created per-Resource and used internally in the 
 * TranscodeManager for this purpose.
 *
 * The format of a preset mapping can be seen in the YAML example below:
 * 
 *      video/mp4:
 *          - { preset: handbrake.ipod, tag: low, extension: mp4, representation: transcoding, quality: 1 }
 *          - { preset: handbrake.classic, tag: medium, extension: mp4, representation: transcoding, quality: 2 }
 *          - { preset: handbrake.high_profile, tag: high, extension: mp4, representation: transcoding, quality: 3 }
 *  
 * An explanation of the preset array keys:
 * 
 * - `preset`: TODO...
 * - `tag`: 
 * - `extension`: 
 * - `representation`: 
 * - `quality`: 
 *
 * @package AyamelTranscodingBundle
 * @author Evan Villemez
 */
class PresetMapper
{
	
	private $mimes = array();
	
	public function __construct($presetMap = array())
	{
		$this->addPresetDefinitions($presetMap);
	}
	
	protected function processMimeMap(array $presetMap)
	{
        $mimes = array();
        foreach ($presetMap as $presetName => $data) {
            if (isset($data['mimes'])) {
                foreach ($data['mimes'] as $mime) {
                    if (!isset($mimes[$mime])) {
                        $mimes[$mime] = array();
                    }
                    
                    $mimes[$mime][] = $presetName;
                }
            }
        }
        
        return $mimes;
	}
	
    /**
     * Return true/false if the given FileReference can be
     * transcoded based on the available preset mappings.
     *
     * @param FileReference $ref 
     * @return boolean
     */
	public function canTranscodeFileReference(FileReference $ref)
	{
		return isset($this->mimes[$ref->getMimeType()]);
	}
    
    public function getPresetMappingsForFileReference(FileReference $ref)
    {
        return $this->getPresetMappingsForMimeType($ref->getMimeType());
    }
    
    public function getPresetMappingsForMimeType($mime)
    {
        return (isset($this->mimes[$mime])) ? $this->mimes[$mime] : false;
    }
    
    public function getPresetsForMimeType($mime)
    {
        if (isset($this->mimes[$mime])) {
            $presets = array();
            foreach ($this->mimes[$mime] as $def) {
                $presets[] = $def['preset'];
            }
            
            return array_unique($presets);
        }
        
        return false;
    }
    
    public function getMimeTypesForPreset($presetName)
    {
        $mimeTypes = array();
        foreach ($this->mimes as $mime => $defs) {
            foreach ($defs as $def) {
                if ($presetName === $def['preset']) {
                    $mimeTypes[] = $mime;
                }
            }
        }
        
        return empty($mimeTypes) ? false : $mimeTypes;
    }
    
    public function addPresetDefinitions(array $map)
    {
        foreach ($map as $mime => $defs) {
            foreach ($defs as $def) {
                if ($this->validatePresetMapping($def)) {
                    $this->mimes[$mime][] = $def;
                }
            }
        }
    }
    
    protected function validatePresetMapping(array $map) {
        if (!isset($map['preset']) || !isset($map['tag']) || !isset($map['quality']) || !isset($map['representation']) || !isset($map['extension'])) {
            throw new \InvalidArgumentException(sprintf("Preset mapping did not have the required fields."));
        }
        
        return true;
    }
}
