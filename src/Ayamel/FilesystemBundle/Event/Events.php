<?php

namespace Ayamel\FilesystemBundle\Event;

/**
 * Defines core events that occur within the FilesystemManager instance.
 *
 * @author Evan Villemez
 */
final class Events {
	
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
