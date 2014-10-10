<?php

namespace Ayamel\SearchBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Index a Resource for search by ID.
 */
class IndexResourceCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('resource:index')
            ->setDescription("Reindex a particular Resource for search by ID.")
            ->addArgument('id', InputArgument::REQUIRED, "ID of Resource to index.")
            ->addOption('force','-f', InputOption::VALUE_NONE, "If forced, the indexing will happen immediately, rather than asynchronously.")
            ->addOption('debug', null, InputOption::VALUE_NONE, "Dump the search document to the console, rather than actually indexing.")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');
        
        //just create the search doc, but don't do anything with it
        if ($input->getOption('debug')) {
            $indexer = $this->getContainer()->get('ayamel.search.resource_indexer');
            $searchDoc = $indexer->createResourceSearchDocumentForId($id);
            $output->writeln(var_export($searchDoc->toArray()));
            return;
        }

        if ($input->getOption('force')) {
            $this->getContainer()->get('ayamel.search.resource_indexer')->indexResource($id);
            $output->writeln("Indexed Resource $id");
        } else {
            //otherwise publish message via RabbitMQ
            $this->getContainer()->get('old_sound_rabbit_mq.search_index_producer')->publish(serialize(array(
                'id' => $id
            )));

            $output->writeln(sprintf("Search index job for Resource %s scheduled.", $id));
        }

        return;
    }
}
