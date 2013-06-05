<?php

namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Controller\ApiController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;

class Search extends ApiController
{
    /**
     * Search for Resource objects based on many, potentially loosely-defined, criteria.  By default searches include
     * all publicly available resources, including resources visible to the requesting client.
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
    public function searchForResources(Request $request)
    {
        throw $this->createHttpException(501, sprintf("Not yet implemented [%s]", __METHOD__));
    }

}
