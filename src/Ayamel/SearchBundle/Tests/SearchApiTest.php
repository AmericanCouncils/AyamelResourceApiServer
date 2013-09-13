<?php

namespace Ayamel\SearchBundle\Tests;

use Ayamel\ApiBundle\ApiTestCase;

class SearchApiTest extends ApiTestCase
{
    public function testCreatingResourcesAddsThemToIndexAsynronously()
    {
        $this->markTestSkipped();

        //create resources
        //query api - they should be visible
        //requires rabbitmq consumer
    }

    public function testSearchApiFiltersUnauthorizedResources()
    {
        $this->markTestSkipped();

        //make sure clients can't see things they shouldn't see
        //when using the search api
    }
}
