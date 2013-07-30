<?php

namespace Ayamel\MediaInfoBundle;

use AC\MediaInfoBundle\MediaInfo;
use Ayamel\FilesystemBundle\Analyzer\AnalyzerInterface;
use Ayamel\ResourceBundle\Document\FileReference;

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
        } else if (isset($data['file']['audio'])) {
            $this->handleAudio($ref, $data);
        } else if (isset($data['file']['image'])) {
            $this->handleImage($ref, $data);
        }
        
        return $ref;
    }
    
    protected function handleImage(FileReference $ref, $data)
    {
        $image = $data['file']['image'];
        $general = $data['file']['general'];
        
        if (isset($general['internet_media_type'])) {
            $ref->setMimeType($general['internet_media_type']);
        }
        
        $attrs = array();
        
        if (isset($image['height']) && isset($image['width'])) {
            $attrs['frameSize'] = array(
                'height' => $this->getNumericValue($image['height']),
                'width' => $this->getNumericValue($image['width'])
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
        
    }
    
    protected function handleAudio(FileReference $ref, $data)
    {
        
    }
    
    protected function getNumericValue($val)
    {
        foreach ($val as $item) {
            if (is_numeric($item)) {
                return (int) $item;
            }
        }
    }
    
    protected function parseDuration($val)
    {
        $val = $this->getNumericValue($val);
        
        return $val / 1000;
    }
    
    public function parseBitrate($val)
    {
        $val = $this->getNumericValue($val);
        
        //TODO: convert to kbps
        
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
