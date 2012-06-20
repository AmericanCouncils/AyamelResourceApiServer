<?php

namespace AC\MarkdownContentBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Uses page loader to recache all pages in their current state.
 *
 * @author Evan Villemez
 */
class RebuildCacheCommand extends ContainerAwareCommand {
	
    protected function configure() {
        $this
            ->setName('mdcontent:cache:rebuild')
            ->setDescription('Rebuilds cached version of all available pages.')
		;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //TODO
	}    

}
