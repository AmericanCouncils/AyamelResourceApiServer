<?php

namespace Ayamel\FilesystemBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ayamel\FilesystemBundle\Filesystem\FilesystemManager;
use Ayamel\FilesystemBundle\Filesystem\LocalFilesystem;

/**
 * If the filesystem is local, checks database for local files and removes unreferenced files.
 *
 * @author Evan Villemez
 */
class FilesystemPurgeFilesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('fs:purge:files')
            ->setDescription('Remove files for deleted Resources.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //check for local filesystem
        $c = $this->getContainer();
        $fs = $c->get('ayamel.api.filesystem');
        $manager = $c->get('doctrine_mongodb')->getManager();
        $mongo = $manager->getConnection();

        $collection = $mongo->selectCollection($c->getParameter('mongodb_database'), "resources");

        //find deleted
        $results = $collection->find(['status' => 'deleted'], ['_id' => 1]);
        $ids = array_keys(iterator_to_array($results));
        
        //try to remove files
        $removed = 0;
        $failed = [];
        foreach ($ids as $id) {
            foreach ($fs->getFilesForId($id) as $file) {
                try {
                    if ($fs->removeFile($file)) {
                        $removed++;
                    }
                } catch (\Exception $e) {
                    $failed[] = $e;
                }
            }
        }

        $output->writeln(sprintf("Removed %s files.", $removed));

        //failure output
        if (count($failed) > 0) {
            $output->writeln(sprintf("Failed to remove %s files", count($failed)));
        }
        if ($input->getOption('verbose')) {
            foreach ($failed as $failure) {
                $output->writeln($failure->getMessage());
            }
        }

        return;
    }

}
