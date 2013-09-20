<?php

namespace Ayamel\SearchBundle\Controller;

use Ayamel\ApiBundle\Controller\ApiController;
use Symfony\Component\HttpFoundation\Request;
use Elastica\Query\QueryString;
use Elastica\Query;
use Elastica\Filter\Term as TermFilter;
use Elastica\Filter\BoolOr as BoolOrFilter;
use Elastica\Filter\BoolAnd as BoolAndFilter;


/**
 * This controller implements two APIs for searching.
 *
 * @package AyamelSearchBundle
 * @author Evan Villemez
 */
class Search extends ApiController
{
    public function simpleSearchAction(Request $request)
    {
        throw $this->createHttpException(501, 'Not implemented.');
        
        if ($q = $request->query->get('q', false)) {
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
