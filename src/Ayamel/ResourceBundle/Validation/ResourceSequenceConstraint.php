<?php

namespace Ayamel\ResourceBundle\Validation;

use Symfony\Component\Validator\Constraint;

/*
 * Invokes checks for proper sequence types.
 *
 * @Annotation
 */
class ResourceSequenceConstraint extends Constraint
{
    public $message = "Resources of this type cannot be sequences.";

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'resource_sequence_validator';
    }
}
