<?php

namespace Ayamel\FilesystemBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Ayamel\FilesystemBundle\Event\Events;
use Ayamel\FilesystemBundle\Event\FilesystemEvent;

/**
 * Listens for the filesystem events in order to populate FileReference attribute information
 * with any registered analyzers
 *
 * @author Evan Villemez
 */
class FilesystemAnalyzerSubscriber implements EventSubscriberInterface
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::FILESYSTEM_POST_ADD => 'onRetrieveFile',
            Events::FILESYSTEM_RETRIEVE => 'onRetrieveFile',
        );
    }

    /**
     * Use the analyzer to populate attribute information for a given file reference
     *
     * @param FilesystemEvent $e
     */
    public function onRetrieveFile(FilesystemEvent $e)
    {
        $this->container->get('ayamel.filesystem.analyzer')->analyzeFile($e->getFileReference());
    }

}
