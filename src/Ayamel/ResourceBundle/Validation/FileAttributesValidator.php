<?php

namespace Ayamel\ResourceBundle\Validation;

use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/*
 * This validator checks the attributes field of file references, depending on the reference
 * mime type.
 */
class FileAttributesValidator extends ConstraintValidator
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
        //all file attributes are optional from a validation standpoint. If there are none, it is valid
        $fileAttributes = $object->getAttributes();
        if (empty($fileAttributes)) {
            return;
        }
        
        $mime = $object->getMimeType();
        $attrs = $this->getAttributesClasses($mime, $object->getAttributes());
        if (empty($attrs)) {
            $this->context->addViolationAt('mimeType', sprintf("Files with mimeType [%s] cannot be validated.", $mime));

            return;
        }

        $errors = array();

        //validate each applicable attributes class
        foreach ($attrs as $attr) {
            $failures = $this->validator->validate($attr);
            if (!empty($failures)) {
                foreach ($failures as $fail) {
                    $errors[] = $fail;
                }
            }
        }
        
        //add violations, if any
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->context->addViolationAt('attributes', $error);
            }
        }

        //check for fields that were NOT validated by any of the mapped attributes classes
        $total = count($attrs);
        $extras = array();
        foreach ($attrs as $attr) {
            foreach ($attr->getExtraFields() as $field) {
                $extras[$field] = (isset($extras[$field])) ? $extras[$field] + 1 : 1;
            }
        }
        foreach ($extras as $field => $count) {
            if ($count >= $total) {
                $this->context->addViolationAt('attributes', $field." is not a valid attribute for this type of file.");
            }
        }
    }

    protected function getAttributesClasses($mime, $data)
    {
        $classes = array();
        foreach ($this->map as $class => $mimes) {
            if (in_array($mime, $mimes)) {
                $classes[] = $class::createFromArray($data);
            }
        }

        return $classes;
    }
}
