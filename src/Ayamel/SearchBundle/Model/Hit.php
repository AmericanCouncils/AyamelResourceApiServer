<?php

namespace Ayamel\SearchBundle\Model;

use AC\ModelTraits\AutoGetterSetterTrait;
use AC\ModelTraits\ArrayFactoryTrait;

class Hit
{
    use ArrayFactoryTrait, AutoGetterSetterTrait;

    protected $score;

    protected $resource;

}
