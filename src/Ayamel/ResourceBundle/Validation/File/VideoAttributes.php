<?php

namespace Ayamel\ResourceBundle\Validation\File;

use Ayamel\ResourceBundle\Validation\AbstractAttributes;

/**
 * Defines properties for validating video files attributes.
 *
 * @package AyamelResourceBundle
 * @author Evan Villemez
 */
class VideoAttributes extends AbstractAttributes
{
    public $resolutionX;
    public $resolutionY;
    public $duration;
    public $averageBitrate;
}
