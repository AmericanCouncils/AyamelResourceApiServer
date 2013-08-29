<?php

namespace Ayamel\ResourceBundle\Validation;

use Symfony\Component\Validator\Constraint;

/*
 * Invokes checks to ensure that a valid URI exists.
 *
 * @Annotation
 */
class FileUriConstraint extends Constraint
{
    public $message = "Files must contain either a downloadUri, a streamUri, or both";

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'Ayamel\ResourceBundle\Validation\FileUriValidator';
    }
}
