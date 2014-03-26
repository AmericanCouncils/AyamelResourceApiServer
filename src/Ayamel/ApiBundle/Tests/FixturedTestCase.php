<?php

namespace Ayamel\ApiBundle\Tests;

use AC\WebServicesBundle\TestCase;

class FixturedTestCase extends TestCase
{
    protected $user;
    public function setUp()
    {
        parent::setUp();

        // Do I need to worry about authentication here?
        
        // $container = $this->getClient()->getContainer();
        // $doc = $container->get('doctrine_mongodb');
        // $repo = $doc->getRepository('ACFlagshipBundle:User');
        // $user = $repo->findOneBy(['email' => 'queenie16@yahoo.com']);
        // if (empty($user)) {
        //     throw new \Exception("couldn't find a user to log in as");
        // }
        // $this->user = $user;
    }
    protected function getFixtureClass()
    {
        return 'Ayamel\ApiBundle\Tests\AyamelFixture';
    }
}
