<?php

namespace Ayamel\TranscodingBundle;

use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\FileReference;
use Ayamel\FilesystemBundle\Filesystem\FilesystemInterface;
use AC\Transcoding\Transcoder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Ayamel\TranscodingBundle\Exception\NoRelevantPresetsException;
use Ayamel\TranscodingBundle\Exception\NoTranscodeableFilesException;
use Ayamel\TranscodingBundle\Exception\ResourceDeletedException;
use Ayamel\TranscodingBundle\Exception\ResourceNotFoundException;
use Ayamel\TranscodingBundle\Exception\ResourceLockedException;
use Ayamel\ApiBundle\Event\Events as ApiEvents;
use Ayamel\ApiBundle\Event\ApiEvent;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This class transcodes original files in a resource into multiple files
 * depending on mappings from configuration.  This class uses various other
 * objects in the Ayamel API system to make sure that the filesystem and the
 * data stay in sync.
 *
 * For this reason, you cannot really transcode individual files, you can only
 * transcode "Resources" in their entirety, with a few restrictions to narrow
 * the scope of what exactly gets transcoded.
 *
 * Transcoder presets are mapped to mime types in configuration in a special
 * format described below. This is done to ensure as much as possible that
 * the transcoder will be able to successfully transcode a file with as few as
 * possible unknowns regarding the name of the output file.  The configuration
 * also specifies what additional information should be injected into the resulting
 * `FileReference` object, which is eventually stored in the database.
 *
 * See `PresetMapper` for example configuration.
 *
 * @package AyamelTranscodingBundle
 * @author Evan Villemez
 */
class TranscodeManager
{
    private $container;
    private $filesystem;
    private $docManager;
    private $transcoder;
    private $tmpDirectory;
    private $dispatcher;
    private $presetConfig;
    private $presetMap;
    //private $logger;
    //private $clientManager;

    /**
     * Constructor. The manager needs several objects to keep the filesystem and data in sync for resource objects
     *
     * @param FilesystemInterface      $fs
     * @param ResourceManagerInterface $rm
     * @param Transcoder               $t
     * @param $defaulMapperConfig
     */
    public function __construct(ContainerInterface $container, FilesystemInterface $fs, DocumentManager $dm, Transcoder $t, $tmpDirectory, EventDispatcherInterface $dispatcher, $presetConfig = array(), $presetMap = array())
    {
        $this->container = $container;
        $this->filesystem = $fs;
        $this->docManager = $dm;
        $this->transcoder = $t;
        $this->dispatcher = $dispatcher;
        $this->tmpDirectory = $tmpDirectory;
        $this->presetConfig = $presetConfig;
        $this->presetMap = $presetMap;
    }

    /**
     * Transcode original files in a specific resource, given it's ID, and some configuration.
     *
     * @param  string  $id
     * @param  boolean $appendFiles
     * @param  array   $presetFilter
     * @param  array   $mimeFilter
     * @return boolean
     */
    public function transcodeResource($id, $appendFiles = false, $presetFilter = array(), $mimeFilter = array())
    {
        //get resource or fail with relevant exception
        $resource = $this->getResource($id);

        //get file to transcode
        $refsToTranscode = $this->getRefsToTranscode($resource);

        //get mapper, start transcoding files
        $mapper = $this->createMapperForResource($resource);
        $processed = false;
        $i = 0;
        foreach ($refsToTranscode as $ref) {
            $append = ($i < 1) ? $appendFiles : true;
            $mappings = $mapper->getPresetMappingsForFileReference($ref);
            if (!$mappings) {
                $mappings = array();
            }
            $mappings = $this->filterPresetMappings($mappings, $ref, $presetFilter, $mimeFilter);
            if (empty($mappings)) {
                continue;
            }

            //this is just making sure that there ARE valid mappings to process
            $processed = true;

            //transcode new files, modifying Resource accordingly
            $resource = $this->transcodeFileReferenceForResource($ref, $resource, $mappings, $append);
        }

        //was anything processed at all?
        if (!$processed) {
            throw new NoRelevantPresetsException();
        }

        //notify system that Resource was modified
        $this->dispatcher->dispatch(ApiEvents::RESOURCE_MODIFIED, new ApiEvent($resource));

        return $resource;
    }

