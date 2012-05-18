<?php

namespace Ayamel\MutateBundle;

use AC\Mutate\TranscodeEventListener;

/**
 * Logs all transcode events, and notifies a client app if a transcode fails.
 */
class TranscodeLogListener extends TranscodeEventListener {
	
	protected $logger;
	
	public function __construct($logger) {
		$this->logger = $logger;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function onTranscodeStart(File $inFile, Preset $preset, $outputFilePath) {
		$this->logger->info(sprintf("Trying to transcode [%s] to [%s] using preset [%s]", $inFile->getPathname(), $outputFilePath, $preset->getKey()));
		return;
	}

	/**
	 * {@inheritdoc}
	 */
	public function onTranscodeComplete(File $inFile, Preset $preset, File $outFile) {
		//log it
		return;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function onTranscodeFailure(\Exception $e, $inputFilePath, $presetKey, $outputFilePath) {
		//log it, and notify client app of failure
		return;
	}
	
	
}