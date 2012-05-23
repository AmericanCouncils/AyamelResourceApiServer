<?php

namespace Ayamel\FilesystemBundle\Event;

//TODO: consider an event for resolving file reference attributes

/**
 * Defines core events that occur within the FilesystemManager instance.
 *
 * @author Evan Villemez
 */
final class Events {
    
    /**
     * Event fires when a specific reference is being retrieved.
     *
     * Listeners receive an instance of `FilesystemEvent`
     */
    const FILESYSTEM_RETRIEVE = "ayamel.filesystem.retrieve_reference";
	
    /**
     * Event fires when a new FileReference is added into a filesystem.
     *
     * Listeners receive an instance of `FilesystemEvent`
     */
	const FILESYSTEM_PRE_ADD = "ayamel.filesystem.pre_reference_added";
    
    /**
     * Event fires when a new FileReference is added into a filesystem.
     *
     * Listeners receive an instance of `FilesystemEvent`
     */
	const FILESYSTEM_POST_ADD = "ayamel.filesystem.post_reference_added";
	
    /**
     * Event fires when a new FileReference is added into a filesystem.
     *
     * Listeners receive an instance of `FilesystemEvent`
     */
	const FILESYSTEM_PRE_DELETE = "ayamel.filesystem.pre_reference_delete";
	
    /**
     * Event fires when a new FileReference is added into a filesystem.
     *
     * Listeners receive an instance of `FilesystemEvent`
     */
	const FILESYSTEM_POST_DELETE = "ayamel.filesystem.post_reference_delete";
}
