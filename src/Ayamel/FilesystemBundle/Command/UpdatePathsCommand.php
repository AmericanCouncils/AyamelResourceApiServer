<?php

namespace Ayamel\FilesystemBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdatePathsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('fs:update:paths')
            ->setDescription('Update resource FileReference objects in database to point to a new local base path.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        throw new \RuntimeException("Not yet implemented.");
    }

}
