<?php

namespace AC\MarkdownContentBundle\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Routing\Route;
use Symfony\Component\Yaml\Yaml;
use AC\MarkdownContentBundle\ContentPage;

class MarkdownContentRouteLoader implements LoaderInterface {
    
    protected $rootDir;
    protected $routePrefix;
    protected $cache;

    public function __construct(Cache $cache, $rootDir, $prefix) {
        $this->rootDir = $rootDir;
        $this->routePrefix = $prefix;
        $this->cache = $cache;
    }
    
    /**
     * Search the content root directory and add route definition for each file
     *
     * @param string $resource 
     * @param string $type 
     * @return void
     * @author Evan Villemez
     */
    public function load($resource, $type = null)
    {
        //check for cached collection route collection first
        if($collection = $this->cache->get('mdcontent.route_collection', false)) {
            return $collection;
        }

        $collection = new SerializableRouteCollection();
        
        foreach($this->getContentPaths($this->rootDir) as $path) {
            $page = new ContentPage($path);
            $meta = Yaml::parse($page->getHeader());
            
            //check for route overrides
            $overrides = array();
            if(isset($meta['controller'])) {
                $overrides['_controller'] = $meta['controller'];
            }
            
            //define route defaults
            $defaults = array_merge(array(
                "_controller" => "AC\MarkdownContentBundle\Controller\ContentPageController::viewPath",
                "path" => $path
            ), $overrides);
            
            $routeName = implode(".", explode("/", $path));
            
            $collection->add(new Route($routeName, $defaults));
            
        }
        
        $collection->setPrefix($this->routePrefix);
        
        $this->cache->set('mdcontent.route_collection', $collection);
        
        return $collection;
    }
    
    protected function getContentPaths($root = null, $paths = array()) {
        
        return $paths;
    }
 
    public function supports($resource, $type = null)
    {
        return 'markdownDirectory' === $type;
    }
 
    public function getResolver()
    {
    }
 
    public function setResolver(LoaderResolver $resolver)
    {
        // irrelevant to us, since we don't need a resolver
    }
}