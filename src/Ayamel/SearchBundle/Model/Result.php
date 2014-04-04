<?php

namespace Ayamel\SearchBundle\Model;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;
use JMS\Serializer\Annotation as JMS;

/**
 * Top-level model for a search result.
 */
class Result
{
    use ArrayFactoryTrait, AutoGetterSetterTrait;

    /**
     * General pagination and timing information about the search hits.
     *
     * @JMS\Type("Ayamel\SearchBundle\Model\Query")
     */
    protected $query;

    /**
     * Array of hits, with score.
     *
     * @JMS\Type("array<Ayamel\SearchBundle\Model\Hit>")
     */
    protected $hits;

    /**
     * Array of facets, if any were requested.
     *
     * @JMS\Type("array<Ayamel\SearchBundle\Model\Facet>")
     */
    protected $facets;

}
