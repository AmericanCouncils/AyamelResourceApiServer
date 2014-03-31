<?php

namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Controller\ApiController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Elastica\Query\QueryString;
use Elastica\Query;
use Elastica\Filter\Term as TermFilter;
use Elastica\Filter\BoolOr as BoolOrFilter;

/**
 * Search controller for querying ElasticSearch.
 *
 * @package AyamelSearchBundle
 */
class Search extends ApiController
{
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
     */
    public function searchForResources(Request $request)
    {
        if (!$q = $request->query->get('q', false)) {
            throw $this->createHttpException(400, "Searches must include a string query via the [q] parameter.");
        }

        //limit and skip, with some internally enforced ranges
        $limit = ($l = $request->query->get('limit', 20)) > 100 ? 100 : $l;
        $skip = ($s = $request->query->get('skip', 0)) > 1000 ? 1000 : $s;
        $query = array('size' => $limit, 'from' => $skip);

        //create query and set limit/skip
        $query = new Query();
        $query->setFrom($limit);
        $query->setLimit($skip);

        //the text to query
        $queryString = new QueryString();
        $queryString->setQuery($q);
        $queryString->setDefaultOperator('AND');

        //TODO: filters
        //  * resource.type
        //  * resource.client
        //  * resource.languages
        //    * languages.iso639_3 OR langauges.bcp47
        //  * subjectDomains
        //  * functionalDomains
        //  * registers

        //TODO: facets
        //  * resource.type
        //  * resource.client
        //  * resource.languages ... how to do this, really?
        //  * subjectDomains
        //  * functionalDomains
        //  * registers

        //TODO: enforce proper client visibility filter
        //  * if anon, where resource.visibility null
        //  * if known, where resource.visibility null OR currentClient in resource.visibility

        // There are a couple things that I could do here - use Elastica\Type, query->refesh() or query->optimize, all of which are used in the elastica tests.

        $query->setQuery($queryString);

        $index = $this->container->get('fos_elastica.index.ayamel');

        $results = $index->search($query);

        // TODO: turn this result set into something more useful that can be returned to the client
        return $this->createServiceResponse([
            'query' => $query,
            'results' => $results
        ], 200);
    }

    protected function createVisibilityFilter()
    {
        // $apiClient = $this->getApiClient();
        // $publiclyVisibleFilter = new TermFilter();
        // $publiclyVisibleFilter->setTerm('visibility', null);
        // if ($apiClient) {
        //     $visibilityFilter = new BoolOrFilter();
        //     $visibilityFilter->addFilter($publiclyVisibleFilter);

        // } else {
        // }
    }

    public function advancedSearchAction()
    {
        throw $this->createHttpException(501, 'Not implemented.');
    }
}
