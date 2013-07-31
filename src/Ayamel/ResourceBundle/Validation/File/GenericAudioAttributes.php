<?php

namespace Ayamel\ResourceBundle\Validation\File;

/**
 * Defines properties for validating audio file attributes.
 *
 * @package AyamelResourceBundle
 * @author Evan Villemez
 */
class GenericAudioAttributes extends AbstractFileAttributes
{
    public $duration;
    public $bitrate;
    public $channels;
}
