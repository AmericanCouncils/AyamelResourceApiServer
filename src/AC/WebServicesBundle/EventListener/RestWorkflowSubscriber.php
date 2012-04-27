<?php

namespace AC\WebServicesBundle\EventListener;

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
 * TODO: needs to be properly abstracted, some functionality needs to move out of here and into Ayamel\ResourceApiBundle... could do this via special event dispatchers
 * TODO: Need to implement ServiceResponse where possible
 */


/**
 * A listener that monitors input/output for all requests under `/rest/`.  Enforces accept header, logs requests and handles exceptions thrown by Controllers.
 *
 * @author Evan Villemez
 */
class RestWorkflowSubscriber implements EventSubscriberInterface {

    /**
     * @var Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;
    
    /**
     * The event dispathcher used specifically for API events.
     * 
     * @var Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $dispatcher;
    
    /**
     * String format to use during view events, passed to the JMS Serializer.  Default is JSON unless specified otherwise in configuration.
     *
     * @var string
     */
    protected $format = 'json';
    
    /**
     * Array of content-type headers to used, keyed by requested serialization format.
     *
     * @var array
     */
    protected $formatHeaders = array(
        'json' => 'application/json',
        'xml' => 'application/xml',
        'yml' => 'text/yaml'
    );
    
    /**
     * Whether or not to suppress http error codes and always return 200 ok, even in event of error. (This may be necessary for some clients, such as Adobe Flash)
     *
     * @var boolean
     */
    protected $suppress_response_codes = false;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
//      $this->dispatcher = $this->container->get('web_services.dispatcher');
        //$this->exceptionCodes = $this->container->getParameter('web_services.exception_codes');
    }
    
    /**
     * Register a listener for all kernel events to implement api-specific workflow.
     */
    public static function getSubscribedEvents() {
        return array(
            KernelEvents::CONTROLLER => array('onApiController', 1000),
            KernelEvents::EXCEPTION => array('onApiException', 1000),
            KernelEvents::RESPONSE => array('onApiResponse', 1000),
            KernelEvents::VIEW => array('onApiView', 1000),
            KernelEvents::TERMINATE => array('onApiTerminate', 1000),
        );
    }

    /**
     * Note that this event listener is NOT registered - it is called manually by the ApiBootstrapListener.
     *
     * @param GetResponseEvent $e 
     */
    public function onApiRequest(GetResponseEvent $e) {
        $request = $e->getRequest();
        
        //get default format
        $this->format = $this->container->hasParameter('ayamel.api.default_format') ? $this->container->getParameter('ayamel.api.default_format') : 'json';
        
        //now check for client-specified format overrides
        $this->format = $request->query->get('_format', $this->format);
        //TODO: check headers for specified format
        
        //check if we should suppress http response codes, and always default to 200
        $this->suppress_response_codes = $request->query->get('_suppress_codes', false);
        
        //make sure the request format is valid, exception if not
        if(!isset($this->formatHeaders[$this->format])) {
            $this->format = 'json';
            throw new HttpException(415);
        }
                
        //TODO (MAYBE): get api client instance, set in container... ?, if no client, throw 401 - or use plug into the Security component for this... depends
        
    }

    /**
     * Called once the controller to be used has been resolved.
     */
    public function onApiController(FilterControllerEvent $e) {
        //any auth logic if not integrated into security component, possibly profiling logic as well
        
        //TODO: check for format in the url
    }

    /**
     * Called if an exception was thrown at any point.
     */
    public function onApiException(GetResponseForExceptionEvent $e) {
        //handle exception body format
        $exception = $e->getException();
        
        //preserve specific http exception codes and messages, otherwise it's 500
        $realHttpErrorCode = $outgoingHttpStatusCode = ($exception instanceof HttpException) ? $exception->getStatusCode() : 500;
        if($this->suppress_response_codes == true) {
            $outgoingHttpStatusCode = 200;
        }
        $errorMessage = ($exception instanceof HttpException) ? (null != $exception->getMessage() ? $exception->getMessage() : Response::$statusTexts[$realHttpErrorCode]) : "Internal Server Error";
        
        $errorData = array(
            'response' => array(
                'code' => $realHttpErrorCode,
                'message' => $errorMessage,
            )
        );
        
        //inject exception data if we're in dev mode
        if('dev' === $this->container->get('kernel')->getEnvironment()) {
            $errorData['exception'] = array(
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => explode("#", $exception->getTraceAsString())
            );
        }
        
        //serialize error content into requested format
        $content = $this->container->get('serializer')->serialize($errorData, $this->format);
                                
        //build response
        $response = new Response($content, $outgoingHttpStatusCode, array('content-type' => $this->formatHeaders[$this->format]));
        
        $e->setResponse($response);
    }

    /**
     * Called when a response object has been resolved.
     */
    public function onApiResponse(FilterResponseEvent $e) {
        //if supression is active, always return 200 no matter what
        if($this->suppress_response_codes) {
            $response = $e->getResponse();
            $response->setStatusCode(200);
        }

        //TODO: compare returned response with request header accepted mimes, modify accordingly, and prepare the response (if not already done, it may be unnecessary)
        //$kernel->prepare($request, $response);
    }

    /**
     * Called when a controller does not return a response object.  Checks specifically for content structures expected by the resource API.
     */
    public function onApiView(GetResponseForControllerResultEvent $e) {
        $request = $e->getRequest();
        $data = $e->getControllerResult();
        
        //TODO: Implement an ApiView class, with cache/code/status controls that can be processed here

        //figure out meta status and code, and actual outgoing http response code
        $httpStatusCode = isset($data['response']['code']) ? $data['response']['code'] : 200;
        $data['response']['message'] = isset($data['response']['message']) ? $data['response']['message'] : Response::$statusTexts[$httpStatusCode];
        $outgoingStatusCode = $this->suppress_response_codes ? 200 : $httpStatusCode;
        
        //load serializer, encode response structure into requested format
        $content = $this->container->get('serializer')->serialize($data, $this->format);
        
        //build the response
        $response = new Response($content, $outgoingStatusCode, array('content-type' => $this->formatHeaders[$this->format]));
                
        $e->setResponse($response);
    }
    
    /**
     * Called after a response has already been sent.  Any shutdown/bookeeping functionality should initiate here.
     */
    public function onApiTerminate(PostResponseEvent $e) {

        //TODO: log the request, maybe do this via dispatching listeners instead of directly?
        
    }

}