    /**
     * The real work happens here.  Locks a resource before doing work, then on completion
     * of any transcode work, cleans up the filesystem, and stores the data.0
     *
     * @param  FileReference $ref
     * @param  string        $resource
     * @param  array         $presetDefinitions
     * @return void
     * @author Evan Villemez
     */
    protected function transcodeFileReferenceForResource(FileReference $ref, $resource, array $presetDefinitions, $appendFiles = false)
    {
        //first lock the resource
        $this->lockResource($resource);
        $newFiles = array();
        try {
            foreach ($presetDefinitions as $def) {

                $preset = $this->container->get($def['preset_service']);

                if (isset($def['params'])) {
                    $preset->mergeOptions($def['params']);
                }

                //run the transcode & create a FileReference from the resulting file
                $transcodedFile = $this->transcoder->transcodeWithPreset(
                    $ref->getInternalUri(),
                    $preset,
                    $this->generateTemporaryOutputPath($resource->getId(), $def),
                    Transcoder::ONCONFLICT_DELETE,
                    Transcoder::ONDIR_CREATE,
                    Transcoder::ONFAIL_DELETE
                );

                $newFileReference = FileReference::createFromLocalPath($transcodedFile->getRealPath());

                //inject file reference data from the mapper & transcoded file
                $newFileReference->setMimeType($transcodedFile->getMimeType());
                $newFileReference->setQuality($def['quality']);
                $newFileReference->setRepresentation($def['representation']);
                $newFileReference->setBytes($transcodedFile->getSize());

                //new base name for file with tag + output extension
                $newBaseName = $def['tag'].".".$def['extension'];

                //add file into filesystem (will move it to final location)
                $finalReference = $this->filesystem->addFileForId($resource->getId(), $newFileReference, $newBaseName, false, FilesystemInterface::CONFLICT_OVERWRITE);

                //check for full-mime string, add it if not set
                if (!$finalReference->getMime()) {
                    $finalReference->setMime($finalReference->getMimeType());
                }

                //store good file reference in array
                $newFiles[] = $finalReference;
            }
        } catch (\Exception $e) {
            $this->unlockResource($resource);

            foreach ($newFiles as $failedFile) {
                $this->filesystem->removeFile($failedFile);
            }

            //rethrow exception to be handled by environment
            throw $e;
        }

        //Remove old files or not?
        if (!$appendFiles) {
            $toRemove = array();
            foreach ($resource->content->getFiles() as $file) {
                if ('original' !== $file->getRepresentation()) {
                    $toRemove[] = $file;
                }
            }

            foreach ($toRemove as $oldFile) {
                if ($this->filterOverwrittenFiles($newFiles, $oldFile)) {
                    $this->filesystem->removeFile($oldFile);
                }

                //remove it from the content anyway, the new reference will
                //be re-added in the next step
                $resource->content->removeFile($oldFile);
            }
        }

        //if we got this far, modify the Resource object
        foreach ($newFiles as $newRef) {
            $resource->content->addFile($newRef);
        }

        //unlock & save, note this is persisting the object including
        //the changes
        $this->unlockResource($resource);

        //clean up the filesystem
        $this->cleanupFilesystem($resource);

        return $resource;
    }

    /**
     * Return false if the new file array contains a reference to the same
     * file as the old file, this means the new file overwrote a previously
     * existing file.
     *
     * @param  array         $new
     * @param  FileReference $old
     * @return boolean
     */
    protected function filterOverwrittenFiles(array $new, FileReference $old)
    {
        foreach ($new as $file) {
            if ($file->equals($old)) {
                return false;
            }
        }

        return true;
    }

    protected function lockResource(Resource $resource)
    {
        $resource->setStatus(Resource::STATUS_PROCESSING);
        $this->docManager->flush();
    }

    protected function unlockResource(Resource $resource)
    {
        $resource->setStatus(Resource::STATUS_NORMAL);
        $this->docManager->flush();
    }

    protected function cleanupFilesystem(Resource $resource)
    {
        foreach ($this->filesystem->getFilesForId($resource->getId()) as $ref) {
            if (!$resource->content->hasFile($ref)) {
                $this->filesystem->removeFile($ref);
            }
        }
    }

