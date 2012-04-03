<?php

namespace Ayamel\ApiBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Ayamel\ApiBundle\EventListener\ApiWorkflowSubscriber;

/**
 * A listener that monitors incoming requests under `/api/`.  When detected, registers the ApiWorkflowSubscriber to handle Api events.
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
	 * Listens for request uris beginning with `/api/`, and registers other listeners accordingly.
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
		
		//if requested path doesn't begin with `/api/`, return early
		if(0 !== strpos($request->getPathInfo(), "/api/")) {
			return;
		}

		//build and manually call subscriber's `onKernelRequest`
		$subscriber = new ApiWorkflowSubscriber($this->container);
		$subscriber->onKernelRequest($e);
		
		//register subscriber with dispatcher
		$this->container->get('event_dispatcher')->addSubscriber($subscriber);
	}
}