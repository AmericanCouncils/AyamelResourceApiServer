<?php

namespace Ayamel\FilesystemBundle\Analyzer;

use Doctrine\Common\Cache\Cache;
use Ayamel\ResourceBundle\Document\FileReference;

/**
 * A class for using multiple file analyzers to populate the attributes field of a file reference.
 *
 * @author Evan Villemez
 */
class DelegatingAnalyzer implements AnalyzerInterface
{
    protected $cache;

    protected $cacheResults;

    protected $cacheTTL;

    protected $analyzers = [];

    /**
     * Constructor, tell it whether or not to analyze remote files, and optionally give it a cache mechanism.
     *
     * @param string $analyzeRemote
     * @param Cache  $cache
     */
    public function __construct(Cache $cache = null, $cacheResults = true, $cacheTTL = 0)
    {
        $this->cache = $cache;
        $this->cacheResults = $cacheResults;
        $this->cacheTTL = $cacheTTL;
    }

    /**
     * Set TTL on cached items in seconds
     *
     * @param boolean $bool
     */
    public function setAnalyzeRemoteFiles($ttl)
    {
        $this->cacheTTL = $ttl;
    }

    /**
     * Set whether or not to cache results of analysis
     *
     * @param boolean $bool
     */
    public function setCacheResults($bool)
    {
        $this->cacheResults = (bool) $bool;
    }

    public function getAnalyzers()
    {
        return $this->analyzers;
    }

    /**
     * Register an analyzer.
     *
     * @param AnalyzerInterface $a
     */
    public function registerAnalyzer(AnalyzerInterface $a)
    {
        $this->analyzers[] = $a;
    }

    /**
     * {@inheritdoc}
     *
     * If one registered analyzer can handle the file, will return true, if none can, false.
     */
    public function acceptsFile(FileReference $ref)
    {
        foreach ($this->analyzers as $analyzer) {
            if ($analyzer->acceptsFile($ref)) {
                return true;
            }
        }

        return false;
    }

    /**
      * Attempts to use a cache to return file attributes if possible, otherwise calls all registered
     * analyzers to analyze a file, and caches results for future calls possible.
     *
     * {@inheritdoc}
     */
    public function analyzeFile(FileReference $ref)
    {
        //using md5 in the cache keys to avoid proplematic characters
        $cacheKey = ($ref->getInternalUri()) ? md5($ref->getInternalUri())."_attrs" : md5($ref->getDownloadUri())."_attrs";

        //check cache first if we can
        if ($this->cache) {
            if ($attrs = $this->cache->fetch($cacheKey)) {
                $ref->setAttributes($attrs);

                return $ref;
            }
        }

        //call all analyzers
        $analyzersUsed = 0;
        foreach ($this->analyzers as $analyzer) {
            if ($analyzer->acceptsFile($ref)) {
                $analyzer->analyzeFile($ref);
                $analyzersUsed++;
            }
        }

        //return early if nothing was analyzed
        if (0 === $analyzersUsed) {
            return $ref;
        }

        //cache results if we can
        if ($this->cache && $this->cacheResults) {
            $this->cache->save($cacheKey, $ref->getAttributes(), $this->cacheTTL);
        }

        return $ref;
    }

}
