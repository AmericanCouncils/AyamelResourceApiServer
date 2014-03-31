<?php

namespace Ayamel\FilesystemBundle\Filesystem;

use Ayamel\ResourceBundle\Document\FileReference;
use Ayamel\FilesystemBundle\Event\FilesystemEvent;
use Ayamel\FilesystemBundle\Event\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * FilesystemManager wraps another FilesystemInterface instance and uses an EventDispatcer to dispatch filesystem related events.
 *
 * @author Evan Villemez
 */
class FilesystemManager implements FilesystemInterface
{
    /**
     * @var object - Ayamel\FilesystemBundle\Filesystem\FilesystemInterface
     */
    protected $fs;

    /**
     * @var object - Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * Constructor takes another FilesystemInterface to wrap, and an EventDispatcher for events
     *
     * @param FilesystemInterface      $fs
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(FilesystemInterface $fs, EventDispatcherInterface $dispatcher)
    {
        $this->fs = $fs;
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function generateBaseDirectoryForId($id)
    {
        return $this->fs->generateBaseDirectoryForId($id);
    }

    /**
     * {@inheritdoc}
     */
    public function generateBasePathForId($id)
    {
        return $this->fs->generateBasePathForId($id);
    }

    /**
     * {@inheritdoc}
     */
    public function generateBaseFilenameForId($id)
    {
        return $this->fs->generateBaseFilenameForId($id);
    }

    /**
     * {@inheritdoc}
     */
    public function removeFile(FileReference $ref)
    {
        $id = $this->getIdForFile($ref);
        $this->dispatcher->dispatch(Events::FILESYSTEM_PRE_DELETE, new FilesystemEvent($this->fs, $ref, $id));
        if (!$this->fs->removeFile($ref)) {
            return false;
        }
        $this->dispatcher->dispatch(Events::FILESYSTEM_POST_DELETE, new FilesystemEvent($this->fs, $ref, $id));

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdForFile(FileReference $ref)
    {
        return $this->fs->getIdForFile($ref);
    }

    /**
     * {@inheritdoc}
     */
    public function removeFileForId($id, $name)
    {
        $ref = $this->getFileForId($id, $name);
        $this->dispatcher->dispatch(Events::FILESYSTEM_PRE_DELETE, new FilesystemEvent($this->fs, $ref, $id));
        $return = $this->fs->removeFileForId($id, $name);
        $this->dispatcher->dispatch(Events::FILESYSTEM_POST_DELETE, new FilesystemEvent($this->fs, $ref, $id));

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function removeFilesForId($id)
    {
        foreach ($this->getFilesForId($id) as $ref) {
            $this->removeFile($ref);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFileForId($id, $name)
    {
        $ref = $this->fs->getFileForId($id, $name);
        $e = $this->dispatcher->dispatch(Events::FILESYSTEM_RETRIEVE, new FilesystemEvent($this->fs, $ref, $id));

        return $e->getFileReference();
    }

    /**
     * {@inheritdoc}
     */
    public function getFilesForId($id)
    {
        $returned = $this->fs->getFilesForId($id);
        $processed = array();
        foreach ($returned as $ref) {
            $processed[] = $this->dispatcher->dispatch(Events::FILESYSTEM_RETRIEVE, new FilesystemEvent($this->fs, $ref, $id))->getFileReference();
        }

        return $processed;
    }

    /**
     * {@inheritdoc}
     */
    public function hasFileForId($id, $name)
    {
        return $this->fs->hasFileForId($id, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function addFileForId($id, FileReference $ref, $newBasename = null, $copy = false, $onConflict = FilesystemInterface::CONFLICT_OVERWRITE)
    {
        $e = $this->dispatcher->dispatch(Events::FILESYSTEM_PRE_ADD, new FilesystemEvent($this->fs, $ref, $id));
        $ref = $e->getFileReference();
        $newRef = $this->fs->addFileForId($id, $ref, $newBasename, $copy, $onConflict);
        $e = $this->dispatcher->dispatch(Events::FILESYSTEM_POST_ADD, new FilesystemEvent($this->fs, $newRef, $id));

        return $e->getFileReference();
    }

    /**
     * {@inheritdoc}
     */
    public function getCount($return = FilesystemInterface::COUNT_FILES)
    {
        return $this->fs->getCount($return);
    }

    /**
     * {@inheritdoc}
     */
    public function getStats()
    {
        return $this->fs->getStats();
    }

    /**
     * Return the instance of the actual filesystem being wrapped by the manager.
     *
     * @return FilesystemInterface
     */
    public function getFilesystem()
    {
        return $this->fs;
    }
}
