<?php

namespace Ayamel\ResourceBundle\Validation;

use Symfony\Component\Validator\Constraint;

/*
 * This constraint invokes checks against a file attribute validation class determined
 * by the files mime type.
 * 
 * @Annotation
 */
class FileAttributesConstraint extends Constraint
{
    public $message = "The attributes are not appropriate for the file's mime type.";
    
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
    
    public function validatedBy()
    {
        return 'file_attributes_validator';
    }
}
