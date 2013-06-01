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
        $mime = $object->getMimeType();
        $attrs = $this->createAttributesClass($mime, $object->getAttributes());
        if (!$attrs) {
            $this->context->addViolationAt('mimeType', sprintf("Files with mimeType [%s] cannot be validated.", $mime));
            return;
        }
        
        $errors = $this->validator->validate($attrs);
        
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->context->addViolationAt('attributes', $error);
            }
        }
        
        //check for extra fields
        if (count($attrs->getExtraFields()) > 0) {
            foreach ($attrs->getExtraFields() as $field) {
                $this->context->addViolationAt('attributes', $field." is not a valid field.");
            }
        }
    }
    
    protected function createAttributesClass($mime, $data)
    {
        foreach ($this->map as $class => $mimes) {
            if (in_array($mime, $mimes)) {
                return $class::createFromArray($data);
            }
        }
        
        return false;
    }
}
