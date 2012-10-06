<?php

namespace AC\WebServicesBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use AC\WebServicesBundle\EventListener\ApiWorkflowSubscriber;

//TODO: REIMPLEMENT THIS AS A FIREWALL LISTENER
//TODO: listen for all kernel events, firing an early/late listener pair.... maybe

/**
 * A listener that monitors for incoming requests under `/rest/`.  When detected, registers the RestWorkflowSubscriber to handle Api events.
 * 
 * Note: As of right now we only support REST...if we ever decide to support SOAP, it can register listeners for that here as well, but it may not actually be necessary.
 */
class ApiBootstrapListener {

    /**
     * @var Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * Listens for request uris beginning with `/rest/`, and registers other listeners accordingly.
     * Also scans the incoming request accept headers - if JSON is not an acceptible format, throws exception.
     *
     * @param GetResponseEvent $e 
     */
    public function onKernelRequest(GetResponseEvent $e) {
        $request = $e->getRequest();

        //if requested path contains `/rest/`, register the RestWorkflowListener
        if(false !== strpos($request->getPathInfo(), "/api/")) {
            //build rest subscriber
            $subscriber = new RestWorkflowSubscriber($this->container);

            //register subscriber with dispatcher
            $this->container->get('event_dispatcher')->addSubscriber($subscriber);

            //manually call subscriber's `onKernelRequest`
            $subscriber->onApiRequest($e);
        }
        
        //eventually, if decided, listen for SOAP requests ... maybe?
        
        
        //TODO:
        //$servicesDispatcher->dispatch(Events::REQUEST);
    }
    
    public function onKernelTerminate(PostResponseEvent $e) {
        //$servicesDispatcher->dispatch(Events::TERMINATE)
    }
}