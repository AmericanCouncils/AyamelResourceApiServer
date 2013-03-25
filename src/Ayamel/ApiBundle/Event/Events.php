<?php

namespace Ayamel\ApiBundle\Event;

/**
 * Defines core events that occur within the Resource API.
 *
 * @package AyamelApiBundle
 * @author Evan Villemez
 */
final class Events
{
    /**
     * Event fires when a new resource is created via the api
     *
     * Listeners receive an instance of `ApiEvent`
     */
    const RESOURCE_CREATED = "ayamel.api.resource_created";

    /**
     * Fires when a resource is modified via the api
     *
     * Listeners receive an instance of `ApiEvent`
     */
    const RESOURCE_MODIFIED = "ayamel.api.resource_modified";

    /**
     * Fires when a resource is deleted via the api
     *
     * Listeners receive an instance of `ApiEvent`
     */
    const RESOURCE_DELETED = "ayamel.api.resource_deleted";

//TODO: implement relation events

    /**
     * Fires when uploaded content needs to be resolved for a resource.
     *
     * Listeners receive an instance of `ResolveUploadedContentEvent`
     */
    const RESOLVE_UPLOADED_CONTENT = "ayamel.api.resolve_uploaded_content";

    /**
     * Fires after content has been resolved, and uploaded content needs to be properly handled.
     *
     * Listeners receive an instance of `HandleUploadedContentEvent`
     */
    const HANDLE_UPLOADED_CONTENT = "ayamel.api.handle_uploaded_content";

    /**
     * Fires when content should be permanentaly removed for a given resource.
     *
     * Listeners receive an instance of `ApiEvent`
     */
    const REMOVE_RESOURCE_CONTENT = "ayamel.api.remove_resource_content";

}
