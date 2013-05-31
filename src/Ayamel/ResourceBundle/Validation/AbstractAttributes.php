<?php

namespace Ayamel\ResourceBundle\Validation;

/**
 * Base class for defining attribute validation classes.  Attribute validation classes are created
 * internally in the various AttributeValidators.
 *
 * @package AyamelResourceBundle
 * @author Evan Villemez
 */
abstract class AbstractAttributes
{
    public static function createFromArray(array $data)
    {
        $obj = new static();
        foreach ($data as $key => $val) {
            if (property_exists(array($obj, $key))) {
                $obj->$key = $val;
            }
        }
    }
}
