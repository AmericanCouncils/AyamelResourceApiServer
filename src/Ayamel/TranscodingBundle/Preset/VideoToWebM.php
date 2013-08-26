<?php

namespace Ayamel\TranscodingBundle\Preset;

use AC\Transcoding\Preset;
use AC\Transcoding\Preset\FFmpeg\BasePreset;
use AC\Transcoding\FileHandlerDefinition;

/**
 * For more information on this preset please visit this link: https://trac.handbrake.fr/wiki/BuiltInPresets#classic
 */
class VideoToWebM extends BasePreset
{
    protected $key = "ffmpeg.video_to_webm";
    protected $name = "Video to webm";
    protected $description = "Converts a video into .webm format.";

    /**
     * Specify the options for this specific preset
     */
    public function configure()
    {
        $this->setOptions(array(
            '-codec:v' => 'libvpx',
            '-deadline' => 'good',
            '-cpu-used' => '0',
            '-codec:a' => 'libvorbis',
            '-b:a' => '128k',
            '-f' => 'webm',
            '-threads' => "0"
        ));
    }

    protected function buildOutputDefinition()
    {
        return new FileHandlerDefinition(array(
            'requiredType' => 'file',
            'requiredExtension' => 'webm',
            'inheritInputExtension' => false,
        ));
    }

}
