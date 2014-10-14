<?php

namespace Ayamel\ApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use JMS\Serializer\SerializationContext;

/**
 * Show a resource by ID in the console.
 *
 * @package AyamelApiBundle
 * @author Evan Villemez
 */
class ResourceShowCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('resource:show')
            ->setDescription("Modify a specific Resource's status.")
            ->addArgument('id', InputArgument::REQUIRED, "ID of Resource to modify.")
            ->addOption('with-relations', null, InputOption::VALUE_NONE, 'Include all relations?')
            ->addOption('show-nulls', null, InputOption::VALUE_NONE, 'Show empty fields?')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'What serializer format should be used?', 'yml')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $manager = $container->get('doctrine_mongodb')->getManager();
        $serializer = $container->get('serializer');
        $resourceId = $input->getArgument('id');
        $format = $input->getOption('format');
        
        $resource = $manager->getRepository('AyamelResourceBundle:Resource')->find($resourceId);
        
        if (!$resource) {
            throw new \RuntimeException(sprintf("No Resource found for id [%s]", $resourceId));
        }
        
        if ($input->getOption('with-relations')) {
            $relations = $manager->getRepository('AyamelResourceBundle:Relation')->getRelationsForResource($resourceId);
            $resource->setRelations(iterator_to_array($relations));
        }
        
        $context = new SerializationContext();
        if ($input->getOption('show-nulls')) {
            $context->setSerializeNull(true);
        }

        $output->writeln($serializer->serialize($resource, $format, $context));

        return;
    }
}
