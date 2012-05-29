<?php

namespace AC\GetID3Bundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ayamel\FilesystemBundle\Filesystem\FilesystemInterface;
use Symfony\Component\Yaml\Dumper;

/**
 * Uses getid3 to analyze a file from the command line.
 *
 * @author Evan Villemez
 */
class AnalyzeCommand extends ContainerAwareCommand {
	
    protected function configure() {
        $this
            ->setName('getid3:analyze')
            ->setDescription('Return stats on a file by using the getid3 library to analyze the file.')
            ->setDefinition(array(
                new InputArgument('path', InputArgument::REQUIRED, 'Path to file to analyze with getid3.'),
                new InputArgument('filter', InputArgument::OPTIONAL, 'Optionally specify a subset of the data to return using dot syntax.', false),
                new InputOption('expand', null, InputOption::VALUE_REQUIRED, 'Optionally specify number of layers to visually expand in the output, if the output is YAML.', 10),
                new InputOption('format', null, InputOption::VALUE_REQUIRED, 'Optionally specify alternate output format (json or yaml).', 'yaml')
            ))
		;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filePath = $input->getArgument('path');

        //use getid3 to analyze, and time results
		$g = new \getID3;
        $__start = microtime(true);
		$stats = $g->analyze($filePath);
        $time = round(((microtime(true) - $__start) * 1000), 4);

        if(!$stats) {
            throw new \RuntimeException(sprintf("Could not analyze file %s", $filePath));
        }

        //check for a filter
        if($filter = $input->getArgument('filter')) {
            $exp = explode(".", $filter);
            $stats = $this->filterResults($stats, $exp);
            if(!$stats) {
                throw new \InvalidArgumentException(sprintf("Requested key [%s] was not found.", $filter));
            }
        }
        
        //convert to requested format
        $format = $input->getOption('format');
        switch($format) {
            case 'json' : {
                $out = json_encode($stats);
            } break;
            
            case 'yaml' : {
                $numexpand = $input->getOption('expand');
                $dumper = new Dumper();
                $out = $dumper->dump($stats, $numexpand);
            
            } break;
            
            default : throw new \InvalidArgumentException("Format must be either 'yaml' or 'json'.");
        }
        
        //print results
        $title = sprintf("Getid3 analyzed file [<info>%s</info>] in [<info>%s</info>] ms", $filePath, $time);
        $title .= ($filter) ? sprintf(", showing key [<info>%s</info>]: ", $filter) : ": ";
        $output->writeln($title);
        foreach(explode("\n", $out) as $line) {
            $output->writeln($line);
        }
	}
    
    /**
     * Return subset of data array based on array of key filters
     *
     * @param string $arr 
     * @param array $filter 
     * @return mixed
     */
    protected function filterResults($arr, array $filter) {
        if(isset($arr[$filter[0]])) {
            if(count($filter) === 1) {
                return $arr[$filter[0]];
            } else {
                $subset = $arr[$filter[0]];
                array_shift($filter);
                return $this->filterResults($subset, $filter);
            }
        }
        
        return false;
    }

}
