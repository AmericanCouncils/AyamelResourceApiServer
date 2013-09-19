<?php

namespace Ayamel\SearchBundle\Tests;

use Ayamel\ApiBundle\ApiTestCase;

/**
 * This test ensures that the indexer is invoked via RabbitMQ when
 * certain API events are fired.
 *
 * @package AyamelSearchBundle
 * @author Evan Villemez
 */
class AsynchronousSearchIndexerTest extends ApiTestCase
{
    public function testCreateResourceTriggersIndex()
    {
        $this->markTestSkipped();
    }
    
    public function testModifyResourceTriggersIndex()
    {
        $this->markTestSkipped();
    }
    
    public function testDeleteResourceTriggersIndex()
    {
        $this->markTestSkipped();
    }
    
    public function testCreateRelatedResourceTriggersIndex()
    {
        $this->markTestSkipped();
    }
    
    public function testDeleteRelatedResourceTriggersIndex()
    {
        $this->markTestSkipped();
    }
}
