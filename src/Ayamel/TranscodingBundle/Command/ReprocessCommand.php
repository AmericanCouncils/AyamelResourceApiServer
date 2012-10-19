<?php

class ReprocessCommand
{
	
	protected function configure()
	{
		$this->setName('api:transcoder:reprocess')
			->setDescription("Reprocess transcoded files for a given Resource ID.")
			->setWhatever(....);
	}
	
	protected function execute()
	{
		//TODO: schedule job, delete previous
	}
	
}
