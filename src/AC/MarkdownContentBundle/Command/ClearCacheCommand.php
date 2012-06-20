<?php

namespace AC\MarkdownContentBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Clears markdown page content caches.
 *
 * @author Evan Villemez
 */
class ClearCacheCommand extends ContainerAwareCommand {
	
    protected function configure() {
        $this
            ->setName('mdcontent:cache:clear')
            ->setDescription('Clears cached versions of all available pages.')
		;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //$this->getContainer()->get('mdcontent.cache')->clear();
	}    

}
