<?php

namespace Ayamel\SearchBundle\Controller;

use Ayamel\ApiBundle\Controller\ApiController;

/**
 * This controller implements two APIs for searching.
 *
 * @package AyamelSearchBundle
 * @author Evan Villemez
 */
class Search extends ApiController
{
    public function simpleSearchAction()
    {
        throw $this->createHttpException(501, 'Not implemented.');
    }
    
    public function advancedSearchAction()
    {
        throw $this->createHttpException(501, 'Not implemented.');
    }
}
