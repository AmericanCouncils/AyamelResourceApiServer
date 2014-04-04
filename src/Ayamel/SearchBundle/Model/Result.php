<?php

namespace Ayamel\SearchBundle\Model;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;

/**
 * Top-level document for a search result.
 */
class Result
{
    use ArrayFactoryTrait, AutoGetterSetterTrait;

    protected $query;

    protected $hits;

    protected $facets;

}
