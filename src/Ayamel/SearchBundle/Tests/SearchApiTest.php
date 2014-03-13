<?php

namespace Ayamel\SearchBundle\Tests;

use Ayamel\SearchBundle\AsynchronousSearchTest;
use Guzzle\Http\Client;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Ayamel\ApiBundle\Tests\FixturedTestCase;

/**
 * This set of tests makes sure the API search routes perform as expected.  Most importantly
 * they need to strip unauthoried Resources from the returned results.
 *
 * @package AyamelSearchBundle
 * @author Evan Villemez
 */
class SearchApiTest extends FixturedTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testFixtures()
    {
        // check that fixtures are present
        $this->assertTrue(!empty($this->fixtureData));
        $id = $this->fixtureData['ACFlagshipBundle:Resource'][0]->getId();
        $content = $this->callJsonApi('GET', "/api/v1/resources/$id?_key=45678isafgd56789asfgdhf4567", ['auth' => [ 'user' => $this->user ]]);
        print_r($content);
        $this->assertArrayHasKey('resource', $content); 
    }

    /**
     * @depends testFixtures
     */
    public function testSimpleSearchApi($ids)
    {
        $this->markTestIncomplete();
        $client = new Client('http://127.0.0.1:9200');
        $response = $client->get('/ayamel/resource/')->send();
        var_dump($response->getBody());
        return;
        
        $proc = $this->startRabbitListener(3);
        $tester = $this;
        $proc->setTimeout(5);
        $b = [];
        $proc->wait(function($type, $buffer) use ($tester, $proc) {
            $b[] = $buffer;
            while ($proc->isRunning()) {
                usleep(50000); //wait a tiny bit to make sure the process actually quit (... meh)
            }

            if (!$proc->isSuccessful()) {
                throw new \RuntimeException($proc->getErrorOutput());
            }

            $response = $tester->getJson('GET', '/api/v1/resources/search?q=House');
            $code = $response['response']['code'];
            $tester->assertSame(200, $code);
            print_r($response);
            $tester->assertFalse(empty($response['results']['_results']));
        });
    }

    /**
     * @depends testFixtures
     */
    public function testSimpleSearchApiHidesUnauthorizedResources($ids)
    {
        $this->markTestSkipped();
    }

    /**
     * @depends testFixtures
     */
    public function testAdvancedSearchApi($ids)
    {
        $this->markTestSkipped();
    }

    /**
     * @depends testFixtures
     */
    public function testAdvancedSearchApiHidesUnauthorizedResources($ids)
    {
        $this->markTestSkipped();
    }
}


            //object should not be in the index
            // try {
            //     $response = $client->get('/ayamel/resource/'.$relation['objectId'])->send();
            // } catch (ClientErrorResponseException $exception) {
            // }
            // $this->assertSame(404, $exception->getResponse()->getStatusCode());

        // shouldn't need any of this with fixtures, in theory, although I might have to index the database
        
        // $this->clearDatabase();
        // $container = $this->getContainer();
        // // clear rabbit queue
        // try {
        //     $container->get('old_sound_rabbit_mq.search_index_producer')->getChannel()->queue_purge('search_index');
        // } catch (\PhpAmqpLib\Exception\AMQPProtocolChannelException $e) {
        //     //swallow this error because of travis
        // }

        // $uploadUrls = [];
        // $titles = ['The Russia House','The Sealand House','The Maxwell House'];

        // foreach ($titles as $title) {
        //     $response = $this->getJson('POST', '/api/v1/resources?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
        //         'CONTENT_TYPE' => 'application/json'
        //     ), json_encode(array(
        //         'title' => $title,
        //         'type' => 'document',
        //     )));

        //     $uploadUrls[] = substr($response['contentUploadUrl'], strlen('http://localhost'));
        // }
        // foreach ($uploadUrls as $uploadUrl) {
        //     $content = $this->getJson('POST', $uploadUrl.'?_key=45678isafgd56789asfgdhf4567', array(), array(), array(
        //         'CONTENT_TYPE' => 'application/json'
        //     ), json_encode(array(
        //         'uri' => 'http://www.google.com/'
        //     )));
        // }
