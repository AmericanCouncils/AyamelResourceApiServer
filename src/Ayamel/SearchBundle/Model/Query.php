<?php

namespace Ayamel\SearchBundle\Model;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;
use JMS\Serializer\Annotation as JMS;

/**
 * Contains basic hit and timing information about a search query result.
 */
class Query
{
    use ArrayFactoryTrait, AutoGetterSetterTrait;

    /**
     * The max number of hits returned.
     *
     * @JMS\Type("integer")
     */
    protected $limit;

    /**
     * The max number of results skipped.
     *
     * @JMS\Type("integer")
     */
    protected $skip;

    /**
     * The total number of hits that matched the search.
     *
     * @JMS\Type("integer")
     */
    protected $total;

    /**
     * The time in milliseconds taken to search the index.
     *
     * @JMS\Type("integer")
     */
    protected $time;
}
