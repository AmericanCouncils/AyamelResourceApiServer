<?php

namespace Ayamel\ApiBundle\EventListener;

use Ayamel\ResourceBundle\Document\Resource;
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
        $e->setResource($newResource);
    }

    /**
     * merge derived resource properties into the existing resource
     *
     * @param  Resource $old
     * @param  Resource $new
     * @return Resource
     */
    protected function mergeResources(Resource $old, Resource $new)
    {
        //set any unset top-level properties
        $this->mergeDocumentProperties(
            $old,
            $new,
            [
                'title',
                'type',
                'functions',
                'topics',
                'genres',
                'authenticity',
                'formats',
                'registers',
                'license',
                'copyright',
                'description',
                'keywords',
                'subjectDomains',
                'functionalDomains'
            ]
        );

        //always take newly derived content
        $old->setContent($new->getContent());

        //set origin if not previously set
        if ($old->origin) {
            $this->mergeDocumentProperties($old->getOrigin(), $new->getOrigin(), ['creator', 'location', 'date', 'format', 'note', 'uri']);
        } else {
            $old->setOrigin($new->getOrigin());
        }

        //set languages if not previously set
        if ($old->getLanguages()) {
            $this->mergeDocumentProperties($old->getLanguages(), $new->getLanguages(), ['iso639_3', 'bcp47']);
        } else {
            $old->setLanguages($new->getLanguages());
        }

        return $old;
    }

    protected function mergeDocumentProperties($old, $new, $properties = [])
    {
        if (get_class($old) !== get_class($new)) {
            throw new \LogicException(sprintf(
                "Attempted to merge instances of different types (%s and %s).",
                var_export(get_class($old), true),
                var_export(get_class($new), true)
            ));
        }

        $reflClass = new \ReflectionClass(get_class($old));

        foreach ($properties as $prop) {
            $reflProp = $reflClass->getProperty($prop);
            $protected = ($reflProp->isProtected() || $reflProp->isPrivate());

            if ($protected) {
                $reflProp->setAccessible(true);
            }

            $oldValue = $reflProp->getValue($old);

            if (is_null($oldValue) || empty($oldValue)) {
                $reflProp->setValue($old, $reflProp->getValue($new));
            }
            
            if ($protected) {
                $reflProp->setAccessible(false);
            }
        }
    }
}
