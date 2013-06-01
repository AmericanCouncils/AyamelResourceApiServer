<?php

namespace Ayamel\ResourceBundle\Validation\File;

use Ayamel\ResourceBundle\Validation\AbstractAttributes;

/**
 * Defines properties for validating audio file attributes.
 *
 * @package AyamelResourceBundle
 * @author Evan Villemez
 */
class AudioAttributes extends AbstractAttributes
{
    public $duration;
    public $averageBitrate;
}
