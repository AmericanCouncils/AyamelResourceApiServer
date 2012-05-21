<?php

namespace Ayamel\FilesystemBundle\Command;

use Ayamel\FilesystemBundle\Filesystem\FilesystemInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdatePaths extends ContainerAwareCommand {
	
    protected function configure() {
        $this
            ->setName('fs:update:paths')
            ->setDescription('Return count of files and directories managed locally by the Ayamel API.');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        throw new \RuntimeException("Not yet implemented.");
	}

}
