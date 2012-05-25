<?php

namespace Ayamel\FilesystemBundle\Analyzer;

use Ayamel\ResourceBundle\Document\FileReference;

/**
 * Defines what a file analysis object must provide.
 *
 * @author Evan Villemez
 */
interface AnalyzerInterface {

	/**
	 * Return boolean whether or not the analyzer can analyze the given file
	 *
	 * @param FileReference $ref 
	 * @return boolean
	 */
	function acceptsFile(FileReference $ref);
	
	/**
	 * Analyze a FileReference to populate its attributes property.
	 *
	 * @return FileReference
	 */
	function analyzeFile(FileReference $ref);
	
}