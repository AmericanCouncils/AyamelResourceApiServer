<?php

namespace Ayamel\SearchBundle\Controller;

use Ayamel\ApiBundle\Controller\ApiController;
use Ayamel\SearchBundle\Model\Result;
use Ayamel\SearchBundle\Model\Query;
use Ayamel\SearchBundle\Model\Hit;
use Ayamel\SearchBundle\Model\Facet;
use Ayamel\SearchBundle\Model\FacetValue;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\DeserializationContext;
use Elastica\Query\QueryString as ESQueryString;
use Elastica\Query as ESQuery;
use Elastica\Filter\Terms as TermsFilter;
use Elastica\Filter\Nested as NestedFilter;
use Elastica\Filter\Missing as MissingFilter;
use Elastica\Filter\BoolOr as BoolOrFilter;
use Elastica\Filter\BoolAnd as BoolAndFilter;
use Elastica\Facet\Terms as TermsFacet;

/**
 * Search controller for discovering Resources, which provides an simplified
 * GET API on top of ElasticSearch.
 *
 * @package AyamelSearchBundle
 */
class SearchV1 extends ApiController
{
    /**
     * Search for Resource objects based on a text query, filtering by criteria.  Searches include all publicly
     * available resources, as well as resources visible to the requesting client.  Searches may contain a variety
     * of filters and facets, described below.
     *
     * ### Filters ###
     *
     * When searching, you may filter results on certain fields.  All filters are specified in the format
     * `filter:<fieldName>=<values>`. Values can be passed as comma-delimited, to specify multiple allowed
     * values.  For example:
     *
     * * **?q=colorless%20green%20dreams&filter:type=video** - will return matches only where
     *     the resource is of type `video`
     * * **?q=colorless%20green%20dreams&filter:type=video,audio** - will return matches where
     *     the resource is either video, or audio
     *
     * For fields that can contain multiple values, such as `subjectDomains`, there are additional ways to specify a filter.
     *
     * * **?q=colorless%20green%20dreams&filter:subjectDomains=language,science** - will return matches where the resource contains either "language" or "science", or possibly both, as one of the values.
     * * **?q=colorless%20green%20dreams&filter:subjectDomains[]=language&filter:subjectDomains[]=science** - will contain matches where `subjectDomains` contains *both* "language" and "science".
     *
     * Any field that contains multiple values can be passed as an array shown above to specify an "AND" requirement.  Otherwise, a
     * comma-delimited list of values is interpreted as an "OR" requirement.
     *
     * ### Facets ###
     *
     * Facets allow you see the count of potential hits containing values for certain fields.  Facets are available for the same
     * fields that you are allowed to filter on, and are specified in the format `facet:<fieldName>`, or `facet:<fieldName>=<size>`.
     * By default, facets will contain a max of 10 values.  You may increase the size by specifying a larger facet size.  Also, note
     * that facet values are always ordered by the highest number of hits first.  So, if a particular facet potentially contains
     * many values, you will need to increase the size of the facet to see values with lower counts.  A few examples:
     *
     * * **?q=colorless%20green%20dreams&facet:type** - show the type facet
     * * **?q=colorless%20green%20dreams&facet:subjectDomains=20** - show the subject domains, including value counts for the top 20 most used values
     *
     * @ApiDoc(
     *      resource=true,
     *      description="Search for resources",
     *      output="Ayamel\SearchBundle\Model\Result",
     *      filters={
     *          {"name"="q", "required"="yes", "description"="The text to search on."},
     *          {"name"="limit", "default"="10", "description"="How many results to return.  Max 100."},
     *          {"name"="skip", "default"="0", "description"="Which result to start at. This in combination with `limit` can be used for paginating results.  Max 1000."},
     *          {"name"="filter:type", "description"="comma-delimited list of Resource types."},
     *          {"name"="filter:client", "description"="**Not yet implemented** Comma-delimited list of API Client ids."},
     *          {"name"="filter:clientUser", "description"="**Not yet implemented** Comma-delimited list of API Client User ids."},
     *          {"name"="filter:language", "description"="**Not yet implemented** Comma-delimited list of langauge codes.  Can be specified as an array."},
     *          {"name"="filter:subjectDomains", "description"="Comma-delimited list of subject domains.  Can be specified as an array."},
     *          {"name"="filter:functionalDomains", "description"="Comma-delimited list of functional domains.  Can be specified as an array."},
     *          {"name"="filter:registers", "description"="Comma-delimited list of registers.  Can be specified as an array."},
     *          {"name"="facet:type", "description"="Include facet for type."},
     *          {"name"="facet:client", "description"="**Not yet implemented** Include facet for client."},
     *          {"name"="facet:clientUser", "description"="**Not yet implemented** Include facet for client."},
     *          {"name"="facet:language", "description"="**Not yet implemented** Include facet for language."},
     *          {"name"="facet:subjectDomains", "description"="Include facet for subject domains."},
     *          {"name"="facet:functionalDomains", "description"="Include facet for functional domains."},
     *          {"name"="facet:registers", "description"="Include facet for registers."}
     *      }
     * )
     */
    public function searchAction(Request $request)
    {
        $q = $request->query;
        if (!$queryText = $q->get('q', false)) {
            throw $this->createHttpException(400, "Searches must include a string query via the [q] parameter.");
        }

        //create an Elastica query, and set the text to query
        $query = new ESQuery();
        $queryString = new ESQueryString();
        $queryString->setQuery($queryText);
        $queryString->setDefaultOperator('AND');
        $query->setQuery($queryString);

        //limit and skip, with some internally enforced ranges
        $limit = ($l = $q->get('limit', 20)) > 100 ? 100 : (int) $l;
        $skip = ($s = $q->get('skip', 0)) > 1000 ? 1000 : (int) $s;
        $query->setFrom($skip);
        $query->setLimit($limit);

        //TODO: nested field filters
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
            //TODO
            // $f = new NestedFilter();
            // $f->setPath('client');
            // $f->setFilter(new TermsFilter('client.id', explode(',', strtolower($filterValue))));
            // $queryFilters[] = $f;
        }
        if ($filterValue = $q->get('clientUser', false)) {
            //TODO
        }
        if ($filterValue = $q->get('filter:language', false)) {
            //TODO
            // $filterValue = explode(',', strtolower($filterValue));
            // $queryFilters[] = (new BoolOrFilter())
            //     ->addFilter(new TermsFilter('languages.iso639_3', $langs))
            //     ->addFilter(new TermsFilter('languages.bcp47', $langs))
            // ;
        }

