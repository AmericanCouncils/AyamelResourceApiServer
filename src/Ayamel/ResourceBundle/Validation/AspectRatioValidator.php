<?php

namespace Ayamel\ResourceBundle\Validation;

use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/*
 * Validates aspect ratio strings.
 */
class AspectRatioValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $exp = explode(':', $value);
        
        if (count($exp) !== 2) {
            //if it equals 0, it means it's a scalable vector image, so the
            //aspect ratio can change
            if (0 == $value) {
                return;
            }

            $this->context->addViolation($constraint->message);
        }
        
        if (!is_numeric($exp[0]) || !is_int((int) $exp[1])) {
            $this->context->addViolation($constraint->message);
        }
    }
}
