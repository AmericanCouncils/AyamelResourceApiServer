<?php

namespace Ayamel\ResourceBundle\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/*
 * This validator checks the attributes field of file references, depending on the reference
 * mime type.
 * 
 * @Annotation
 */
class FileAttributesValidator extends Constraint
{
    protected $map;
    protected $validator;
    
    public function __construct(Validator $validator, $map = array())
    {
        $this->validator = $validator;
        $this->map = $map;
    }
    
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
    
    public function validate($object, Constraint $constraint)
    {
        $mime = $object->getMimeType();
        if (!isset($this->map[$mime])) {
            $this->context->addViolationAt('mimeType', $constraint->message);
            return;
        }
        
        $attrs = $this->createAttributesClass($mime, $object->getAttributes());
        if (!$attrs) {
            $this->context->addViolationAt('mimeType', $constraint->message);
            return;
        }
        
        $errors = $this->validator->validate($attrs);
        
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->context->addViolationAt('attributes', $error);
            }
        }
    }
    
    protected function createAttributesClass($mime, $data)
    {
        foreach ($this->map as $class => $mimes) {
            if in_array($mime, $mimes) {
                return $class::createFromArray($data);
            }
        }
        
        return false;
    }
}
