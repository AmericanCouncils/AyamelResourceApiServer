<?php

namespace Ayamel\TranscodingBundle\RabbitMQ;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use AC\Component\Transcoding\Transcoder;
use Ayamel\FilesystemBundle\Filesystem\FilesystemInterface;

/**
 * Listens for Resource transcode jobs and processes accordingly.
 *
 * For now, this processes an entire resource at a time, eventually
 * it will probably be more efficient to schedule individual transcode jobs,
 * but that can be determined after some real use.
 *
 * The format of the message processed is the following:
 *  array(
 *      'id' => $id,                            //the string resource id
 *      'appendFiles' => false,                 //whether or not to add transcoded files into the existing files array, or replace them
 *      'presetFilter' => array(),              //limit job to specific presets
 *      'mimeFilter' => array(),                //limit job to specific mimes
 *      'replacePreexisting' => true            //whether or not to replace a preexisting file
 *  );
 *
 * @package AyamelTranscodingBundle
 * @author Evan Villemez
 */
class Consumer implements ConsumerInterface
{
    private $container;
    
    public function __construct(ContianerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * Process any applicable transcode jobs for a given resource.  Returning true
     * removes the job from the queue (what we do if there is no real work to be done)
     *
     * Returning false requeues the message to be processed again later
     *
     * @param AMQPMessage $msg 
     */
	public function execute(AMQPMessage $msg)
	{
		$id = $msg->body['id'];
        $appendFiles = isset($msg->body['appendFiles']) ? (bool) $msg->body['appendFiles'] : false;
        $presetFilter = () ? : array();
        $mimeFilter = () ? : array();
        
        try {
            $this->container->get('ayamel.transcoding.manager')->transcodeFilesForResource($id, $appendFiles, $presetFilter, $mimeFilter);
        } catch (\Exception $e) {
            //logic for type of exception
            //log it if necessary
        }
        
        //TODO: fire resource_modified event
        
        return true;
        
        //TODO: logic below this line should move into TranscodeManager
        
        $resource = $this->container->get('ayamel.resource.manager')->getResourceById($id);
        
        //if not found or deleted, return and remove message
        if (!$resource || !is_null($resource->getDateDeleted())) {
            return true;
        }
        
        //if locked, reprocess message later by returning false
        if ($resource->isLocked()) {
            return false;
        }
        
        //should be at least one file
        $files = $resource->content->getFiles();
        if (!$files || empty($files)) {
            return true;
        }
                
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
        
        //get applicable preset mappings for file
        $mime = $fileToTranscode->getMime();
        $mapper = $this->container->get('ayamel.transcoding.mapper');
        //TODO: add client-specific presets if applicable
        //$mapper->addPresetDefinitions($clientPresetDefinitions);
        $presetDefinitions = $mapper->getPresetsForMime($mime);
        
        //can the mapper find presets that even apply to this file?
        if (!$presetDefinitions || !isset($presetDefinitions[$mime])) {
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
        $resource->content->setFiles($newFileReferences);
        $resource->content->addFile($fileToTranscode);
        
        try {
            //we only want to delete the old files if the new data persists correctly
            $this->container->get('ayamel.resource.manager')->persistResource($resource);
        } catch (\Exception $e) {
            $this->container->get('logger')->addError(sprintf("Failed async transcode job for resource [%s] with %s('%s').", $resource->getId(), get_class($e), $e->getMessage()));
            
            //clean up after failed transcoding by deleting the new files
            
            //someting weird happened, so reschedule this job
            return false;
        }

        if (!empty($fileReferencesToBeRemoved)) {
            //$this->container->get('ayamel.api.filesystem')->removeReferences($fileReferencesToBeRemoved);
        }
        
        return true;
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
}
