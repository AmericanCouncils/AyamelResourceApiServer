<?php

namespace Ayamel\ResourceBundle\Validation\File;

use Ayamel\ResourceBundle\Validation\AbstractAttributes;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Specifies validation for attributes of video files.
 *
 * @package AyamelResourceBundle
 * @author Evan Villemez
 */
class VideoAttributes extends AudioAttributes
{
    /**
     * @Assert\Type(type="integer")
     * @Assert\Range(min=0)
     */
    public $frameX;
    
    /**
     * @Assert\Type(type="integer")
     * @Assert\Range(min=0)
     */
    public $frameY;
    
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
