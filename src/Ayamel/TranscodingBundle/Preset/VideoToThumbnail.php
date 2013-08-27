<?php

namespace Ayamel\TranscodingBundle\Preset;

use AC\Transcoding\Preset;
use AC\Transcoding\FileHandlerDefinition;

class VideoToThumbnail extends Preset
{
	protected $requiredAdapter = 'ffmpegthumbnailer';
    protected $key = "ffmpegthumbnailer.thumbnail";
    protected $name = "Video to Thumbnail";
    protected $description = "Extracts a thumbnail from a video.";

	protected function buildOutputDefinition()
	{
        return new FileHandlerDefinition(array(
            'requiredType' => 'file',
            'allowedExtensions' => array('png','jpg','jpeg', 'gif','tiff')
        ));
	}

	protected function buildInputDefinition()
    {
        return new FileHandlerDefinition(array(
            'requiredType' => 'file',
            'requiredEncoding' => 'binary'
        ));
    }

    public function configure()
    {
        $this->setOptions(array(
            '-t' => '50%',
            '-s' => '0',
            '-q' => '8'
        ));
    }

}
