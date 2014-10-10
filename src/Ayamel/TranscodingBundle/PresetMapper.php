<?php

namespace Ayamel\TranscodingBundle;

use Ayamel\ResourceBundle\Document\FileReference;

/**
 * The PresetMapper maps file mime types to transcoding presets, along
 * with other metadata that is needed by the Resource Library. The PresetMapper
 * is used internally in the TranscodeManager for this purpose.
 *
 * @package AyamelTranscodingBundle
 * @author Evan Villemez
 */
class PresetMapper
{

    private $presets = [];
    private $map = [];

    /**
     * Constructor expects two arguments describing the available presets.
     *
     * Presets in the form of (YAML):
     *
     *      name_of_preset:
     *          preset_service: 'transcoder.preset.service_name'
     *          tag: 'low'
     *          extension: 'mp3'
     *          representation: 'transcoding'
     *          quality: 2
     *
     * Preset map in the form of (YAML):
     *
     *      'video/quicktime': [name_of_preset]
     *
     * @param array $presets Map of presetDefinitionKey => array of preset data
     * @param array $map     Map of mimeType => array of preset definition keys
     */
    public function __construct(array $presets = [], array $map = [])
    {
        foreach ($presets as $key => $data) {
            if ($this->validatePreset($data)) {
                $this->addPreset($key, $data);
            }
        }

        $this->map = $map;
    }

    public function getPreset($key)
    {
        return (isset($this->presets[$key])) ? $this->presets[$key] : false;
    }

    public function getPresets()
    {
        return $this->presets;
    }

    public function getMap()
    {
        return $this->map;
    }

    public function addPreset($key, array $data)
    {
        $this->presets[$key] = $data;
    }

    /**
     * Return true/false if the given FileReference can be
     * transcoded based on the available preset mappings.
     *
     * @param  FileReference $ref
     * @return boolean
     */
    public function canTranscodeFileReference(FileReference $ref)
    {
        return isset($this->map[$ref->getMimeType()]);
    }

    public function getPresetMappingsForFileReference(FileReference $ref)
    {
        return $this->getPresetMappingsForMimeType($ref->getMimeType());
    }

    public function getPresetMappingsForMimeType($mime)
    {
        if (!isset($this->map[$mime])) {
            return false;
        }

        $presets = [];
        foreach ($this->map[$mime] as $key) {
            if (isset($this->presets[$key])) {
                $presets[$key] = $this->presets[$key];
            }
        }

        return $presets;
    }

    public function getPresetsForMimeType($mime)
    {
        if (isset($this->map[$mime])) {
            return $this->map[$mime];
        }

        return false;
    }

    public function getMimeTypesForPreset($presetName)
    {
        $mimeTypes = [];
        foreach ($this->map as $mime => $presets) {
            if (in_array($presetName, $presets)) {
                $mimeTypes[] = $mime;
            }
        }

        return empty($mimeTypes) ? false : $mimeTypes;
    }

    protected function validatePreset(array $map)
    {
        if (!isset($map['preset_service']) || !isset($map['tag']) || !isset($map['quality']) || !isset($map['representation']) || !isset($map['extension'])) {
            return false;
        }

        return true;
    }
}
