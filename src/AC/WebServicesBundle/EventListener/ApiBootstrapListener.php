<?php

namespace AC\WebServicesBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * A listener that monitors for incoming Api requests.  When detected, registers the RestWorkflowSubscriber to handle generic REST API functionality.
 */
class ApiBootstrapListener
{
    /**
     * @var Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    protected $paths;

    public function __construct(ContainerInterface $container, $paths = array())
    {
        $this->container = $container;
        $this->paths = $paths;
    }

    /**
     * Checks arrays of regex against requested route, and registers other listeners accordingly.
     *
     * @param GetResponseEvent $e
     */
    public function onKernelRequest(GetResponseEvent $e)
    {
        $request = $e->getRequest();

        foreach ($this->paths as $regex) {
            if (preg_match($regex, $request->getPathInfo())) {
                //build rest subscriber
                $subscriber = new RestServiceSubscriber(
                    $this->container,
                    $this->container->getParameter('ac.webservices.default_response_format'),
                    $this->container->getParameter('ac.webservices.include_response_data'),
                    $this->container->getParameter('ac.webservices.allow_code_suppression'),
                    $this->container->getParameter('ac.webservices.include_dev_exceptions')
                );

                //register subscriber with dispatcher
                $this->container->get('event_dispatcher')->addSubscriber($subscriber);

                $subscriber->onApiRequest($e);

                return;
            }
        }
    }
}
