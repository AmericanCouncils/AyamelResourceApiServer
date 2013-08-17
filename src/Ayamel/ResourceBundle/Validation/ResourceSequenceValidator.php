<?php

namespace Ayamel\ResourceBundle\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/*
 * Validates whether or not a Resource can be a sequence.
 */
class ResourceSequenceValidator extends ConstraintValidator
{
    protected $types;

    public function __construct(array $types)
    {
        $this->types = $types;
    }

    public function validate($value, Constraint $constraint)
    {
        if ($value->getSequence() && !in_array($value->getType(), $this->types)) {
            $this->context->addViolationAt('sequence', $constraint->message);
        }
    }
}
