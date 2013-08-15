<?php

namespace Ayamel\ApiBundle\Event;

use Ayamel\ResourceBundle\Document\Resource;
use Symfony\Component\HttpFoundation\Request;

/**
 * This event fires when uploaded content is being resolved from an incoming request.  It is done via an event because
 * there are several different types of content, each of which is handled in a different way.  Listeners for this event
 * should parse the incoming request to determine which type of content is incoming, and set the parsed content data accordingly.
 *
 * The parsed content data will be dispatched via a HandleUploadedContentEvent instance to listeners which will deal with the uploaded
 * content accordingly.
 *
 * @author Evan Villemez
 */
class ResolveUploadedContentEvent extends ResourceEvent
{
    protected $request;

    protected $resource;

    protected $type = false;

    protected $content = false;

    protected $json_body = false;

    protected $post_body = false;

    protected $remove_previous_content = false;

    /**
     * Constructor requires the Resource which is being modified, and the incoming Http Request, which should
     * contain content to be processed for the resource.
     *
     * @param Resource $resource
     * @param Request  $request
     */
    public function __construct(Resource $resource, Request $request, $removePrevious = false)
    {
        parent::__construct($resource);
        $this->request = $request;
        $this->remove_previous_content = $removePrevious;

        //figure out the request body format, try json first
        $body = $request->getContent();
        if ($data = @json_decode($body, true)) {
            $this->json_body = $data;
        } else {
            //otherwise attempt to parse as a query string (aka post fields)
            parse_str($body, $data);

            if (empty($data) || !is_array($data)) {
                $this->post_body = $data;
            }
        }
    }

    /**
     * Set the type of content, should be a string.  This gets passed to the HandleUploadedContentEvent
     * in order to give listeners an easier way to determine which type of content is being handled.
     *
     * @param string $type
     */
    public function setContentType($type)
    {
        $this->type = $type;
    }

    /**
     * Get the type of content to be handled.
     *
     * @return string, or false if not set
     */
    public function getContentType()
    {
        return $this->type;
    }

    /**
     * Set the parsed content data.  In order to set the content, you must first set the content
     * type via "setContentType()".  Setting the content data will stop propagation to other listeners.
     *
     * @param  mixed $data
     * @return void
     */
    public function setContentData($data)
    {
        $this->content = $data;

        if (!$this->type) {
            throw new \RuntimeException("Cannot set content data without first setting the content type.");
        }

        $this->stopPropagation();
    }

    /**
     * Get the actual content to be handled.
     *
     * @return mixed, false if not set
     */
    public function getContentData()
    {
        return $this->content;
    }

    /**
     * Return whether or not previous content should be removed for this resource
     *
     * @return boolean - default is false
     */
    public function getRemovePreviousContent()
    {
        return $this->remove_previous_content;
    }

    /**
     * Get the raw http request from which to derive any uploaded content content.
     *
     * @return Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the JSON structure of the content request, if present, returns false if not present.
     *
     * @return array or false
     */
    public function getJsonBody()
    {
        return $this->json_body;
    }

    /**
     * Get the array of post fields associated with the request body.  Returns false if not set.
     *
     * @return array or false
     */
    public function getPostBody()
    {
        return $this->post_body;
    }

    /**
     * Get the request body data in array format, regardless of what format in came in originally (json or post fields)
     *
     * @return array or false
     */
    public function getRequestBody()
    {
        if ($this->getJsonBody()) {
            return $this->getJsonBody();
        }

        if ($this->getPostBody()) {
            return $this->getPostBody();
        }

        return false;
    }

}
