<?php

namespace Ayamel\ApiBundle;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

abstract class ApiTestCase extends WebTestCase
{
    public function clearDatabase()
    {
        $c = $this->getContainer();
        $dbName = $c->getParameter('mongodb_database');
        $c->get('doctrine_mongodb')->getConnection()->dropDatabase($dbName);
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
    protected function getResponse($method, $uri, $params = array(), $files = array(), $server = array(), $content = null, $changehistory = true)
    {
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
    protected function getJson($method, $uri, $params = array(), $files = array(), $server = array(), $content = null, $changehistory = true)
    {
        return json_decode($this->getResponse($method, $uri, $params, $files, $server, $content, $changehistory)->getContent(), true);
    }

}
