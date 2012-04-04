<?php

namespace Ayamel\ApiBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Ayamel\ApiBundle\EventListener\ApiWorkflowSubscriber;

/**
 * A listener that monitors incoming requests under `/rest/`.  When detected, registers the ApiWorkflowSubscriber to handle Api events.
 * 
 * Note: If we ever decide to support SOAP, it can register listeners for that here as well, but it may not be necessary.
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

		//only handle master requests
		if(HttpKernelInterface::MASTER_REQUEST !== $e->getRequestType()) {
			return;
		}
		
		//if requested path contains `/rest/`, register the RestWorkflowListener
		if(false !== strpos($request->getPathInfo(), "/rest/")) {
			//build rest subscriber
			$subscriber = new RestWorkflowSubscriber($this->container);

			//register subscriber with dispatcher
			$this->container->get('event_dispatcher')->addSubscriber($subscriber);

			//manually call subscriber's `onKernelRequest`
			$subscriber->onApiRequest($e);
		}
		
		//eventually, if decided, listen for SOAP requests ... maybe?

	}
}