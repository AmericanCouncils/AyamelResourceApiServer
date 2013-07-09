<?php

namespace Ayamel\ResourceBundle\Validation\File;

use Ayamel\ResourceBundle\Validation\AbstractAttributes;

/**
 * Defines properties for validating audio file attributes.
 *
 * @package AyamelResourceBundle
 * @author Evan Villemez
 */
class GenericAudioAttributes extends AbstractAttributes
{
    public $duration;
    public $bitrate;
    public $channels;
}
