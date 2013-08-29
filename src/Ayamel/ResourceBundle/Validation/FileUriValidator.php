<?php

namespace Ayamel\ResourceBundle\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/*
 * Validates that files specify at least one uri.
 */
class FileUriValidator extends ConstraintValidator
{
    public function validate($object, Constraint $constraint)
    {
        if (!$object->getDownloadUri() && !$object->getStreamUri()) {
            $this->context->addViolationAt('downloadUri', $constraint->message);
        }
    }
}
