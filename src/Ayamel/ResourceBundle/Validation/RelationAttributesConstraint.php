<?php

namespace Ayamel\ResourceBundle\Validation;

use Symfony\Component\Validator\Constraint;

/*
 * This constraint invokes checks against a Relation attribute validation class determined
 * by the Relation's type field.
 *
 * @Annotation
 */
class RelationAttributesConstraint extends Constraint
{
    public $message = "The attributes are not appropriate for the Relation's type.";

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'relation_attributes_validator';
    }
}
