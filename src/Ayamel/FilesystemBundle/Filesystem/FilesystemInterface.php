<?php

namespace Ayamel\FilesystemBundle\Filesystem;

use Ayamel\ResourceBundle\Document\FileReference;

/**
 * Filesystem instances assume that all files related to a given resource share a common base path, which is derived
 * from the Resource id.
 *
 * @author Evan Villemez
 */
interface FilesystemInterface {
    
    /**
     * If a file already exists, overwrite it
     */
    const CONFLICT_OVERWRITE = 1;
    
    /**
     * If a file already exists, throw an exception
     */
    const CONFLICT_EXCEPTION = 2;
    
    /**
     * Count only real files, not directories.
     */
    const COUNT_FILES = 1;
    
    /**
     * Count only directories
     */
    const COUNT_DIRECTORIES = 2;
    
    /**
     * Count both files and directories
     */
    const COUNT_ALL = 3;
    
    /**
     * Return array with both file and directory counts.
     */
    const COUNT_BOTH = 4;
    
    /**
     * Generate a string base directory given an id.
     *
     * @param string $id 
     * @return string
     */
    function generateBaseDirectoryForId($id);
    
    /**
     * Generate a string base path to use in file names for a given Resource ID.  This includes
     * the base directory, plus the common part of the file names from `generateBaseFilenameForId`
     *
     * @param string $id 
     * @return string
     */
    function generateBasePathForId($id);
    
    /**
     * Generates the first part of a string filename, given a Resource ID
     *
     * @param string $id 
     * @return string
     */
    function generateBaseFilenameForId($id);
	
	/**
	 * Get the resource id associated with a given FileReference
	 *
	 * @param FileReference $ref 
	 * @return string
	 */
	function getIdForFile(FileReference $ref);
	
	/**
	 * Remove a specific FileReference instance, regardless of which object it's associated with.
	 *
	 * @param FileReference $ref 
	 * @return boolean on success
	 */
	function removeFile(FileReference $ref);
	    
    /**
     * Remove a specific file by name for an id.  The name should NOT include elements of the base file name generated
     * by the file system.
     *
     * @param string $id - resource id of owning object
     * @param string $name - end of file name including extension, unique for that object
     * @return boolean
     */
    function removeFileForId($id, $name);
    
    /**
     * Remove all files for a given Resource ID.
     *
     * @param string $id 
     * @return int - the number of files successfully removed
     */
    function removeFilesForId($id);
	
    /**
     * Return a specific FileReference instance for an id, given a unique name.
     *
     * @param string $id 
     * @param string $name 
     * @return FileReference
     */
    function getFileForId($id, $name);
    
    /**
     * Get array of FileReference instances for all files from a given Resource ID
     *
     * @param string $id 
     * @return array
     */
    function getFilesForId($id);
	
	/**
	 * Return boolean whether or not a file by a particular name exists for the given Resource ID.
	 *
	 * @param string $id 
	 * @param string $name 
	 * @return boolean
	 */
	function hasFileForId($id, $name);
    
    /**
     * Add a new file for a given id.  The basename, if provided, will be appended to the base path for
     * the given id.  If $basename is not specified, it will be derived from the original filename.
     * 
     * Any relocation operations should be performed in this method as needed.
     * 
     *
     * @param string $id - resource id
     * @param FileReference $file - reference to the file to associate with a given id.
     * @param string $newBasename - optional new basename to use for file
     * @param boolean $copy - whether or not to copy the file to it's new location, or move it.
     * @param int $onConflict - how to handle conflicts for files that already exist, either throw exceptions or overwrite
     * 
     * @throws \RuntimeException if the incoming FileReference cannot be read.
     * @throws \RuntimeException if the file to write already exists and $onConflict is set to FilesystemInterface::CONFLICT_EXCEPTION
     * 
     * @return FileReference - reference to file at it's new location
     */
    function addFileForId($id, FileReference $file, $newBasename = null, $copy = false, $onConflict = FilesystemInterface::CONFLICT_OVERWRITE);
    
    /**
     * Return the # of files currently being managed by the file system.
     *
     * @param string $includeDirectories - whether or not to include directories in the count
     * @return int
     */
    function getCount($return = FilesystemInterface::COUNT_FILES);
}
