<?php

namespace Ayamel\ResourceApiBundle\Command;

use Symfony\Component\Console\Command;

class CleanFilesystem extends Command {
	
    protected function configure() {
        //TODO: set up
    }
    
	protected function execute() {
		//TODO: load file system, recursively scan all dirs starting from root, if a file cannot be found in the db, remove it
	}
	
}