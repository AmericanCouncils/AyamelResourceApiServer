<?php

namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Controller\ApiController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class EmbedPlayer extends ApiController
{
    /**
     * Should return an iframe-embeddable media player appropriate to the type of Resource.
     *
     * @ApiDoc(
     *      resource=true,
     *      description="Embed media player."
     * );
     *
     * @param string $id The id of the Resource to view.
     */
    public function executeAction($id)
    {
        throw $this->createHttpException(501);
    }

}
