<?php
namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Controller\ApiController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;

class GetResources extends ApiController
{
    /**
     * Returns multiple resources based on some query parameters.  Unless otherwise specified only Resources owned by the requesting
     * client are returned.  Only resources visible to the requesting client are returned.
     *
     * Query filters can be comma-delimited strings for multiple values.
     *
     * @ApiDoc(
     *      resource=true,
     *      description="Retrieve multiple resources",
     *      filters={
     *          {"name"="_format", "default"="json", "description"="Return format, can be one of xml, yml or json."},
     *          {"name"="id", "description"="Comma separated list of IDs for specific Resources to fetch."},
     *          {"name"="client", "description"="Comma separated list of API client owners. By default query returns resources owned by requesting client."},
     *          {"name"="type", "description"="Limit returned Resources to a certain type."},
     *          {"name"="status", "description"="Filter returned Resources by status."},
     *          {"name"="genres", "description"="Filter returned Resources by genres"
     *          {"name"="authenticity", "description"="Filter returned Resources by authenticity"
     *          {"name"="formats", "description"="Filter returned Resources by formats"
     *          {"name"="functions", "description"="Filter returned Resources by functions"
     *          {"name"="topics", "description"="Filter returned Resources by topics"
     *          {"name"="clientUser", "description"="Limit returned Resources to those owned by a specific user an API client."},
     *          {"name"="languages", "description"="Limit returned Resources to those containing a specific language.  This can be specified in either the ISO 639-3 format or BCP47 format."},
     *          {"name"="public", "description"="Must be 'true' or 'false'.  Will filter resources based on whether or not they have visibility restrictions."},
     *          {"name"="limit", "default"="20", "max": "100", "description"="Limit the number of ids to return."},
     *          {"name"="skip", "default"="0", "description"="Number of results to skip. Use this for paginating results."}
     *      }
     * )
     *
     */
    public function executeAction(Request $req)
    {
        $q = $req->query;

        //create raw query filters
        $filters = [];

        if ($ids = $q->get('id', false)) {
            $filters['id'] = explode(',', $ids);
        }
        if ($type = $q->get('type', false)) {
            $filters['type'] = explode(',', $type);
        }
        if ($status = $q->get('status', false)) {
            $filters['status'] = explode(',', $status);
        }
        if ($genres = $q->get('genres', false)) {
            $filters['genres'] = explode(',', $genres);
        }
        if ($authenticity = $q->get('authenticity', false)) {
            $filters['authenticity'] = explode(',', $authenticity);
        }
        if ($formats = $q->get('formats', false)) {
            $filters['formats'] = explode(',', $formats);
        }
        if ($functions = $q->get('functions', false)) {
            $filters['functions'] = explode(',', $functions);
        }
        if ($topics = $q->get('topics', false)) {
            $filters['topics'] = explode(',', $topics);
        }
        if ($clients = $q->get('client', false)) {
            $filters['client.id'] = explode(',', $clients);
        }
        if ($clientUsers = $q->get('clientUser', false)) {
            $filters['clientUser.id'] = explode(',', $clientUsers);
        }

        //get query builder
        $qb = $this->getRepo('AyamelResourceBundle:Resource')->getQBForResources($filters);

        //langs filter is an "or", to allow clients to simplify language
        //queries
        if ($languages = $q->get('languages', false)) {
            $langs = explode(',', $languages);
            $qb->addAnd($qb->expr()
                ->addOr($qb->expr()->field('languages.iso639_3')->in($langs))
                ->addOr($qb->expr()->field('languages.bcp47')->in($langs))
            );
        }

        //check for "public" filter
        switch ($q->get('public', null)) {
            case null: $public = null; break;
            case 'true': $public = true; break;
            case 'false': $public = false; break;
            default: throw $this->createHttpException(400, 'The "public" filter must be "true" or "false", if specified.');
        }

        //enforce the visibility filter, conditional on "public" filter
        $qb->addAnd($this->createVisibilityFilter($qb, $public));

        //enforce not returning deleted resources
        $qb->addAnd($qb->expr()->field('status')->notEqual('deleted'));

        //enforce default limits/skips
        $limit = ($l = (int) abs($q->get('limit', 20))) <= 100 ? $l : 100;
        $qb->limit($limit);
        $qb->skip($skip = (int) abs($q->get('skip', 0)));

        $results = $qb->getQuery()->execute();

        //assemble final content structure
        return $this->createServiceResponse([
            'total' => (int) $results->count(),
            'limit' => $limit,
            'skip' => $skip,
            'resources' => array_values(iterator_to_array($results))
        ], 200);
    }

    /**
     * Creates the visibility query expression, which is conditional on the "public" filter.
     */
    private function createVisibilityFilter($qb, $public)
    {
        $apiClient = $this->getApiClient();
        $expressions = [];

        //always find public resources, unless public is explicitly false
        if (false !== $public) {
            $expressions[] = $qb->expr()->field('visibility')->size(0);
            $expressions[] = $qb->expr()->field('visibility')->exists(false);
        }

        //if we know the API client, and public is not explicitly true, include
        //requesting client visibility
        if ($apiClient && true !== $public) {
            $expressions[] = $qb->expr()->field('visibility')->equals($apiClient->getId());
        }

        if (empty($expressions)) {
            throw new \LogicException("Visibility filter must include at least one expression.");
        }

        $visibilityFilter = $qb->expr();
        foreach ($expressions as $expression) {
            $visibilityFilter->addOr($expression);
        }

        return $visibilityFilter;
    }
}
