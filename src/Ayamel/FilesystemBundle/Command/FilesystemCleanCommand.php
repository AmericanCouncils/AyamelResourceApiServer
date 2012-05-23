<?php

namespace Ayamel\FilesystemBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FilesystemCleanCommand extends ContainerAwareCommand {
	
    protected function configure() {
        $this
            ->setName('fs:clean')
            ->setDescription('Remove files in the filesystem for which no records exist in Resource storage.');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        throw new \RuntimeException("Not yet implemented.");
		//TODO: load file system, recursively scan all dirs starting from root, if a file cannot be found in the db, remove it
	}

}