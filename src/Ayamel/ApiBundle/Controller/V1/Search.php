<?php

namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Controller\ApiController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Search controller for searching ElasticSearch index for
 * Resource objects.
 *
 * @package AyamelApiBundle
 * @author Evan Villemez
 */
class Search extends ApiController
{
    /**
     * Search for Resource objects based on many, potentially loosely-defined, criteria.
     *
     * @ApiDoc(
     *      resource=true,
     *      description="Search for resources",
     *      filters={
     *          {"name"="foo", "description"="bar"},
     *          {"name"="baz", "description"="barf"}
     *      }
     * )
     */
    public function searchForResources()
    {
        throw $this->createHttpException(501, sprintf("Not yet implemented [%s]", __METHOD__));
    }

}
