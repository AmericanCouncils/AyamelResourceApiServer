<?php

namespace Ayamel\ApiBundle\Tests;

class FilterRelationsTest extends FixturedTestCase
{
    public function testFilters()
    {
        $content = $this->callJsonApi('GET', '/api/v1/relations?_key=key-for-test-client-1');
        $this->assertSame(20, count($content['relations']));
        $this->assertSame(48, $content['total']);

        $content = $this->callJsonApi('GET', '/api/v1/relations?_key=key-for-test-client-1&limit=10&skip=10');
        $this->assertSame(10, count($content['relations']));
        $this->assertSame(48, $content['total']);
    }
}
