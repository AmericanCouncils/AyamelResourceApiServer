<?php
namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Controller\ApiController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class GetResources extends ApiController
{
    /**
     * Returns multiple resources based on some query parameters.  By default only Resources owned by the requesting client are returned.
     * 
     * @ApiDoc(
     *      resource=true,
     *      description="Retrieve multiple resources",
     *      filters={
     *          {"name"="_format", "default"="json", "description"="Return format, can be one of xml, yml or json."},
     *          {"name"="ids", "description"="Comma separated list of IDs for specific Resources to fetch."},
     *          {"name"="client", "description"="Comma separated list of API client owners. By default query returns resources owned by requesting client."},
     *          {"name"="type", "description"="Limit returned Resources to a certain type."},
     *          {"name"="client_user", "description"="Limit returned Resources to those owned by a specific user an API client."},
     *          {"name"="languages", "description"="Limit returned Resources to those containing a specific language."},
     *          {"name"="limit", "default"="50", "description"="Limit the number of ids to return."},
     *          {"name"="skip", "default"="0", "description"="Number of results to skip. Use this for paginating results."},
     *          {"name"="order", "default"="-1", "description"="Set to '1' for ascending, or '-1' for descending"}
     *      }
     * )
     *
     */
    public function executeAction()
    {
        
        //TODO: implement proper logic
        
        $request = $this->getRequest();
        $db = $this->container->get('doctrine_mongodb.odm.default_connection')->ayamel->resources;

        $limit = $request->query->get('limit', 50); //enforce a max (~100)
        $order = $request->query->get('order', -1);
        $skip = $request->query->get('skip', 0);

        $ids = array();
        $results = $db->find(array(), array('id' => 1))
                        ->limit($limit)
                        ->sort(array("_id" => $order))
                        ->skip($skip);

        //assemble final content structure
        return $this->createServiceResponse(array('ids' => array_keys(iterator_to_array($results))), 200);
    }
}
