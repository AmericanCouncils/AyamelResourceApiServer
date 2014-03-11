<?php

namespace Ayamel\ApiBundle;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

abstract class ApiTestCase extends WebTestCase
{
    public function clearDatabase()
    {
        $c = $this->getContainer();
        $db = $c->get('doctrine_mongodb.odm.default_connection')->selectDatabase($c->getParameter('mongodb_database'));
        $db->dropCollection('resources');
        $db->dropCollection('relations');
    }

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
        $rabbitProcess = new Process(sprintf('%s --env=test rabbitmq:consumer search_index --messages='.$numMessages.' --verbose', $consolePath));
        $rabbitProcess->start();
        usleep(500000); //wait half a second, check to make sure process is still up
        if (!$rabbitProcess->isRunning()) {
            throw new \RuntimeException(($rabbitProcess->isSuccessful()) ? $rabbitProcess->getOutput() : $rabbitProcess->getErrorOutput());
        }

        return $rabbitProcess;
    }

    /**
     * Shortcut to get client
     */
    protected function getClient()
    {
        $client = static::createClient(array(
            'environment' => 'test',
            'debug' => true
        ));

        return $client;
    }

    /**
     * Shortcut to run a CLI command - returns a... ?
     */
    protected function runCommand($string)
    {
        $command = sprintf('%s --quiet --env=test', $string);
        $k = $this->createKernel();
        $app = new Application($k);
        $app->setAutoExit(false);

        return $app->run(new StringInput($string), new NullOutput());
    }

    /**
     * Shortcut to get the Container
     */
    protected function getContainer()
    {
        $k = $this->createKernel();
        $k->boot();

        return $k->getContainer();
    }

    /**
     * Shortcut to make a request and get the returned Response instance.
     */
    public function getResponse($method, $uri, $params = array(), $files = array(), $server = array(), $content = null, $changehistory = true)
    {
        $server['SERVER_NAME'] = '127.0.0.1';
        $client = static::createClient(array(
            'environment' => 'test',
            'debug' => true
        ));

        $client->request($method, $uri, $params, $files, $server, $content, $changehistory);

        return $client->getResponse();
    }

    /**
     * Shortcut to make a request and get the json_decoded response content
     */
    public function getJson($method, $uri, $params = array(), $files = array(), $server = array(), $content = null, $changehistory = true)
    {
        return json_decode($this->getResponse($method, $uri, $params, $files, $server, $content, $changehistory)->getContent(), true);
    }

}
