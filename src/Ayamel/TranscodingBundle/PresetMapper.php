<?php

namespace Ayamel\TranscodingBundle;

use Ayamel\ResourceBundle\Document\FileReference;

/**
 * TODO: Document config format
 *
 * @package default
 * @author Evan Villemez
 */
class PresetMapper
{
	
	private $presets = array();
	private $mimes;
	
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
	
	public function canTranscodeFileReference(FileReference $ref)
	{
		return isset($this->mimes[$ref->getMimeType()]);
	}
    
    public function getPresetMappingsForFileReference(FileReference $ref)
    {
        $defs = array();
        if (isset($this->mimes[$ref->getMimeType()])) {
            foreach ($this->mimes[$ref->getMimeType()] as $presetName) {
                $defs[$presetName] = $this->presets[$presetName];
            }
        }
        
        return (!empty($defs)) ? $defs : false;
    }
    
    public function getPresetsForMimeType($mime)
    {
        return (isset($this->mimes[$mime])) ? $this->mimes[$mime] : false;
    }
    
    public function getMimeTypesForPreset($preset)
    {
        return (isset($this->presets[$preset])) ? $this->presets[$preset]['mimes'] : false;
    }
    
    public function getPresetMapping($preset)
    {
        return isset($this->presets[$preset]) ? $this->presets[$preset] : false;
    }
    
    public function addPresetDefinitions(array $defs)
    {
        foreach ($defs as $name => $def)
        {
            $this->presets[$name] = $def;
        }
        
        $this->mimes = $this->processMimeMap($this->presets);
    }
}
