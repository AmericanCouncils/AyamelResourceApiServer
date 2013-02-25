<?php

namespace Ayamel\ApiBundle\Controller\V1;

use Ayamel\ApiBundle\Controller\ApiController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Resource Ratings controller.
 *
 * @package AyamelApiBundle
 * @author Evan Villemez
 */
class RatingsController extends ApiController
{

    /**
     * This will add a rating for the Resource.  Ratings are crowd sourced, and affect how
     * Resource objects are ranked in search, depending on the search criteria, and potentially
     * data about the user searching.
     *
     * **We have yet to decide what a *rating* entails... this needs to be figured out ASAP.**
     *
     * Note that Resources will not be added into the search index until they have at least
     * one Rating.
     *
     * @ApiDoc(
     *      resource=true,
     *      return="Ayamel\ResourceBundle\Document\Rating",
     *      description="Add a Resource Rating"
     * )
     *
     * @param  string $id The id the of the Resource
     * @return array
     */
    public function addResourceRating($id)
    {
        throw $this->createHttpException(501, sprintf("Not yet implemented [%s]", __METHOD__));
    }

}
