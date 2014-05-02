<?php

namespace Ayamel\SearchBundle\Model;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;
use JMS\Serializer\Annotation as JMS;

/**
 * Facet value and count information.
 */
class FacetValue
{
    use ArrayFactoryTrait, AutoGetterSetterTrait;

    /**
     * The actual value for the given facet.
     *
     * @JMS\Type("string")
     */
    protected $value;

    /**
     * The count of Resources that contain the given field value.
     *
     * @JMS\Type("integer")
     */
    protected $count;

    /**
     * **NOT IMPLEMENTED**
     *
     * The search query string that would apply an AND filter for Resources
     * that match the value of this facet.
     *
     * @JMS\Type("string")
     */
    protected $andQuery;

    /**
     * **NOT IMPLEMENTED**
     *
     * The search query string that would apply an OR filter for Resources
     * that match the value of this facet.
     *
     * @JMS\Type("string")
     */
    protected $orQuery;
}
