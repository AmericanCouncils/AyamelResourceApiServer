<?php

namespace Ayamel\ResourceBundle\Validation\File;

use Ayamel\ResourceBundle\Document\FileReference;

/**
 * Defines properties for validating image file attributes.
 *
 * @package AyamelResourceBundle
 * @author Evan Villemez
 */
class GenericImageAttributes extends AbstractFileAttributes
{
    public $frameSize;
    public $time;
    public $aspectRatio;
    public $units;

    public function validateFileReference(FileReference $ref, $context)
    {
        $attrs = $ref->getAttributes();

        if (isset($attrs['frameSize']['height']) && isset($attrs['frameSize']['width'])) {
            if (!isset($attrs['units'])) {
                $ref->setAttribute('units', 'px');
            }
        }
    }

}
