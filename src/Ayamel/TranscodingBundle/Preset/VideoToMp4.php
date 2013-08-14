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
            '-acodec' => 'aac',
            '-ac' => '2',
            '-strict' => 'experimental',
            '-ab' => '160k',
            '-vcodec' => 'libx264',
            '-preset' => 'medium',
            '-profile:v' => 'high',
            '-level' => '30',
            '-maxrate' => '10000000',
            '-bufsize' => '10000000',
            '-b' => '1200k',
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
