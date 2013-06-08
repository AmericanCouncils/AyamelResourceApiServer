<?php

namespace Ayamel\ApiBundle\EventListener;

use Ayamel\ResourceBundle\Document\Resource;
use Ayamel\ResourceBundle\Document\FileReference;
use Ayamel\ApiBundle\Event\Events;
use Ayamel\ApiBundle\Event\ResolveUploadedContentEvent;
use Ayamel\ApiBundle\Event\HandleUploadedContentEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Registers API event listeners for handling special URI uploads.
 *
 * @author Evan Villemez
 */
class UriContentSubscriber implements EventSubscriberInterface
{
    /**
     * @var object Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Array of events subscribed to.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::RESOLVE_UPLOADED_CONTENT => 'onResolveContent',
            Events::HANDLE_UPLOADED_CONTENT => 'onHandleContent',
        );
    }

    /**
     * Check incoming request for a string uri, either as a post field, or json structure.
     *
     * @param ResolveUploadedContentEvent $e
     */
    public function onResolveContent(ResolveUploadedContentEvent $e)
    {
        $request = $e->getRequest();
        $uri = false;
        if ($json = json_decode($request->getContent())) {
            $uri = isset($json->uri) ? $json->uri : false;
        } else {
            $uri = $request->request->get('uri', false);
        }

        if(!$uri) return;

        $exp = explode("://", $uri);
        if (2 === count($exp)) {
            if ($this->container->get('ayamel.resource.provider')->handlesScheme($exp[0])) {
                $e->setContentType('uri');
                $e->setContentData($uri);
            } else {
                throw new HttpException(400, "Does not support scheme [".$exp[0]."]");
            }
        }
    }

    /**
     * Process a specified uri and modify resource accordingly.
     *
     * @param HandleUploadedContentEvent $e
     */
    public function onHandleContent(HandleUploadedContentEvent $e)
    {
        if ('uri' !== $e->getContentType()) {
            return;
        }

        //try to derive a resource from the received uri
        $uri = $e->getContentData();
        if (!$derivedResource = $this->container->get('ayamel.resource.provider')->createResourceFromUri($uri)) {
            throw new \RuntimeException(sprintf("Could not derive resource from [%s]", $uri));
        }

        $oldResource = $e->getResource();

        $newResource = $this->mergeResources($oldResource, $derivedResource);
        $newResource->setStatus(Resource::STATUS_NORMAL);
        
        $e->setResource($resource);
    }
    
    protected function mergeResources($old, $new)
    {
        $this->mergeDocumentProperties($old, $new, array('title', 'type', 'functionalDomains', 'subjectDomains', 'license', 'copyright', 'description', 'keywords', 'languages'));
        
        $old->content = $new->content;
        $old->origin = $this->mergeDocumentProperties($old->origin, $new->origin, array('creator', 'location', 'date', 'format', 'note', 'uri'));
        
        return $old;
    }
    
    protected function mergeDocumentProperties($old, $new, $properties = array())
    {
        foreach ($properties as $prop) {
            $setter = 'set'.ucfirst($prop);
            $getter = 'get'.ucfirst($prop);
            if (method_exists($old, $getter) && method_exists($old, $setter) && method_exists($new, $getter)) {
                if (!$old->$getter()) {
                    $old->$setter($new->$getter());
                }
            } else {
                if (property_exists($old, $prop) && property_exists($new, $prop)) {
                    if (!$old->$prop) {
                        $old->$prop = $new->$prop;
                    }
                }
            }
        }
    }
}
