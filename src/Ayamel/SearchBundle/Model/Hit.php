<?php

namespace Ayamel\SearchBundle\Model;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;
use JMS\Serializer\Annotation as JMS;

/**
 * Contains relevancy information and a Resource.
 */
class Hit
{
    use ArrayFactoryTrait, AutoGetterSetterTrait;

    /**
     * The relevancy score computed by the search index.  Results are ordered
     * with highest relevancy scores first.
     *
     * @JMS\Type("double")
     */
    protected $score;

    /**
     * The Resource matched.  Note that the Resource is pulled from the index for
     * faster results, meaning that the Resource could be slightly out of date if very
     * recently modified, or deleted.
     *
     * Also, the Resource will contain relations created by the owning client of the Resource.
     *
     * @JMS\Type("Ayamel\ResourceBundle\Document\Resource")
     */
    protected $resource;
}
