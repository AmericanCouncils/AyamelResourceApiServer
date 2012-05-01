<?php

namespace Ayamel\ResourceApiBundle\Filesystem;

use Ayamel\ResourceBundle\Document\FileReference;

/**
 * Filesystem instances assume that all files related to a given resource share a common base path, which is derived
 * from the Resource id.
 *
 * @author Evan Villemez
 */
interface FilesystemInterface {
    
    
    const CONFLICT_OVERWRITE = 1;
    
    
    const CONFLICT_EXCEPTION = 2;
    
    /**
     * Generate a string base directory given an id.
     *
     * @param string $id 
     * @return string
     */
    function generateBaseDirectoryForId($id);
    
    /**
     * Generate a string base path to use in file names for a given Resource ID.  This includes
     * the base directory, plus the common part of the file names from `generateBaseFilename`
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
     * Remove a file given a path.
     *
     * @param string $tag 
     * @return boolean
     */
    function removeFile($path);
    
    /**
     * Remove all files for a given Resource ID.
     *
     * @param string $id 
     * @return boolean
     */
    function removeFilesForId($id);
    
    /**
     * Get array of FileReference instances for all files from a given Resource ID
     *
     * @param string $id 
     * @return array
     */
    function getFilesForId($id);
    
    /**
     * Add a new file for a given id.  The basename, if provided, will be appended to the base path for
     * the given id.  If $basename is not specified, it will be derived from the original filename.
     * Any movement operations should be performed in this method as needed.
     * 
     *
     * @param string $id - resource id
     * @param FileReference $file - reference to the file to associate with a given id.
     * @param string $newBasename - optional new basename to use for file
     * @return FileReference - reference to new file
     */
    function addFileForId($id, FileReference $file, $newBasename = null, $copy = false, $onConflict = FilesystemInterface::CONFLICT_OVERWRITE);
    
}
