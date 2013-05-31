<?php

namespace Ayamel\ResourceBundle\Validation\File;

use Ayamel\ResourceBundle\Validation\AbstractAttributes;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Specifies validation for attributes of audio files.
 *
 * @package AyamelResourceBundle
 * @author Evan Villemez
 */
class AudioAttributes extends AbstractAttributes
{
    /**
     * @Assert\Type(type="integer")
     * @Assert\Range(min=0)
     */
    public $duration;

    /**
     * @Assert\Type(type="integer")
     * @Assert\Range(min=0)
     */
    public $averageBitrate;
    
}
