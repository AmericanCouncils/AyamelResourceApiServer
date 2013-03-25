<?php
namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Controller\ApiController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Returns a resources object structure by its ID.
 */
class ViewResourceIds extends ApiController
{
    /**
     * Returns a list of all resource IDs available in the system.  By default returns the last 50
     * resource IDs created.
     *
     * @ApiDoc(
     *      resource=true,
     *      description="View available IDs",
     *      filters={
     *          {"name"="_format", "default"="json", "description"="Return format, can be one of xml, yml or json"},
     *          {"name"="limit", "default"=50, "description"="Limit the number of ids to return."},
     *          {"name"="order", "default"=-1, "description"="Set to '1' for ascending, or '-1' for descending"},
     *          {"name"="skip", "default"=0, "description"="Number of results to skip."}
     *      }
     * );
     *
     */
    public function executeAction()
    {
        $request = $this->getRequest();
        $db = $this->container->get('doctrine_mongodb.odm.default_connection')->ayamel->resources;

        $limit = $request->query->get('limit', 50);
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
