<?php

namespace Ayamel\ResourceBundle\Validation\File;

use Ayamel\ResourceBundle\Validation\AbstractAttributes;

/**
 * Defines properties for validating image file attributes.
 *
 * @package AyamelResourceBundle
 * @author Evan Villemez
 */
class GenericImageAttributes extends AbstractAttributes
{
    public $frameSize;
    public $time;
    public $aspectRatio;
    public $units;
}
