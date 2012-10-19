<?php

namespace Ayamel\TranscodingBundle;

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
	
	public function __construct($presetMap)
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
		
	}
    
    public function getPresetsForMime($mime)
    {
        
    }
    
    public function getMimesForPreset($preset)
    {
        
    }
    
    public function getPresetMapping($preset)
    {
        
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
