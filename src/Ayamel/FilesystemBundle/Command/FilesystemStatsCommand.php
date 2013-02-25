<?php

namespace Ayamel\FilesystemBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ayamel\FilesystemBundle\Filesystem\FilesystemInterface;
use Ayamel\FilesystemBundle\Filesystem\FilesystemManager;

class FilesystemStatsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('fs:stats')
            ->setDescription('Return count of files and directories managed locally by the Ayamel API, as well as filesystem specific stats (if any).');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = $this->getContainer()->get('ayamel.api.filesystem');
        $counts = $fs->getCount(FilesystemInterface::COUNT_BOTH);

        $output->writeln("Filesystem counts:");
        $output->writeln(sprintf("<comment>Files: <info>%s</info></comment>", $counts['files']));
        $output->writeln(sprintf("<comment>Directories: <info>%s</info></comment>", $counts['directories']));
        $class = ($fs instanceof FilesystemManager) ? get_class($fs->getFilesystem()) : get_class($fs);
        $output->writeln(sprintf("Filesystem stats (<info>%s</info>):", $class));
        foreach ($fs->getStats() as $key => $val) {
            $output->writeln(sprintf("<comment>%s: <info>%s</info></comment>", $key, $val));
        }
    }

}
