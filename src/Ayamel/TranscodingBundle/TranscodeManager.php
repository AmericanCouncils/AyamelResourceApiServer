<?php

namespace Ayamel\TranscodingBundle;

use Ayamel\ResourceBundle\Storage\StorageInterface;
use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\FileReference;
use Ayamel\FilesystemBundle\Filesystem\FilesystemInterface;
use AC\Component\Transcoding\Transcoder;

//TODO: Fire RESOURCE_MODIFIED event if everything goes correctly

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
	
	private $filesystem;
	private $resourceManager;
	private $transcoder;
    private $defaultMapperConfig;
    //private $clientManager;
	
    /**
     * Constructor. The manager needs several objects to keep the filesystem and data in sync for resource objects
     *
     * @param FilesystemInterface $fs 
     * @param ResourceManagerInterface $rm 
     * @param Transcoder $t 
     * @param $defaulMapperConfig 
     */
    public function __construct(FilesystemInterface $fs, StorageInterface $rm, Transcoder $t, $defaultMapperConfig = array())
    {
        $this->filesystem = $fs;
        $this->resourceManager = $rm;
        $this->transcoder = $t;
        $this->defaultMapperConfig = $defaultMapperConfig;
    }
    
    protected function createMapperForResource(Resource $resource)
    {
        $mapper = new PresetMapper($this->defaultMapperConfig);

        //NOTE: eventually we'll check the client for custom preset and inject them here:
        //$mapper->addPresetDefinitions($this->apiClientManager->getClient($resource->getClient()->getId())->getPresetMappings());

        return $mapper;
    }
    
	public function transcodeResource($id, $appendFiles = false, $presetFilter = array(), $mimeFilter = array())
	{
        //get resource or fail with relevant exception
        $resource = $this->getResource($id);
        
        //get file to transcode
        $refToTranscode = $this->getRefToTranscode($resource);
        
        //get relevant preset mappings
        $mappings = $this->createMapperForResource($resource)->getPresetMappingsForFileReference($refToTranscode);
        
        //filter mappings
        $mappings = $this->filterPresetMappings($refToTranscode, $presetFilter, $mimeFilter);
        if (empty($mappings)) {
            throw new NoRelevantPresetsException();
        }
        
        //transcode new files
        $newReferences = $this->transcodeFileForResource($refToTranscode, $resource, $mappings);
        
        //resolve old/new data, move files into place, etc...
        $resource->content->setFiles($newFileReferences);
        $resource->content->addFile($fileToTranscode);
        
        //we only transcode original files, if there isn't one we can stop
        $fileToTranscode = false;
        foreach ($resource->content->getFiles() as $file) {
            if ("original" === $file->getRepresentation()) {
                $fileToTranscode = $file;
                break;
            }
        }
        if (!$fileToTranscode) {
            return true;
        }
                
        //TODO: check for preset and or mime restrictions for this job (may have been initiated from CLI)
        
        //if we got this far, we can transcode the file
        $newFileReferences = $this->transcodeFileReferenceForResource($fileToTranscode, $resource, $presetDefinitions);
        
        //store old files array in memory until we have finished successfully
        $oldFilesArray = $resource->content->getFiles();

        //TODO: delete pre-existing files?
        //get $fileReferencesToBeRemoved
        
        //set file content as newly created files & add original file back into array
        //TODO: this isn't quite right
        
        
        return true;
	}
    
    /**
     * Get the Resource to transcode by an id, throwing exceptions
     * for all the various reasons it may not be transcodeable
     *
     * @param string $id 
     * @return Resource
     * @throws ResourceNotFoundException If not found
     * @throws ResourceDeletedException If resource was previously deleted
     * @throws NoTranscodeableFilesException If the Resource doesn't have files that can be transcoded
     */
    protected function getResource($id)
    {
        $resource = $this->resourceManager->getResourceById($id);
        
        if (!$resource) {
            throw new ResourceNotFoundException(sprintf("Resource [%s] could not be found.", $id));
        }
        
        if (Resource::STATUS_DELETED === $resource->getStatus()) {
            throw new ResourceDeletedException(sprintf("Resource [%s] has been deleted and has no content.", $id));
        }
        
        if ($resource->isLocked()) {
            throw new ResourceLockedException(sprintf("Resource [%s] is currently locked.", $id));
        }
        
        //should be at least one file
        $files = $resource->content->getFiles();
        if (!$files || empty($files)) {
            throw new NoTranscodeableFilesException(sprintf("Resource [%s] has no transcodeable files.", $id));
        }
        
        return $resource;
    }
    
    protected function transcodeFileReferenceForResource(FileReference $ref, $resource, $presetDefinitions)
    {
        $newFiles = array();
        foreach ($presetDefinitions as $presetName => $refInfo) {
            try {
                //run the transcode & create a FileReference from the resulting file
                $newFileReference = FileReference::createFromPath($this->container->get('transcoder')->transcodeWithPreset(
                    $ref->getInternalUri(),
                    $presetName,
                    $this->generateTemporaryOutputPath($resource->getId(), $refInfo)
                )->getRealPath());
                
                //inject file reference data from the mapper
                $newFileReference->setQuality($refInfo['quality']);
                $newFileReference->setRepresentation($refInfo['representation']);
                
                //new base name for file with tag + output extension
                $newBaseName = $refInfo['tag'].".".$refInfo['extension'];
                
                //add file into filesystem (will move it to final location)
                $finalReference = $this->container->get('ayamel.api.filesystem')->addFileForId($resource->getid(), $newFileReference, $newBaseName, FilesystemInterface::CONFLICT_EXCEPTION);
                
                //store good file reference in array
                $newFiles[] = $finalReference;
            } catch (\Exception $e) {
                
            }
        }
        
        return $newFiles;
    }

    protected function generateTemporaryOutputPath($id, $presetInfo)
    {
        $ext = $presetInfo['extension'];
        $tag = $presetInfo['tag'];
        return $this->container->get('ayamel.transcoding.temp_directory').DIRECTORY_SEPARATOR.$id.".".$tag.".".$ext;
    }
    
	protected function filterPresetMappings(FileReference $ref, $presetFilter, $mimeFilter)
    {
        
    }
}
