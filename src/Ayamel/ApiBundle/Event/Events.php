<?php

namespace Ayamel\ApiBundle\Event;

/**
 * Defines core events that occur within the Resource API.  Most API subsystems, such as
 * the filesystem, transcoding, and search, are implemented as listeners to these events.
 *
 * These events are generally fired from the controllers in the API, though there may be
 * some cases where Resources/Relations are modified outside of the API controllers.  When
 * this is the case, the appropriate events should be thrown as well.
 *
 * @package AyamelApiBundle
 * @author Evan Villemez
 */
final class Events
{
    /**
     * Event fires when a new resource is created via the api
     *
     * Listeners receive an instance of `ResourceEvent`
     */
    const RESOURCE_CREATED = 'ayamel.api.resource_created';

    /**
     * Fires when a resource is modified via the api
     *
     * Listeners receive an instance of `ResourceEvent`
     */
    const RESOURCE_MODIFIED = 'ayamel.api.resource_modified';

    /**
     * Fires when a resource is deleted via the api
     *
     * Listeners receive an instance of `ResourceEvent`
     */
    const RESOURCE_DELETED = 'ayamel.api.resource_deleted';

    /**
     * Fires when uploaded content needs to be resolved for a resource.
     *
     * Listeners receive an instance of `ResolveUploadedContentEvent`
     */
    const RESOLVE_UPLOADED_CONTENT = 'ayamel.api.resolve_uploaded_content';

    /**
     * Fires after content has been resolved, and uploaded content needs to be properly handled.
     *
     * Listeners receive an instance of `HandleUploadedContentEvent`
     */
    const HANDLE_UPLOADED_CONTENT = 'ayamel.api.handle_uploaded_content';

    /**
     * Fires when content should be permanentaly removed for a given resource.
     *
     * Listeners receive an instance of `ResourceEvent`
     */
    const REMOVE_RESOURCE_CONTENT = 'ayamel.api.remove_resource_content';

    /**
     * Fired when a Relation is created.
     *
     * Listeners receive an instance of `RelationEvent`
     */
    const RELATION_CREATED = 'ayamel.api.relation_created';

    /**
     * Fired when a Relation is deleted.
     *
     * WARNING: It is important to note that this event does NOT fire when a Relation
     * is deleted as the result of a Resource being deleted.  This only fires if a Relation
     * is deleted explicitly via the /relations/{id} api.
     *
     * Listeners receive an instance of `RelationEvent`
     */
    const RELATION_DELETED = 'ayamel.api.relation_deleted';

}
