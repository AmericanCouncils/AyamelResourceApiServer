<?php

namespace Ayamel\TranscodingBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use AC\TranscodingBundle\Console\OutputSubscriber;

/**
 * Run transcode jobs for a Resource by ID.
 *
 * @package AyamelTranscodingBundle
 * @author Evan Villemez
 */
class ProcessCommand extends ContainerAwareCommand
{
	
	protected function configure()
	{
		$this->setName('api:transcode:resource')
			->setDescription("Transcode files for a given Resource ID.")
            ->addArgument('id', InputArgument::REQUIRED, "ID of Resource to transcode.")
            ->addOption('force','-f', InputOption::VALUE_NONE, "If forced, the transcode will happen immediately, rather than asynchronously.");
	}
	
	protected function execute(InputInterface $input, OutputInterface $output)
	{
        if ($input->getOption('force')) {
            //Inject CLI listener into transcoder
            $outputSubscriber = new OutputSubscriber;
            $outputSubscriber->setOutput($output);
            $outputSubscriber->setHelperSet($this->getHelperSet());
            $this->getContainer()->get('transcoder')->getDispatcher()->addSubscriber($outputSubscriber);
        
            //run transcode for Resource immediately
            $this->getContainer()->get('ayamel.transcoding.manager')->transcodeResource($input->getArgument('id'));
            
        } else {
            //otherwise publish message via RabbitMQ
            $this->getContainer()->get('ayamel.transcoding.publisher')->publish(serialize(array(
                'id' => $input->getArgument('id')
            )));
            
            $output->writeln(sprintf("Transcode job for Resource %s scheduled.", $id));
        }
        
        return;
	}
}
