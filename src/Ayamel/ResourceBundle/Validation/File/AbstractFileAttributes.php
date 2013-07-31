<?php

namespace Ayamel\ResourceBundle\Validation\File;

use Ayamel\ResourceBundle\Document\FileReference;
use Ayamel\ResourceBundle\Validation\AbstractAttributes;

/**
 * Used internally in the FileAttributesValidator to allow attribute validators to pre-process the entire file reference.
 *
 * @package AyamelResourceBundle
 * @author Evan Villemez
 */
abstract class AbstractFileAttributes extends AbstractAttributes
{
    public function validateFileReference(FileReference $ref, $context)
    {

    }
}
