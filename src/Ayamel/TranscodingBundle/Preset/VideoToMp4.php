<?php

namespace Ayamel\TranscodingBundle\Preset;

use AC\Transcoding\Preset;
use AC\Transcoding\Preset\FFmpeg\BasePreset;
use AC\Transcoding\FileHandlerDefinition;

/**
 * For more information on this preset please visit this link: https://trac.handbrake.fr/wiki/BuiltInPresets#classic
 */
class VideoToMp4 extends BasePreset
{
    protected $key = "ffmpeg.video_to_mp4";
    protected $name = "Video to MP4";
    protected $description = "Converts a video into h264 .mp4 format.";

    /**
     * Specify the options for this specific preset
     */
    public function configure()
    {
        $this->setOptions(array(
            '-codec:a' => 'aac',
            '-ac' => '2',
            '-strict' => 'experimental',
            '-b:a' => '128k',
            '-codec:v' => 'libx264',
            '-preset' => 'slow',
            '-profile:v' => 'high',
            '-f' => 'mp4',
            '-threads' => "0"
        ));
    }

    protected function buildOutputDefinition()
    {
        return new FileHandlerDefinition(array(
            'requiredType' => 'file',
            'requiredExtension' => 'mp4',
            'inheritInputExtension' => false,
        ));
    }

}
