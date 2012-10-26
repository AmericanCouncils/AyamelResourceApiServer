<?php

namespace Ayamel\ApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Ayamel\ResourceBundle\Document\Resource;

/**
 * Modify a Resource's status.
 *
 * @package AyamelApiBundle
 * @author Evan Villemez
 */
class ResourceStatusCommand extends ContainerAwareCommand
{
	
	protected function configure()
	{
		$this->setName('api:resource:status')
			->setDescription("Modify a specific Resource's status.")
            ->addArgument('id', InputArgument::REQUIRED, "ID of Resource to modify.")
            ->addArgument('status', InputArgument::REQUIRED, "New status of resource.");
	}
	
	protected function execute(InputInterface $input, OutputInterface $output)
	{
        $manager = $this->getContainer()->get('ayamel.resource.manager');
        $resource = $manager->getResourceById($input->getArgument('id'));
        $resource->setStatus($input->getArgument('status'));
        $manager->persistResource($resource);
        
        $output->writeln("Status for Resource changed.");
        
        return;
	}
}
