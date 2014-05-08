<?php

namespace Ayamel\ApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ApiBundle\Event\Events;
use Ayamel\ApiBundle\Event\ResourceEvent;

/**
 * Delete a Resource by ID.
 *
 * @package AyamelApiBundle
 * @author Evan Villemez
 */
class ResourceDeleteCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('resource:delete')
            ->setDescription("Delete a specific Resource.")
            ->addArgument('id', InputArgument::REQUIRED, "ID of Resource to delete.")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = $this->getContainer()->get('doctrine_mongodb')->getManager();
        $dispatcher = $this->getContainer()->get('event_dispatcher');

        $resource = $manager->getRepository('AyamelResourceBundle:Resource')->find($input->getArgument('id'));

        if (!$resource) {
            throw new \InvalidArgumentException("Requested Resource not found.");
        }

        //delete relations for resource
        $relations = $manager->getRepository('AyamelResourceBundle:Relation')->getRelationsForResource($resource->getId());
        foreach ($relations as $relation) {
            $manager->remove($relation);
        }

        //remove from storage (sort of), just clears data and marks as deleted
        $resource = $manager->getRepository('AyamelResourceBundle:Resource')->deleteResource($resource);

        //make a copy of the relations that are about to deleted
        $r = clone $relations;
        $r = $r->toArray();
        $manager->flush();

        //set relations on deleted resource, to pass around to subsystems
        $resource->setRelations($r);

        //notify rest of system of deleted resource
        $dispatcher->dispatch(Events::RESOURCE_DELETED, new ResourceEvent($resource));

        $output->writeln(sprintf("Deleted Resource %s and %s Relations.", $resource->getId(), count($r)));

        return;
    }
}
