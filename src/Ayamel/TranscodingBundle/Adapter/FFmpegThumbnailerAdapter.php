<?php

namespace Ayamel\TranscodingBundle\Adapter;

use AC\Transcoding\Preset;
use AC\Transcoding\Adapter;
use AC\Transcoding\File;
use AC\Transcoding\FileHandlerDefinition;
use AC\Transcoding\Adapter\AbstractCliAdapter;

class FFmpegThumbnailerAdapter extends AbstractCliAdapter
{
    protected $key = "ffmpegthumbnailer";
    protected $name = "FFmpeg Thumbnailer";
    protected $description = "Uses ffmpegthumbnailer presets extract thumbnails from videos.";

    private $thumbnailer_path;

    public function __construct($thumbnailer_path = 'ffmpegthumbnailer')
    {
        parent::__construct(array(
            'timeout' => 0
        ));

        $this->thumbnailer_path = $thumbnailer_path;
    }

    /**
     * {@inheritdoc}
     */
    public function verifyEnvironment()
    {
        if (!file_exists($this->thumbnailer_path)) {
            throw new \RuntimeException(sprintf("Could not find ffmpegthumbnailer executable, given path {%s}", $this->thumbnailer_path));
        }

        return true;
    }

    /**
     * Must receive binary files
     *
     * {@inheritdoc}
     */
    protected function buildInputDefinition()
    {
        return new FileHandlerDefinition(array(
            'requiredMimeEncodings' => array('binary'),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildProcess(File $inFile, Preset $preset, $outFilePath)
    {
        $options = array($this->thumbnailer_path, '-i', $inFile->getPathname());

        //add preset options
        foreach ($preset->getOptions() as $key => $value) {
            if (!is_null($key)) {
                $options[] = $key;
            }
            if (!is_null($value)) {
                $options[] = $value;
            }
        }

        $options[] = "-o";
        $options[] = $outFilePath;

        //get builder with required options for in/out file
        $builder = $this->getProcessBuilder($options);

        return $builder;
    }
}
