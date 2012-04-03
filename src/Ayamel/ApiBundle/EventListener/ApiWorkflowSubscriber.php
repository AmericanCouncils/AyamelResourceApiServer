<?php

namespace Ayamel\ApiBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

/**
 * A listener that monitors input/output for all requests under /api.  Enforces accept header, and logs requests.
 *
 * @author Evan Villemez
 */
class ApiWorkflowSubscriber implements EventSubscriberInterface {

	/**
	 * @var Symfony\Component\DependencyInjection\ContainerInterface
	 */
	protected $container;
	
	/**
	 * String format to use during view events, passed to the JMS Serializer
	 *
	 * @var string
	 */
	protected $format;

	public function __construct(ContainerInterface $container) {
		$this->container = $container;
	}
	
	/**
	 * Register a listener for all kernel events to implement api-specific workflow.
	 */
	public static function getSubscribedEvents() {
		return array(
			KernelEvents::CONTROLLER => array('onKernelController', 1000),
			KernelEvents::EXCEPTION => array('onKernelException', 1000),
			KernelEvents::RESPONSE => array('onKernelResponse', 1000),
			KernelEvents::VIEW => array('onKernelView', 1000),
			KernelEvents::TERMINATE => array('onKernelTerminate', 1000),
		);
	}

	/**
	 * Note that this event listener is NOT registered - it is called manually by the ApiBootstrapListener.
	 *
	 * @param GetResponseEvent $e 
	 */
	public function onKernelRequest(GetResponseEvent $e) {
		$this->format = $this->container->hasParameter('ayamel.api.default_format') ? $this->container->getParameter('ayamel.api.default_format') : 'json';
		
		//check if request specifies a format in any way, record it here
	}

	/**
	 * Called once the controller to be used has been resolved.
	 */
	public function onKernelController(FilterControllerEvent $e) {
		//any auth logic if not integrated into security, possibly profiling logic as well
	}

	/**
	 * Called if an exception was thrown at any point.
	 */
	public function onKernelException(GetResponseForExceptionEvent $e) {
		//handle exception body format
//		die("wtf");
		$exception = $e->getException();
		
		//preserve specific http exception codes and messages, otherwise it's 500
		$errorCode = ($exception instanceof HttpException) ? $exception->getCode() : 500;
		$errorMessage = ($exception instanceof HttpException) ? $exception->getMessage() : "Internal Server Error";
		
		$errorData = array(
			'error' => array(
				'code' => $errorCode,
				'message' => $errorMessage,
			)
		);
		
		$content = $this->container->get('serializer')->serialize($errorData, $this->format);
		$response = new Response($content, $errorCode);
		
		//TODO: properly set error response headers (particularly content-type)
		
		$e->setResponse($response);
	}

	/**
	 * Called when a response object has been resolved.
	 */
	public function onKernelResponse(FilterResponseEvent $e) {
		//compare returned response with request header accepted mimes, modify accordingly
		
	}

	/**
	 * Called when a controller does not return a response object.  Any format abstraction logic would have to initiate here.
	 */
	public function onKernelView(GetResponseForControllerResultEvent $e) {

		//load serializer, enforce best format
		$request = $e->getRequest();
		
		//if bad format requested
		if(true) {
			throw new HttpException(415);
		}
		
		$response = new Response();
		$response->setStatusCode(200);
		$response->setContent($this->container->get('serializer')->serialize(null, $this->format));

		//TODO: consider cache logic here
		
		$e->setResponse($response);
	}
	
	/**
	 * Called after a response has already been sent.  Any shutdown/bookeeping functionality should initiate here.
	 */
	public function onKernelTerminate(PostResponseEvent $e) {
		//log the request, maybe do this via dispatching listeners instead of directly?
	}

}