<?php

namespace Ayamel\ApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
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
        $manager = $this->get('doctrine_mongodb')->getManager();
        
        $resource = $manager->getRepository('AyamelResourceBundle:Resource')->find($input->getArgument('id'));
        
        if (!$resource) {
            throw new \InvalidArgumentException("Requested Resource not found.");
        }
        
        $resource->setStatus($input->getArgument('status'));
        $manager->flush();

        $output->writeln("Status for Resource changed.");

        return;
    }
}
