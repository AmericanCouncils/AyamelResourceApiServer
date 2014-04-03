<?php

namespace Ayamel\SearchBundle\Model;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;

class FacetValue
{
    use ArrayFactoryTrait, AutoGetterSetterTrait;

    protected $value;

    protected $count;

    protected $andQuery;

    protected $orQuery;
}
