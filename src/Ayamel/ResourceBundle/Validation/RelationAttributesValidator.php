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
        $attrs = $object->getAttributes();

        //check map for validation class
        if (!isset($this->map[$type]) || is_null($this->map[$type])) {
            if (empty($attrs)) {
                return;
            }

            //if not mapped and there are attributes, it's invalid
            $this->context->addViolationAt('attributes', sprintf("Relation of type [%s] cannot contain attributes.", $type));

            return;
        }

        //create validation class and validate the attributes
        $class = $this->map[$type];
        $attrs = $class::createFromArray($object->getAttributes());

        //add validation errors
        $errors = $this->validator->validate($attrs);
        $extras = $attrs->getExtraFields();
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->context->addViolationAt('attributes', $error->getMessage());
            }
        }
        if (!empty($extras)) {
            foreach ($extras as $field) {
                $this->context->addViolationAt('attributes', sprintf("[%s] is an invalid attribute for Relations of type [%s]", $field, $type));
            }
        }
    }
}
