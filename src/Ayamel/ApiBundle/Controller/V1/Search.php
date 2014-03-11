<?php

namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Controller\ApiController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Elastica\Query\QueryString;
use Elastica\Query;
use Elastica\Filter\Term as TermFilter;
use Elastica\Filter\BoolOr as BoolOrFilter;
use Elastica\Filter\BoolAnd as BoolAndFilter;

/**
 * This controller implements two APIs for searching.
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
 *
 * @package AyamelSearchBundle
 * @author Evan Villemez
 */
class Search extends ApiController
{
    public function searchForResources(Request $request)
    {
        try {
            $q = $request->query->get('q');
        // TODO more specific exception
        } catch (\Exception $e) {
            throw $this->createHttpException(400, "Searches must include a string query via the [q] parameter.");
        }
        
        //limit and skip, with some internally enforced ranges
        $limit = ($l = $request->query->get('limit', 20)) > 100 ? 100 : $l;
        $skip = ($s = $request->query->get('skip', 0)) > 1000 ? 1000 : $s;
        $query = array('size' => $limit, 'from' => $skip);
        
        //TODO: raw query, or elastica?
        
        //create query and set limit/skip
        $query = new Query();
        $query->setFrom($limit);
        $query->setLimit($skip);

        //the text to query
        $queryString = new QueryString();
        $queryString->setDefaultOperator('AND');
        $queryString->setQuery($q);
        
        //enforce client visibility
        $apiClient = $this->getApiClient();
        $publiclyVisibleFilter = new TermFilter();
        $publiclyVisibleFilter->setTerm('visibility', null);
        if ($apiClient) {
            $visibilityFilter = new BoolOrFilter();
            $visibilityFilter->addFilter($publiclyVisibleFilter);
            
        } else {   
        }
    }

    public function advancedSearchAction()
    {
        throw $this->createHttpException(501, 'Not implemented.');
    }
}

