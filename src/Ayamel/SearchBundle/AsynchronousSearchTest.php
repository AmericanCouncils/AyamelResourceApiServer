<?php

namespace Ayamel\SearchBundle;

use Ayamel\ApiBundle\ApiTestCase;
use Symfony\Component\Process\Process;
use Guzzle\Http\Client;
use Guzzle\Http\Exception\ClientErrorResponseException;

/**
 * This test ensures that the indexer is invoked via RabbitMQ when
 * certain API events are fired.
 *
 * @package AyamelSearchBundle
 * @author Evan Villemez
 */
class AsynchronousSearchTest extends ApiTestCase
{
    protected function startRabbitListener($numMessages = 1)
    {
        $container = $this->getContainer();

        //clear rabbitmq message queue
        try {
            $container->get('old_sound_rabbit_mq.search_index_producer')->getChannel()->queue_purge('search_index');
        } catch (\PhpAmqpLib\Exception\AMQPProtocolChannelException $e) {
            //swallow this error because of travis
        }

        //start index listener
        $consolePath = $container->getParameter('kernel.root_dir').DIRECTORY_SEPARATOR."console";
        $rabbitProcess = new Process(sprintf('%s --env=test rabbitmq:consumer search_index --messages='.$numMessages.' --vvv', $consolePath));
        $rabbitProcess->start();

        usleep(500000); //wait half a second, check to make sure process is still up
        if (!$rabbitProcess->isRunning()) {
            throw new \RuntimeException(($rabbitProcess->isSuccessful()) ? $rabbitProcess->getOutput() : $rabbitProcess->getErrorOutput());
        }

        return $rabbitProcess;
    }
}
