<?php

namespace Ayamel\ResourceBundle\Validation;

use Symfony\Component\Validator\Constraint;

/*
 * Invokes checks for a valid aspect ratio string.
 *
 * @Annotation
 */
class AspectRatioConstraint extends Constraint
{
    public $message = "Invalid aspect ratio.  Should be in the format '[int]:[int]' or '[float]:[int]'.";

    public function validatedBy()
    {
        return 'Ayamel\ResourceBundle\Validation\AspectRatioValidator';
    }
}
