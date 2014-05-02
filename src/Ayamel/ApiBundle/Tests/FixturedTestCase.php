<?php

namespace Ayamel\ApiBundle\Tests;

use AC\WebServicesBundle\TestCase;

class FixturedTestCase extends TestCase
{
    protected function getFixtureClass()
    {
        return 'Ayamel\ApiBundle\Tests\AyamelFixture';
    }
}
