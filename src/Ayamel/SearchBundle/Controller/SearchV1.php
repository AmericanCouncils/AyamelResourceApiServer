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
use Elastica\Query\QueryString;
use Elastica\Query\SimpleQueryString;
use Elastica\Query\MatchAll;
use Elastica\Query as ElasticaQuery;
use Elastica\Filter\Terms as TermsFilter;
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
     * ## General behavior ##
     *
     * The purpose of the Search API is to allow easier discovery of consumable Resources. To do this, it allows
     * quick searching/filtering on the contents of the Library.  However, there are some major differences between
     * the Search API, and the `GET /resources` API.
     *
     * * Only consumable Resources are visible in Search, meaning:
     *     * no deleted Resources
     *     * no Resources that have empty content
     * * Resources may contain Relations - but only the Relations created by owning Client.
     * * Resources of type `data`, which are meant to be consumed by programs (not people), are not stored in the index.
     *
     * ## Query Text ##
     *
     * The text to perform the query on can be specified via one of two parameters.  The `q` parameter allows query
     * strings in the [*SimpleQueryString*  format](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-simple-query-string-query.html#_simple_query_string_syntax)
     * described in the ElasticSearch documentation.  Generally, this is the option that should be used, as it is
     * what most end-users will be familiar with from other search applications.
     *
     * If you specify the `s` parameter instead, the string passed will be parsed according the [*QueryString*
     * format](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-query-string-query.html#query-string-syntax)
     * described in the ElasticSearch documentation.  This option allows for more complex queries, but are more
     * difficult to construct.
     *
     * If you do not specify a query string via the `q` or `s` parameter - then all visible results will be returned. In
     * some cases, this may be preferable to using the the `GET /resources` API, for example if you want to do categorizations
     * of Resources using facets (described below).
     *
     * ## Filters ##
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
     * For fields that can contain multiple values, such as `topics`, there are additional ways to specify a filter.
     *
     * * **?q=colorless%20green%20dreams&filter:topics=language,science** - will return matches where the resource contains either "language" or "science", or possibly both, as one of the values.
     * * **?q=colorless%20green%20dreams&filter:topics[]=language&filter:topics[]=science** - will contain matches where `topics` contains *both* "language" and "science".
     *
     * Any field that contains multiple values can be passed as an array shown above to specify an "AND" requirement.  Otherwise, a
     * comma-delimited list of values is interpreted as an "OR" requirement.
     *
     * ## Facets ##
     *
     * Facets allow you see the count of potential hits containing values for certain fields.  Facets are available for the same
     * fields that you are allowed to filter on, and are specified in the format `facet:<fieldName>`, or `facet:<fieldName>=<size>`.
     * By default, facets will contain a max of 10 values.  You may increase the size by specifying a larger facet size.  Also, note
     * that facet values are always ordered by the highest number of hits first.  So, if a particular facet potentially contains
     * many values, you will need to increase the size of the facet to see values with lower counts.  A few examples:
     *
     * * **?q=colorless%20green%20dreams&facet:type** - show the type facet
     * * **?q=colorless%20green%20dreams&facet:topics=20** - show the topics, including value counts for the top 20 most used values
     *
     *
     * @ApiDoc(
     *      resource=true,
     *      description="Search for resources",
     *      output="Ayamel\SearchBundle\Model\Result",
     *      filters={
     *          {"name"="q", "description"="The text to search on. Follows the SimpleQueryString format described above."},
     *          {"name"="s", "description"="The text to search on. Follows the QueryString format described above."},
     *          {"name"="limit", "default"="20", "description"="How many results to return.  Max 100."},
     *          {"name"="skip", "default"="0", "description"="Which result to start at. This in combination with `limit` can be used for paginating results.  Max 1000."},
     *          {"name"="filter:type", "description"="comma-delimited list of Resource types."},
     *          {"name"="filter:license", "description"="comma-delimited list of licenses."},
     *          {"name"="filter:client", "description"="Comma-delimited list of API Client ids."},
     *          {"name"="filter:clientUser", "description"="Comma-delimited list of API Client User ids."},
     *          {"name"="filter:language", "description"="Comma-delimited list of langauge codes.  Can be specified as an array."},
     *          {"name"="filter:topics", "description"="Comma-delimited list of topics.  Can be specified as an array."},
     *          {"name"="filter:functions", "description"="Comma-delimited list of functions.  Can be specified as an array."},
     *          {"name"="filter:authenticity", description"="Comma-delimited list of authenticity. Can be specified as an array."},
     *          {"name"="filter:formats", description"="Comma-delimited list of formats. Can be specified as an array."},
     *          {"name"="filter:genres", description"="Comma-delimited list of genres. Can be specified as an array."},
     *          {"name"="filter:registers", "description"="Comma-delimited list of registers.  Can be specified as an array."},
     *          {"name"="facet:type", "description"="Include facet for type."},
     *          {"name"="facet:license", "description"="Include facet for license."},
     *          {"name"="facet:authenticity", description"="Include facet for authenticity."},
     *          {"name"="facet:formats", description"="Include facet for formats."},
     *          {"name"="facet:genres", description"="Include facet for genres."},
     *          {"name"="facet:client", "description"="Include facet for client."},
     *          {"name"="facet:clientUser", "description"="Include facet for client."},
     *          {"name"="facet:language", "description"="Include facet for language."},
     *          {"name"="facet:topics", "description"="Include facet for topics."},
     *          {"name"="facet:functions", "description"="Include facet for functions."},
     *          {"name"="facet:registers", "description"="Include facet for registers."}
     *      }
     * )
     */
    public function searchAction(Request $request)
    {
        $q = $request->query;

        //the actual query type depends on which filters received
        if ($queryText = $q->get('q', false)) {
            $queryType = new SimpleQueryString($queryText);
        } elseif ($queryText = $q->get('s', false)) {
            $queryType = new QueryString($queryText);
        } else {
            $queryType = new MatchAll();
        }

        //create an Elastica query, and set the type of query
        $query = new ElasticaQuery();
        $query->setQuery($queryType);

        //limit and skip, with some internally enforced ranges
        $limit = ($l = abs((int) $q->get('limit', 20))) > 100 ? 100 : $l;
        $skip = ($s = abs((int) $q->get('skip', 0))) > 1000 ? 1000 : $s;
        $query->setFrom($skip);
        $query->setLimit($limit);

        //create query filters, always enforcing a visibility filter
        $queryFilters = [$this->createVisibilityFilter()];

        //TODO: enforce filters derived from future AuthorizationPolicy

        if ($filterValue = $q->get('filter:type', false)) {
            $queryFilters[] = new TermsFilter('type', explode(',', strtolower($filterValue)));
        }
        if ($filterValue = $q->get('filter:license', false)) {
            $queryFilters[] = new TermsFilter('license', explode(',', strtoupper($filterValue)));
        }
        if ($filterValue = $q->get('filter:topics', false)) {
            foreach ((array) $filterValue as $val) {
                $queryFilters[] = new TermsFilter('topics', explode(',', strtolower($val)));
            }
        }
        if ($filterValue = $q->get('filter:functions', false)) {
            foreach ((array) $filterValue as $val) {
                $queryFilters[] = new TermsFilter('functions', explode(',', strtolower($val)));
            }
        }
        if ($filterValue = $q->get('filter:registers', false)) {
            foreach ((array) $filterValue as $val) {
                $queryFilters[] = new TermsFilter('registers', explode(',', strtolower($val)));
            }
        }
        if ($filterValue = $q->get('filter:authenticity', false)) {
            foreach ((array) $filterValue as $val) {
                $queryFilters[] = new TermsFilter('authenticity', explode(',', strtolower($val)));
            }
        }
        if ($filterValue = $q->get('filter:formats', false)) {
            foreach ((array) $filterValue as $val) {
                $queryFilters[] = new TermsFilter('formats', explode(',', strtolower($val)));
            }
        }
        if ($filterValue = $q->get('filter:genres', false)) {
            foreach ((array) $filterValue as $val) {
                $queryFilters[] = new TermsFilter('genres', explode(',', strtolower($val)));
            }
        }
        if ($filterValue = $q->get('filter:client', false)) {
            $queryFilters[] = new TermsFilter('client.id', explode(',', strtolower($filterValue)));
        }
        if ($filterValue = $q->get('filter:clientUser', false)) {
            $queryFilters[] = new TermsFilter('clientUser.id', explode(',', $filterValue));
        }
        if ($filterValue = $q->get('filter:languages', false)) {
            foreach ((array) $filterValue as $val) {
                $langs = explode(',', strtolower($val));
                $queryFilters[] = (new BoolOrFilter())
                    ->addFilter(new TermsFilter('languages.iso639_3', $langs))
                    ->addFilter(new TermsFilter('languages.bcp47', $langs))
                ;
            }
        }

        //add all the filters to the query
        $queryFilter = (new BoolAndFilter())->setFilters($queryFilters);
        $query->setFilter($queryFilter);

        $queryFacets = [];
        if ($q->has('facet:type')) {
            $queryFacets[] = $this->createFacet('type', $q->get('facet:type', false));
        }
        if ($q->has('facet:license')) {
            $queryFacets[] = $this->createFacet('license', $q->get('facet:license', false));
        }
        if ($q->has('facet:topics')) {
            $queryFacets[] = $this->createFacet('topics', $q->get('facet:topics', false));
        }
        if ($q->has('facet:functions')) {
            $queryFacets[] = $this->createFacet('functions', $q->get('facet:functions', false));
        }
        if ($q->has('facet:registers')) {
            $queryFacets[] = $this->createFacet('registers', $q->get('facet:registers', false));
        }
        if ($q->has('facet:formats')) {
            $queryFacets[] = $this->createFacet('formats', $q->get('facet:formats', false));
        }
        if ($q->has('facet:genres')) {
            $queryFacets[] = $this->createFacet('genres', $q->get('facet:genres', false));
        }
        if ($q->has('facet:authenticity')) {
            $queryFacets[] = $this->createFacet('authenticity', $q->get('facet:authenticity', false));
        }
        if ($q->has('facet:client')) {
            $queryFacets[] = $this->createFacet('client.id', $q->get('facet:client', false), 'client');
        }
        if ($q->has('facet:clientUser')) {
            $queryFacets[] = $this->createFacet('clientUser.id', $q->get('facet:clientUser', false), 'clientUser');
        }
        if ($q->has('facet:languages')) {
            $queryFacets[] = $this->createFacet(['languages.iso639_3','languages.bcp47'], $q->get('facet:languages', false), 'languages');
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
     * @param  string|array $fields
     * @param  int|null     $size
     * @return TermsFacet
     */
    private function createFacet($fields, $size, $name = false)
    {
        $facet = new TermsFacet($name ? $name : $fields);

        if (is_array($fields)) {
            $facet->setFields($fields);
        } else {
            $facet->setField($fields);
        }

        $facet->setSize($size ? $size : 10);

        return $facet;
    }

    private function transformFacetFieldName($facetName)
    {
        return $facetName;
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

                //'highlight' => //TODO... also include (optionally?) hit highlights?

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
