<?php

namespace Ayamel\TranscodingBundle;

/**
 * This class transcodes original files in a resource into multiple files
 * depending on mappings from configuration.  This class uses various other
 * objects in the Ayamel API to make sure that the filesystem and the data
 * in the database stays in sync.
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
    private $mapper;
    //private $clientManager;
	
    /**
     * Construct. The manager needs several objects to keep the filesystem and data in sync for resource objects
     *
     * @param FilesystemInterface $fs 
     * @param ResourceManagerInterface $rm 
     * @param Transcoder $t 
     * @param Mapper $m 
     */
    public function __construct(FilesystemInterface $fs, ResourceManagerInterface $rm, Transcoder $t, Mapper $m)
    {
        
    }
    
    
	public function transcodeFilesForResource($id, $appendFiles = false, $mimeRestrictions, )
	{
        
	}
	
}