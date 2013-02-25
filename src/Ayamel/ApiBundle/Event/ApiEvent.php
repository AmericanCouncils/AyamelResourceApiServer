<?php

namespace Ayamel\ApiBundle\Event;

use Ayamel\ResourceBundle\Document\Resource;
use Symfony\Component\EventDispatcher\Event;

/**
 * Base API Event makes a Resource object available.
 *
 * @package AyamelApiBundle
 * @author Evan Villemez
 */
class ApiEvent extends Event
{
    protected $resource = false;

    public function __construct(Resource $resource = null)
    {
        if ($resource) {
            $this->resource = $resource;
        }
    }

    /**
     * Get Resource associated with the API Event.
     *
     * @return Ayamel\ResourceBundle\Document\Resource;
     */
    public function getResource()
    {
        return $this->resource;
    }

}
