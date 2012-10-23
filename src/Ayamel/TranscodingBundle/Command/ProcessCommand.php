<?php

namespace Ayamel\TranscodingBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AC\TranscodingBundle\OutputSubscriber;

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
			->setWhatever(....);
	}
	
	protected function execute()
	{
        //TODO: flag for immediate transcode, or asyncronous transcode via RabbitMQ
        
        if ($forced) {
            //Inject CLI listener into transcoder
            $outputSubscriber = new OutputSubscriber;
            $outputSubscriber->setOutput($output);
            $outputSubscriber->setHelperSet($this->getHelperSet());
            $this->getContainer()->get('transcoder')->getDispatcher()->addSubscriber($outputSubscriber);
        
            //run transcode for Resource immediately
            $this->getContainer()->get('ayamel.transcoding.manager')->transcodeFilesForResource($id);
            
        } else {
            //schedule rabbit job
            
            
            $output->writeln(sprintf("Transcode job for Resource %s scheduled.", $id));
        }
        
        return;
	}
	
}
