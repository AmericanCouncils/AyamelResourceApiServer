<?php

namespace Ayamel\SearchBundle\Model;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;
use JMS\Serializer\Annotation as JMS;

/**
 * Top level data about facets requested.
 */
class Facet
{
    use ArrayFactoryTrait, AutoGetterSetterTrait;

    /**
     * The name of the facet, which usually corresponds to a field in the Resource.
     *
     * @JMS\Type("string")
     */
    protected $field;

    /**
     * The number of values returned for the facet.
     *
     * @JMS\Type("integer")
     */
    protected $size;

    /**
     * The total number of hits for this facet.  Note that this can be higher than the total number
     * of hits for the query.  This is because for facets on fields that contain more than one value
     * any particular Resource may be matched more than once - one time for every value.
     *
     * @JMS\Type("integer")
     */
    protected $hits;

    /**
     * The number of Resources that matched the query, but did not have a value for the facet
     * requested.  Generally this means that the Resource did not have a value for the field.
     *
     * @JMS\Type("integer")
     */
    protected $missing;

    /**
     * The number of hits on a facet that was not returned.  This would occur when the facet has potentially many
     * possible values, but only a subset were returned.  For example, unless otherwise specified, facets will return
     * a maximum of 10 values.
     *
     * @JMS\Type("integer")
     */
    protected $other;

    /**
     * Array of facet values and their count.
     *
     * @JMS\Type("array<Ayamel\SearchBundle\Model\FacetValue>")
     */
    protected $values;
}