        //add all the filters to the query
        $queryFilter = (new BoolAndFilter())->setFilters($queryFilters);
        $query->setFilter($queryFilter);

        //TODO: nested field facets
        //  * resource.client.id
        //  * resource.clientUser.id
        //  * resource.language ... how to do this, really?
        $queryFacets = [];
        if ($q->has('facet:type')) {
            $queryFacets[] = $this->createFacet('type', $q->get('facet:type', false));
        }
        if ($q->has('facet:subjectDomains')) {
            $queryFacets[] = $this->createFacet('subjectDomains', $q->get('facet:subjectDomains', false));
        }
        if ($q->has('facet:functionalDomains')) {
            $queryFacets[] = $this->createFacet('functionalDomains', $q->get('facet:functionalDomains', false));
        }
        if ($q->has('facet:registers')) {
            $queryFacets[] = $this->createFacet('registers', $q->get('facet:registers', false));
        }

        //add all the facets to the query
        foreach ($queryFacets as $facet) {
            //the main query filter also needs to apply to any facets used
            $facet->setFilter($queryFilter);
            $query->addFacet($facet);
        }

        //perform query
        $type = $this->container->get('fos_elastica.index.ayamel')->getType('resource');
        $resultSet = $type->search($query);

        //transform raw result into API result
        $result = Result::createFromArray([
            'query' => Query::createFromArray([
                'limit' => $limit,
                'skip' => $skip,
                'total' => $resultSet->getTotalHits(),
                'time' => $resultSet->getTotalTime()
            ]),
            'hits' => $this->filterResults($resultSet->getResults()),
            'facets' => $this->filterFacets($resultSet->getFacets())
        ]);

        return $this->createServiceResponse(['result' => $result], 200);
    }

    /**
     * Create Elastica facet, enforcing a default size.
     *
     * @param  string     $fieldName
     * @param  int|null   $size
     * @return TermsFacet
     */
    private function createFacet($fieldName, $size)
    {
        $facet = (new TermsFacet($fieldName))->setField($fieldName);
        $facet->setSize($size ? $size : 10);

        return $facet;
    }

    /**
     * Transform each Elastica\Result into response hit.
     *
     * @param  array      $results array of Elastica\Result
     * @return array<Hit>
     */
    private function filterResults(array $results)
    {
        $serializer = $this->container->get('serializer');

        $hits = [];
        foreach ($results as $result) {
            $hits[] = Hit::createFromArray([
                'score' => $result->getScore(),

                //This looks funny, but it's basically just saying "deserialize this object
                //from a raw array of data" (from ES).  It's called the "form" deserializer
                //because form data is made available as an already decoded array, and that was
                //its original use case.
                //
                //The custom group allows data that's normally not settable via the API to be
                //set during deserialization when coming from ElasticSearch.  Thus, most model
                //fields that would be "ReadOnly", just have a custom group instead.
                'resource' => $serializer->deserialize(
                    $result->getSource(),
                    'Ayamel\ResourceBundle\Document\Resource',
                    'form',
                    DeserializationContext::create()->setGroups(['search-decode', 'Default'])
                )
            ]);
        }

        return $hits;
    }

    /**
     * Convert specific facet values into result format.
     *
     * @param  array        $esfacets ES term facets
     * @return array<Facet>
     */
    private function filterFacets(array $esfacets)
    {
        $facets = [];

        foreach ($esfacets as $fieldName => $facet) {
            $facets[] = Facet::createFromArray([
                'field' => $fieldName,
                'size' => count($facet['terms']),
                'hits' => $facet['total'],
                'missing' => $facet['missing'],
                'other' => $facet['other'],
                'values' => $this->filterFacetValues($facet['terms'])
            ]);
        }

        return $facets;
    }

    /**
     * Convert specific facet values into result format.
     * @param  array             $vals ES term facet values
     * @return array<FacetValue>
     */
    private function filterFacetValues(array $vals = [])
    {
        $values = [];
        foreach ($vals as $val) {

            //TODO: andQuery and orQuery fields, useful for clients

            $values[] = FacetValue::createFromArray([
                'value' => $val['term'],
                'count' => $val['count']
            ]);
        }

        return $values;
    }

    /**
     * All queries have a visibility filter enforced to prevent clients
     * from discovering Resources in search they should not be allowed to
     * see.
     *
     * @return Filter
     */
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
}
