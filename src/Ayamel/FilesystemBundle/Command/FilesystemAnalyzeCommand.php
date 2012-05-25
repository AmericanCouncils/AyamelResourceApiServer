<?php

namespace Ayamel\FilesystemBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ayamel\FilesystemBundle\Analyzer\DelegatingAnalyzer;

use Ayamel\ResourceBundle\Document\FileReference;

class FilesystemAnalyzeCommand extends ContainerAwareCommand {
	
    protected function configure() {
        $this
            ->setName('fs:analyze')
            ->setDescription('Use registered analyzers to return a FileReference instance of a path, with attributes for the file.');
		//TODO: set mode arguments
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
		throw new \RuntimeException("Not yet implemented.");
		
		$analyzer = $this->getContainer()->get('ayamel.api.filesystem.analyzer');
		
		//special config for the delegator if that's what we are using
		if($analyzer instanceof DelegatingAnalyzer) {
			$analyzer->setTrustCache(false);
			$analyzer->setCacheResults(true);
			$analyzer->setAnalyzeRemoteFiles(true);
		}
		
		//analyze the file
		$ref = $analyzer->analyzeFile($ref);
		
		var_dump($ref->getAttributes());
	}

}
