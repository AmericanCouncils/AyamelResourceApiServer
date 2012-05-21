<?php

namespace Ayamel\FilesystemBundle\Command;

use Ayamel\FilesystemBundle\Filesystem\FilesystemInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CountFilesystem extends ContainerAwareCommand {
	
    protected function configure() {
        $this
            ->setName('fs:count')
            ->setDescription('Return count of files and directories managed locally by the Ayamel API.');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
		$fs = $this->getContainer()->get('ayamel.api.filesystem');
        $counts = $fs->getCount(FilesystemInterface::COUNT_BOTH);
        
        $output->writeln("Filesystem stats:");
        $output->writeln(sprintf("Files: ", $counts['files']));
        $output->writeln(sprintf("Directories: ", $counts['directories']));
	}

}
