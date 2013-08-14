<?php

namespace Ayamel\TranscodingBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use AC\TranscodingBundle\Console\OutputSubscriber;
use AC\Transcoding\Adapter\AbstractCliAdapter;

/**
 * Run transcode jobs for a Resource by ID.
 *
 * @package AyamelTranscodingBundle
 * @author Evan Villemez
 */
class TranscodeResourceCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('api:resource:transcode')
            ->setDescription("Transcode files for a given Resource ID.")
            ->addArgument('id', InputArgument::REQUIRED, "ID of Resource to transcode.")
            ->addOption('force','-f', InputOption::VALUE_NONE, "If forced, the transcode will happen immediately, rather than asynchronously.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');

        if ($input->getOption('force')) {
            $transcoder = $this->getContainer()->get('transcoder');

            //Inject CLI listener into transcoder
            $outputSubscriber = new OutputSubscriber;
            $outputSubscriber->setOutput($output);
            $outputSubscriber->setHelperSet($this->getHelperSet());
            $transcoder->getDispatcher()->addSubscriber($outputSubscriber);

            //check for verbose
            if ($input->getOption('verbose')) {
                foreach ($transcoder->getAdapters() as $adapter) {
                    if ($adapter instanceof AbstractCliAdapter) {
                        $adapter->setStreamBuffer(true);
                    }
                }
            }

            //run transcode for Resource immediately
            $resource = $this->getContainer()->get('ayamel.transcoding.manager')->transcodeResource($id);

        } else {
            //otherwise publish message via RabbitMQ
            $this->getContainer()->get('old_sound_rabbit_mq.transcoding_producer')->publish(serialize(array(
                'id' => $id
            )));

            $output->writeln(sprintf("Transcode job for Resource %s scheduled.", $id));
        }

        return;
    }
}
