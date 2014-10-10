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
    protected $extraFields = [];

    public static function createFromArray(array $data)
    {
        $extras = [];
        $obj = new static();
        foreach ($data as $key => $val) {
            if (property_exists($obj, $key)) {
                $obj->$key = $val;
            } else {
                $extras[] = $key;
            }
        }

        $obj->setExtraFields($extras);

        return $obj;
    }

    public function setExtraFields(array $extras)
    {
        $this->extraFields = $extras;
    }

    public function getExtraFields()
    {
        return $this->extraFields;
    }
}
