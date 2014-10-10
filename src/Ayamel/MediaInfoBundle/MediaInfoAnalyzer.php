<?php

namespace Ayamel\MediaInfoBundle;

use AC\MediaInfoBundle\MediaInfo;
use Ayamel\FilesystemBundle\Analyzer\AnalyzerInterface;
use Ayamel\ResourceBundle\Document\FileReference;

/**
 * Uses mediainfo to fill in any available FileReference attributes.
 *
 * @package AyamelMediaInfoBundle
 * @author Evan Villemez
 */
class MediaInfoAnalyzer implements AnalyzerInterface
{
    protected $mediaInfo;

    public function __construct(MediaInfo $mediaInfo)
    {
        $this->mediaInfo = $mediaInfo;
    }

    public function acceptsFile(FileReference $ref)
    {
        if ($ref->getInternalUri()) {
            return true;
        }

        return false;
    }

    public function analyzeFile(FileReference $ref)
    {
        $path = $ref->getInternalUri();
        if (!file_exists($path)) {
            throw new \RuntimeException(sprintf("File %s could not be scanned, it could not be found.", $path));
        }

        //scan it
        $data = $this->mediaInfo->scan($path);

        //set what we can
        if (isset($data['file']['video'])) {
            $this->handleVideo($ref, $data);
        } elseif (isset($data['file']['audio'])) {
            $this->handleAudio($ref, $data);
        } elseif (isset($data['file']['image'])) {
            $this->handleImage($ref, $data);
        }

        return $ref;
    }

    protected function handleImage(FileReference $ref, $data)
    {
        $image = $data['file']['image'];
        $general = $data['file']['general'];

        if (isset($general['internet_media_type'])) {
            $ref->setMimeType($general['internet_media_type'][0]);
        }

        $attrs = [];

        if (isset($image['height']) && isset($image['width'])) {
            $attrs['frameSize'] = array(
                'height' => $this->getIntValue($image['height']),
                'width' => $this->getIntValue($image['width'])
            );
            $attrs['units'] = 'px';
            $attrs['aspectRatio'] = $this->calculateAspectRatio($attrs['frameSize']['width'], $attrs['frameSize']['width']);
        }

        foreach ($attrs as $key => $val) {
            $ref->setAttribute($key, $val);
        }
    }

    protected function handleVideo(FileReference $ref, $data)
    {
        $video = $data['file']['video'];
        $general = $data['file']['general'];

        if (isset($general['internet_media_type'])) {
            $ref->setMimeType($general['internet_media_type'][0]);
        }

        $attrs = [];

        if (isset($video['height']) && isset($video['width'])) {
            $attrs['frameSize'] = array(
                'height' => $this->getIntValue($video['height']),
                'width' => $this->getIntValue($video['width'])
            );
        }

        if (isset($video['display_aspect_ratio'])) {
            $attrs['aspectRatio'] = $this->parseAspectRatio($video['display_aspect_ratio']);
        }

        if (isset($video['frame_rate'])) {
            $attrs['frameRate'] = $this->parseFPS($video['frame_rate']);
        }

        if (isset($general['overall_bit_rate'])) {
            $attrs['bitrate'] = $this->getIntValue($general['overall_bit_rate']);
        }

        if (isset($general['duration'])) {
            $attrs['duration'] = $this->parseDuration($general['duration']);
        }

        foreach ($attrs as $key => $val) {
            $ref->setAttribute($key, $val);
        }
    }

    protected function handleAudio(FileReference $ref, $data)
    {
        $audio = $data['file']['audio'];
        $general = $data['file']['general'];

        if (isset($general['internet_media_type'])) {
            $ref->setMimeType($general['internet_media_type'][0]);
        }

        $attrs = [];

        if (isset($general['overall_bit_rate'])) {
            $attrs['bitrate'] = $this->getIntValue($general['overall_bit_rate']);
        }

        if (isset($general['duration'])) {
            $attrs['duration'] = $this->parseDuration($general['duration']);
        }

        if (isset($audio['channel_s_'])) {
            $attrs['channels'] = $this->getIntValue($audio['channel_s_']);
        }

        foreach ($attrs as $key => $val) {
            $ref->setAttribute($key, $val);
        }
    }

    protected function getFloatValue(array $val)
    {
        foreach ($val as $item) {
            if (is_numeric($item)) {
                return (float) $item;
            }
        }
    }

    protected function getIntValue(array $val)
    {
        foreach ($val as $item) {
            if (is_numeric($item)) {
                return (int) $item;
            }
        }
    }

    protected function parseAspectRatio(array $val)
    {
        foreach ($val as $item) {
            $exp = explode(':', $item);
            if (2 === count($exp)) {
                return $item;
            }
        }
    }

    //returns value in seconds
    protected function parseDuration(array $val)
    {
        $val = $this->getIntValue($val);

        return (int) round($val / 1000);
    }

    //returns nearest int
    public function parseFPS(array $val)
    {
        $val = $this->getFloatValue($val);

        return (int) round($val);
    }

    //returns bits/s
    public function parseBitrate(array $val)
    {
        $val = $this->getIntValue($val);

        return $val;
    }

    protected function calculateAspectRatio($width, $height)
    {
        $gcd = $this->calculateGCD($width, $height);

        return ($width / $gcd) . ':' . ($height / $gcd);
    }

    protected function calculateGCD($width, $height)
    {
        if ($width === 0 || $height === 0) {
            return abs(max(abs($width), abs($height)));
        }

        $r = $width % $height;

        return ($r != 0) ? $this->calculateGCD($height, $r) : abs($height);
    }
}
