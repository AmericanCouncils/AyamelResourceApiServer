<?php

namespace Ayamel\SearchBundle\Model;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;

class Query
{
    use ArrayFactoryTrait, AutoGetterSetterTrait;

    protected $limit;

    protected $skip;

    protected $total;

    protected $time;
}