    /**
     * Get the preset mapper for the resource.
     *
     * @param  Resource     $resource
     * @return PresetMapper
     */
    protected function createMapperForResource(Resource $resource)
    {
        $mapper = new PresetMapper($this->presetConfig, $this->presetMap);

        //NOTE: eventually we'll check the client for custom preset and inject them here:
        //$mapper->addPresetDefinitions($this->apiClientManager->getClient($resource->getClient()->getId())->getPresetMappings());
        return $mapper;
    }

    /**
     * Get the Resource to transcode by an id, throwing exceptions
     * for all the various reasons it may not be transcodeable
     *
     * @param  string                        $id
     * @return Resource
     * @throws ResourceNotFoundException     If not found
     * @throws ResourceDeletedException      If resource was previously deleted
     * @throws NoTranscodeableFilesException If the Resource doesn't have files that can be transcoded
     */
    protected function getResource($id)
    {
        $resource = $this->docManager->getRepository('AyamelResourceBundle:Resource')->find($id);

        if (!$resource) {
            throw new ResourceNotFoundException(sprintf("Resource [%s] could not be found.", $id));
        }

        if (Resource::STATUS_DELETED === $resource->getStatus()) {
            throw new ResourceDeletedException(sprintf("Resource [%s] has been deleted and has no content.", $id));
        }

        if ($resource->isLocked()) {
            throw new ResourceLockedException(sprintf("Resource [%s] is currently locked.", $id));
        }

        return $resource;
    }

    /**
     * Get file references that should be transcoded.
     *
     * @param  Resource $resource
     * @param  string   $path
     * @return array
     */
    protected function getRefsToTranscode(Resource $resource, $path = false)
    {
        $refsToTranscode = array();
        $fileRefs = $resource->content->getFiles();

        if (!$fileRefs || empty($fileRefs)) {
            throw new NoTranscodeableFilesException(sprintf("Resource [%s] has no files.", $resource->getId()));
        }

        foreach ($fileRefs as $ref) {
            if (!$path) {
                //NOTE: we only transcode original, and locally stored file references (may change?)
                if ('original' === $ref->getRepresentation() && $ref->getInternalUri()) {
                    $refsToTranscode[] = $ref;
                }
            } else {
                if ($path === $ref->getInternalUri()) {
                    $refsToTranscode[] = $ref;
                }
            }
        }

        if (empty($refsToTranscode)) {
            throw new NoTranscodeableFilesException(sprintf("Resource [%s] has no files suitable for transcoding.", $resource->getId()));
        }

        return $refsToTranscode;
    }

    protected function generateTemporaryOutputPath($id, $presetInfo)
    {
        $ext = $presetInfo['extension'];
        $tag = $presetInfo['tag'];

        return $this->tmpDirectory.DIRECTORY_SEPARATOR.$id.".".$tag.".".$ext;
    }

    protected function filterPresetMappings(array $mappings, FileReference $ref, $presetFilter = array(), $mimeFilter = array())
    {
        if (!empty($mimeFilter) && !in_array($ref->getMimeType(), $mimeFilter)) {
            return array();
        }

        $filtered = array();
        foreach ($mappings as $key => $data) {
            if (!empty($presetFilter) && !in_array($key, $presetFilter)) {
                continue;
            }

            $filtered[$key] = $data;
        }
        
        $filtered = $this->filterPresetMappingsByConfig($ref, $filtered);
        
        return $filtered;
    }
    
    protected function filterPresetMappingsByConfig(FileReference $ref, array $presets)
    {
        $attrs = $ref->getAttributes();
        $filtered = array();
        
        foreach ($presets as $key => $data) {
            $include = true;
            
            //height
            if (
                isset($attrs['frameSize']['height']) && 
                isset($data['filters']['height']) && 
                $attrs['frameSize']['height'] > $data['filters']['height']
            ) {
                $include = false;
            }
        
            //width
            if (
                isset($attrs['frameSize']['width']) && 
                isset($data['filters']['width']) && 
                $attrs['frameSize']['width'] > $data['filters']['width']
            ) {
                $include = false;
            }
        
            //bitrate
            if (
                isset($attrs['bitrate']) && 
                isset($data['filters']['bitrate']) && 
                $attrs['bitrate'] > $data['filters']['bitrate']
            ) {
                $include = false;
            }
            
            if ($include) {
                $filtered[$key] = $data;
            }
        }
        
        return $filtered;
    }
}
