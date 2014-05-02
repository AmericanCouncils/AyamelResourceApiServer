<?php

namespace Ayamel\SearchBundle;

use Ayamel\ApiBundle\ApiTestCase;
use Symfony\Component\Process\Process;
use Guzzle\Http\Client;

/**
 * This test ensures that the indexer is invoked via RabbitMQ when
 * certain API events are fired.
 *
 * @package AyamelSearchBundle
 * @author Evan Villemez
 */
abstract class SearchTest extends ApiTestCase
{
    protected $guzzleClient;
    protected $indexName;

    protected function setUpGuzzle()
    {
        $container = $this->getClient()->getContainer();
        $this->guzzleClient = new Client(implode([
            'http://',
            $container->getParameter('elasticsearch_host'),
            ":",
            $container->getParameter('elasticsearch_port')
        ]));

        $this->indexName = $container->getParameter('elasticsearch_index');
    }

    public function tearDown()
    {
        $this->guzzleClient = null;
        $this->indexName = null;
    }

    protected function startRabbitListener($numMessages = 1, $timeout = 5)
    {
        $container = $this->getContainer();
        $queueName = $container->getParameter('search_index_queue_name');
        //clear rabbitmq message queue
        try {
            $container->get('old_sound_rabbit_mq.search_index_producer')->getChannel()->queue_purge($queueName);
        } catch (\PhpAmqpLib\Exception\AMQPProtocolChannelException $e) {
            //swallow this error because of travis
        }

        //start index listener
        $consolePath = $container->getParameter('kernel.root_dir').DIRECTORY_SEPARATOR."console";
        $rabbitProcess = new Process(sprintf('%s rabbitmq:consumer search_index --messages='.$numMessages.' --env=test -vvv', $consolePath));
        $rabbitProcess->setTimeout($timeout);
        $rabbitProcess->start();

        usleep(500000); //wait half a second, check to make sure process is still up
        if (!$rabbitProcess->isRunning()) {
            throw new \RuntimeException(($rabbitProcess->isSuccessful()) ? $rabbitProcess->getOutput() : $rabbitProcess->getErrorOutput());
        }

        return $rabbitProcess;
    }
}
