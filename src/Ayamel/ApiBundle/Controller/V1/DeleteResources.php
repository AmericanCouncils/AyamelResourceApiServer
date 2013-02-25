<?php
namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Controller\ApiController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Accepts parameters to remove multiple objects.
 *
 * @author Evan Villemez
 */
class DeleteResources extends ApiController
{
    public function executeAction(Request $request)
    {
        throw $this->createHttpException(501);
    }

}
