<?php

namespace Ayamel\ResourceBundle\Validation\File;

use Ayamel\ResourceBundle\Validation\AbstractAttributes;

/**
 * Defines properties for validating video file attributes.
 *
 * @package AyamelResourceBundle
 * @author Evan Villemez
 */
class GenericVideoAttributes extends AbstractAttributes
{
    public $frameSize;
    public $duration;
    public $aspectRatio;
    public $frameRate;
    public $bitrate;
}
