<?php

namespace Ayamel\SearchBundle\Controller;

use Ayamel\ApiBundle\Controller\ApiController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Elastica\Query\QueryString;
use Elastica\Query;
use Elastica\Filter\Terms as TermsFilter;
use Elastica\Filter\Nested as NestedFilter;
use Elastica\Filter\Missing as MissingFilter;
use Elastica\Filter\BoolOr as BoolOrFilter;
use Elastica\Filter\BoolAnd as BoolAndFilter;

/**
 * Search controller for querying ElasticSearch, which implements two APIs for searching.
 *
 * @package AyamelSearchBundle
 */
class SearchV1 extends ApiController
{
    /**
     * Search for Resource objects based on many, potentially loosely-defined, criteria.  By
     * default searches include all publicly available resources, including resources visible
     * to the requesting client.
     *
     * @ApiDoc(
     *      resource=true,
     *      description="Search for resources",
     *      filters={
     *          {"name"="limit", "default"="10", "description"="bar"},
     *          {"name"="skip", "default"="0", "description"="bar"},
     *          {"name"="filter:type", "description"="bar"},
     *          {"name"="filter:client", "description"="barf"}
     *          {"name"="filter:language", "description"="bar"},
     *          {"name"="filter:subjectDomains", "description"="bar"},
     *          {"name"="filter:functionalDomains", "description"="barf"}
     *          {"name"="filter:registers", "description"="bar"},
     *          {"name"="facet:type", "description"="barf"}
     *          {"name"="facet:client", "description"="barf"}
     *          {"name"="facet:language", "description"="barf"}
     *      }
     * )
     */
    public function simpleSearchAction(Request $request)
    {
        $q = $request->query;
        if (!$queryText = $q->get('q', false)) {
            throw $this->createHttpException(400, "Searches must include a string query via the [q] parameter.");
        }

        //create query, and set the text to query
        $query = new Query();
        $queryString = new QueryString();
        $queryString->setQuery($queryText);
        $queryString->setDefaultOperator('AND');
        $query->setQuery($queryString);

        //limit and skip, with some internally enforced ranges
        $limit = ($l = $q->get('limit', 20)) > 100 ? 100 : (int) $l;
        $skip = ($s = $q->get('skip', 0)) > 1000 ? 1000 : (int) $s;
        $query->setFrom($skip);
        $query->setLimit($limit);

        //TODO: filters
        //  * resource.client.id
        //  * resource.clientUser.id
        //  * resource.language
        //    * languages.iso639_3 OR langauges.bcp47

        //create query filters, always enforcing a visibility filter
        $queryFilters = [$this->createVisibilityFilter()];

        //TODO: enforce filters derived from future AuthorizationPolicy

        if ($filterValue = $q->get('filter:type', false)) {
            $queryFilters[] = new TermsFilter('type', explode(',', strtolower($filterValue)));
        }
        if ($filterValue = $q->get('filter:subjectDomains', false)) {
            foreach ((array) $filterValue as $val) {
                $queryFilters[] = new TermsFilter('subjectDomains', explode(',', strtolower($val)));
            }
        }
        if ($filterValue = $q->get('filter:functionalDomains', false)) {
            foreach ((array) $filterValue as $val) {
                $queryFilters[] = new TermsFilter('functionalDomains', explode(',', strtolower($val)));
            }
        }
        if ($filterValue = $q->get('filter:registers', false)) {
            foreach ((array) $filterValue as $val) {
                $queryFilters[] = new TermsFilter('registers', explode(',', strtolower($val)));
            }
        }
        if ($filterValue = $q->get('filter:client', false)) {
            $f = new NestedFilter();
            $f->setPath('client');
            $f->setFilter(new TermsFilter('id', explode(',', strtolower($filterValue))));
            $queryFilters[] = $f;
        }
        if ($filterValue = $q->get('filter:language', false)) {
            $filterValue = explode(',', strtolower($filterValue));
            $queryFilters[] = (new BoolOrFilter())
                ->addFilter(new TermsFilter('languages.iso639_3', $langs))
                ->addFilter(new TermsFilter('languages.bcp47', $langs))
            ;
        }

        $query->setFilter((new BoolAndFilter())->setFilters($queryFilters));

        //TODO: facets
        //  * resource.type
        //  * resource.client
        //  * resource.language ... how to do this, really?
        //  * subjectDomains
        //  * functionalDomains
        //  * registers

        // There are a couple things that I could do here
        //  - use Elastica\Type, query->refesh() or query->optimize, all of which are used in the elastica tests.

        //perform query
        $type = $this->container->get('fos_elastica.index.ayamel')->getType('resource');
        $resultSet = $type->search($query);

        //convert result set
        $results = [
            'query' => [
                'limit' => $limit,
                'skip' => $skip,
                'total' => $resultSet->getTotalHits(),
                'time' => $resultSet->getTotalTime()
            ],
            'hits' => $this->filterResults($resultSet->getResults()),
            'facets' => []
        ];

        return $this->createServiceResponse($results, 200);
    }

    /**
     * Transform each Elastica\Result into response data.
     *
     * @param  array $results array of Elastica\Result
     * @return array
     */
    private function filterResults(array $results)
    {
        $arr = [];
        foreach ($results as $result) {
            $arr[] = [
                'score' => $result->getScore(),
                'resource' => $this->filterResultFields($result->getSource())
            ];
        }

        return $arr;
    }

    /**
     * Filters out unwanted data from each ES result
     *
     * @param  array $result raw es result array
     * @return array filtered data array
     */
    private function filterResultFields(array $result)
    {
        //TODO: filter out "content_*" fields
        //or possibly deserialize into a Resource model
        return $result;
    }

    protected function createVisibilityFilter()
    {

        //if known, where resource.visibility null OR currentClient in resource.visibility
        $apiClient = $this->getApiClient();

        //if anonymous, only search public resources with no visibility
        //restrictions (the field may be missing or null)
        $publiclyVisibleFilter = new MissingFilter('visibility');
        if (!$apiClient) {
            return $publiclyVisibleFilter;
        }

        //if we have a client, include results where the
        //visibility is public (missing), OR
        //where client id is in the visibility array
        $visibilityFilter = new BoolOrFilter();
        $visibilityFilter->addFilter($publiclyVisibleFilter);
        $visibilityFilter->addFilter(new TermsFilter('visibility', [$apiClient->getId()]));

        return $visibilityFilter;
    }

    /**
     * Use the raw ElasticSearch api to perform a more specific search by sending a JSON
     * query object.
     *
     * *NOT YET IMPLEMENTED*
     *
     * @ApiDoc(
     *      resource=true,
     *      description="Advanced search for Resources."
     * )
     */
    public function advancedSearchAction()
    {
        //TODO: move to separate controller class - going to get too large
        throw $this->createHttpException(501, 'Not implemented.');
    }
}
