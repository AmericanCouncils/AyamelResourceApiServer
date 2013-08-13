<?php

namespace Ayamel\TranscodingBundle\Preset;

use AC\Transcoding\Preset;
use AC\Transcoding\Preset\FFmpeg\BasePreset;
use AC\Transcoding\FileHandlerDefinition;
/*
-s hd720
-vcodec libvpx
-g 120
-lag-in-frames 16
-deadline good
-cpu-used 0
-vprofile 0
-qmax 51
-qmin 11
-slices 4
-b:v 2M
-acodec libvorbis
-ab 112k
-ar 44100
-f webm
*/
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
            '-vcodec' => 'libvpx',
            '-g' => '120',
            '-lag-in-frames' => '16',
            '-deadline' => 'good',
            '-cpu-used' => '0',
            '-vprofile' => '0',
            '-qmax' => '51',
            '-qmin' => '11',
            '-slices' => '4',
            '-b:v' => '2M',
            '-acodec' => 'libvorbis',
            '-ab' => '112k',
            '-ar' => '44100',
            '-f' => 'webm',
            '-threads' => "8"
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
