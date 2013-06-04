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
    protected $extraFields = array();

    public static function createFromArray(array $data)
    {
        $obj = new static();
        foreach ($data as $key => $val) {
            if (property_exists($obj, $key)) {
                $obj->$key = $val;
            } else {
                $extraFields[] = $key;
            }
        }

        return $obj;
    }

    public function getExtraFields()
    {
        return $this->extraFields;
    }
}
