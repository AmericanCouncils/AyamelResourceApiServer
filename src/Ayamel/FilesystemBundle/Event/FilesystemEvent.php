<?php

namespace Ayamel\FilesystemBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Ayamel\ResourceBundle\Document\FileReference;
use Ayamel\FilesystemBundle\Filesystem\FilesystemInterface;

class FilesystemEvent extends Event {

	/**
	 * @var object - Ayamel\FilesystemBundle\Filesystem\FilesystemInterface
	 */
	protected $fs;
	
	/**
	 * @var object - Ayamel\ResourceBundle\Document\FileReference
	 */
	protected $ref;
	
	/**
	 * @var string - the id of the Resource associated with the file reference
	 */
	protected $id;
	
	/**
	 * Constructor
	 *
	 * @param FilesystemInterface $fs 
	 * @param FileReference $ref 
	 * @param string $id 
	 */
	public function __construct(FilesystemInterface $fs, FileReference $ref, $id) {
		$this->id = $id;
		$this->ref = $ref;
		$this->fs = $fs;
	}
	
	/**
	 * Return the id for the resource associated with this event.
	 *
	 * @return mixed
	 */
	public function getResourceId() {
		return $this->id;
	}
	
	/**
	 * Return the FileReference instance associated with this event.
	 *
	 * @return FileReference
	 */
	public function getFileReference() {
		return $this->ref;
	}
	
	/**
	 * Return the Filesystem associated with this event.
	 *
	 * @return FilesystemInterface
	 */
	public function getFilesystem() {
		return $this->fs;
	}

}