<?php

namespace Ayamel\ResourceBundle\Event;

final class Events {
	
    /**
     * Fires before an object is requested from storage.
     */
	const PRE_RETRIEVE = "ayamel.resource.pre_retrieve";
    
    /**
     * Fires just after storage returns an object.
     */
	const POST_RETRIEVE = "ayamel.resource.post_retrieve";

    /**
     * Fires before a Resource object is persisted to underlying storage.
     */
	const PRE_PERSIST = "ayamel.resource.pre_persist";

    /**
     * Fires just after a Resource object is persisted to storage.
     */
	const POST_PERSIST = "ayamel.resource.post_persist";

    /**
     * Fires just before a Resource object is deleted, or marked for deletion.
     */
	const PRE_DELETE = "ayamel.resource.pre_delete";
    
    /**
     * Fires just after a Resource object has been deleted, or marked for deletion.
     */
	const POST_DELETE = "ayamel.resource.post_delete";
	
}