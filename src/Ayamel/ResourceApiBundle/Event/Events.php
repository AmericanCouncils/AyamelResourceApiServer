<?php

namespace Ayamel\ResourceApiBundle\Event;

/**
 * Defines core events that occur within the Resource API.
 *
 * @author Evan Villemez
 */
final class Events {
	
    /**
     * Event fires when a new resource is created via the api
     */
	const RESOURCE_CREATED = "ayamel.api.resource_created";
    
    /**
     * Fires when a resource is modified via the api
     */
	const RESOURCE_MODIFIED = "ayamel.api.resource_modified";
    
    /**
     * Fires when a resource is deleted via the api
     */
	const RESOURCE_DELETED = "ayamel.api.resource_deleted";
    
    /**
     * Fires when content has been uploaded for a resource via the api
     */
	const CONTENT_UPLOADED = "ayamel.api.content_uploaded";
    
}