<?php

namespace Ayamel\ResourceBundle\Validation;

use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/*
 * This validator checks the attributes field of a Relation, depending on the Relation's type.
 */
class RelationAttributesValidator extends ConstraintValidator
{
    protected $map;
    protected $validator;

    public function __construct(ValidatorInterface $validator, $map = array())
    {
        $this->validator = $validator;
        $this->map = $map;
    }

    public function validate($object, Constraint $constraint)
    {
        $type = $object->getType($object);

        if (!isset($this->map[$type]) || is_null($this->map[$type])) {
            return;
        }

        $class = $this->map[$type];
        $attrs = $class::createFromArray($object->getAttributes());

        $errors = $this->validator->validate($attrs);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->context->addViolationAt('attributes', $error->getMessage());
            }
        }
    }
}
