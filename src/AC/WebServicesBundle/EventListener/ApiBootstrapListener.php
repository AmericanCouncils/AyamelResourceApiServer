<?php

namespace AC\WebServicesBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use AC\WebServicesBundle\EventListener\ApiWorkflowSubscriber;

//TODO: REIMPLEMENT THIS AS A FIREWALL LISTENER

/**
 * A listener that monitors for incoming Api requests.  When detected, registers the RestWorkflowSubscriber to handle generic REST API functionality.
 */
class ApiBootstrapListener {

    /**
     * @var Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    protected $paths;

    public function __construct(ContainerInterface $container, $paths = array()) {
        $this->container = $container;
        $this->paths = $paths;
    }

    /**
     * Checks arrays of regex against requested route, and registers other listeners accordingly.
     *
     * @param GetResponseEvent $e 
     */
    public function onKernelRequest(GetResponseEvent $e) {
        $request = $e->getRequest();

        foreach ($this->paths as $pathRegex) {
            if (preg_match($pathRegex, $request->getPathInfo())) {
                //build rest subscriber
                $subscriber = new RestWorkflowSubscriber($this->container);

                //register subscriber with dispatcher
                $this->container->get('event_dispatcher')->addSubscriber($subscriber);

                //manually call subscriber's `onKernelRequest`
                $subscriber->onApiRequest($e);
                
                return;
            }
        }
    }
}
