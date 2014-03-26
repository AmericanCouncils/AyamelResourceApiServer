<?php

namespace AC\Ayamel\SearchBundle\Tests\Command;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use FOS\ElasticaBundle\Command\PopulateCommand;
use AC\WebServicesBundle\TestCase;
use Ayamel\ApiBundle\Tests\FixturedTestCase;


class PopulateCommandTest extends FixturedTestCase
{

    public function setUp()
    {
        parent::setUp();
        $k = $this->createKernel();
        $k->boot();
        $app = new Application($k);
        $app->setAutoExit(false);
        $app->add(new PopulateCommand());
        $this->command = $app->find('fos:elastica:populate');
        $this->commandTester = new CommandTester($this->command);
    }

    public function testCommand()
    {
        $this->commandTester->execute(['command' => $this->command->getName()]);
        $this->assertRegExp('/Populating ayamel\/resource, Finished indexing resources./', $this->commandTester->getDisplay());
    }
}
