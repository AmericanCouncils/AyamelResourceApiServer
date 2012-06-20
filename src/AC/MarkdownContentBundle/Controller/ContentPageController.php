<?php

namespace AC\MarkdownContentBundle\Controller;

class ContentPageController extends Controller {

    /**
     * View a page, given a path
     *
     * @param string $path 
     * @return Response
     */
	public function viewPage($path) {
		
        
        return Response(200, "Hi");
	}

}