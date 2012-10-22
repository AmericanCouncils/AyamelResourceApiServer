<?php

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
            $this->getContainer()->get('transcoder')->getDispatcher()->registerSubscriber($outputSubscriber);
        
            //run transcode for Resource
            $this->getContainer()->get('ayamel.transcoding.manager')->transcodeFilesForResource();
            
        } else {
            //schedule rabbit job
            
            
            $output->writeln(sprintf("Transcode job for Resource %s scheduled.", $id));
        }
        
        return;
	}
	
}
