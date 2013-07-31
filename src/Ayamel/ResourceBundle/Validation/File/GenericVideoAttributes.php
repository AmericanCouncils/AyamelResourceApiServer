<?php

namespace Ayamel\ResourceBundle\Validation\File;

/**
 * Defines properties for validating video file attributes.
 *
 * @package AyamelResourceBundle
 * @author Evan Villemez
 */
class GenericVideoAttributes extends AbstractFileAttributes
{
    public $frameSize;
    public $duration;
    public $aspectRatio;
    public $frameRate;
    public $bitrate;
}
