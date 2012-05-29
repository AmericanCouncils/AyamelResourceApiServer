<?php

namespace AC\GetID3Bundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Ayamel\FilesystemBundle\Filesystem\FilesystemInterface;
use Symfony\Component\Yaml\Dumper;

class AnalyzeCommand extends ContainerAwareCommand {
	
    protected function configure() {
        $this
            ->setName('getid3:analyze')
            ->setDescription('Return stats on a file by using the getid3 library to analyze the file.')
            ->setDefinition(array(
                new InputArgument('path', InputArgument::REQUIRED, 'Absolute path to file to analyze with getid3.')
            ))
		;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filePath = $input->getArgument('path');
        //$stats = $this->getContainer()->get('getid3')->analyze($filePath);
		$g = new \getID3;
		$stats = $g->analyze($filePath);

        if(!$stats) {
            throw new \RuntimeException(sprintf("Could not analyze file %s", $filePath));
        }

        //convert to yaml
        $dumper = new Dumper();
        $yaml = $dumper->dump($stats);
        
        //print line by line
        //TODO: fix this
        foreach(explode("\n", $yaml) as $line) {
            $output->writeln($line);
        }
	}
}
