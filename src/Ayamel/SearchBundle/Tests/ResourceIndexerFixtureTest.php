<?php

namespace Ayamel\SearchBundle\Tests;

use Ayamel\SearchBundle\ResourceIndexer;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use FOS\ElasticaBundle\Command\PopulateCommand;
use Ayamel\ApiBundle\Tests\FixturedTestCase;

class ResourceIndexerFixtureTest extends FixturedTestCase
{
    public function setUp()
    {
        parent::setUp();

    }

    public function testIndexDeletedResource()
    {
        $id = $this->fixtureData['AyamelResourceBundle:Resource'][0]->getId();
        $mongoId = new \MongoId($id);
        $manager = $this->getClient()->getContainer()->get('doctrine_mongodb')->getManager();
        $manager->getConnection()->initialize();
        $mongo = $manager->getConnection()->getMongo();
        $collection = $mongo->selectCollection("ayamel_test", "resources");
        $newdata = array('$set' => array("status" => "deleted"));
        $result = $collection->update(["_id" => $mongoId], $newdata);
        $indexer = $this->getClient()->getContainer()->get('ayamel.search.resource_indexer');
        $indexer->indexResource($id);
    }
}
