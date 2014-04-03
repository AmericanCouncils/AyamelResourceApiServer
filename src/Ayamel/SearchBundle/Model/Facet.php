<?php

namespace Ayamel\SearchBundle\Model;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;

class Facet
{
    use ArrayFactoryTrait, AutoGetterSetterTrait;

    protected $field;       //name of field

    protected $size;

    protected $hits; //can be higher than total

    protected $missing;

    protected $other;

    protected $values;      //array<FacetValue>
}
