<?php

namespace AC\WebServicesBundle\EventListener;

use Symfony\Component\EventDispatcher\Event;
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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AC\WebServicesBundle\Response\ServiceResponse;

/**
 * TODO: Need to implement ServiceResponse where possible
 * TODO: Need to make formatHeaders configurable
 * TODO: Need to implement ServiceException + configuration, + kernel listener for proper handling
 */


/**
 * A listener that monitors input/output for all REST api requests.  Enforces accept header, logs requests and handles exceptions thrown by Controllers.
 *
 * @author Evan Villemez
 */
class RestWorkflowSubscriber implements EventSubscriberInterface {
    
    const API_REQUEST = 'webservice.request';
    
    const API_EXCEPTION = 'webservice.exception';
    
    const API_RESPONSE = 'webservice.response';
    
    const API_TERMINATE = 'webservice.terminate';

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
    protected $defaultResponseFormat = 'json';
    
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
    
    private $responseFormat;
    
    private $isJsonp = false;
    
    /**
     * Whether or not to suppress http error codes and always return 200 ok, even in event of error. (This may be necessary for some clients, such as Adobe Flash)
     *
     * @var boolean
     */
    protected $suppress_response_codes = false;

    /**
     * Constructor needs container and a default response format.
     *
     * @param ContainerInterface $container 
     * @param string $defaultResponseFormat 
     */
    public function __construct(ContainerInterface $container, $defaultResponseFormat = 'json')
    {
        $this->container = $container;
        $this->defaultResponseFormat = $this->responseFormat = $defaultResponseFormat;
        
    }
    
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents() {
        return array(
            KernelEvents::EXCEPTION => array('onApiException', 1024),
            KernelEvents::RESPONSE => array('onApiResponse', 1024),
            KernelEvents::VIEW => array('onApiView', 1024),
            KernelEvents::TERMINATE => array('onApiTerminate', 1024),
        );
    }

    /**
     * Note that this event listener is NOT registered - it is called manually by the ApiBootstrapListener (TEMPORARY).
     *
     * @param GetResponseEvent $e 
     */
    public function onApiRequest(GetResponseEvent $e) {
        $request = $e->getRequest();
        
        //now check for best response format
        $this->responseFormat = $this->negotiateResponseFormat($request);
        
        //generic validation regarding request/response formats
        $this->validateRequest($request);
        
        //check if we should suppress http response codes, and always default to 200
        $this->suppress_response_codes = $request->query->get('_suppress_codes', false);
                
        $e->getDispatcher()->dispatch(self::API_REQUEST, $e);
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
        $content = $this->container->get('serializer')->serialize($errorData, $this->responseFormat);
                                
        //build response
        $response = new Response($content, $outgoingHttpStatusCode, array('content-type' => $this->formatHeaders[$this->responseFormat]));
        
        $e->setResponse($response);

        $e->getDispatcher()->dispatch(self::API_EXCEPTION, $e);
    }

    /**
     * Called when a response object has been resolved.
     */
    public function onApiResponse(FilterResponseEvent $e) {
        //if supression is active, always return 200 no matter what
        if($this->suppress_response_codes) {
            $response = $e->getResponse()->setStatusCode(200);
        }

        //TODO: compare returned response with request header accepted mimes, modify accordingly, and prepare the response (if not already done, it may be unnecessary)
        //$this->container->get('kernel')->prepare($request, $response); //do this here or elsewhere?
        $e->getDispatcher()->dispatch(self::API_RESPONSE, $e);
    }

    /**
     * Called when a controller does not return a response object.  Checks specifically for content structures expected by the resource API.
     */
    public function onApiView(GetResponseForControllerResultEvent $e)
    {
        $request = $e->getRequest();
        $data = $e->getControllerResult();
        
        if ($data instanceof ServiceResponse) {
            //TODO: Implement an ApiResponse class, with cache/code/status controls that can be processed here
        }        

        //figure out meta status and code, and actual outgoing http response code
        $httpStatusCode = isset($data['response']['code']) ? $data['response']['code'] : 200;
        $data['response']['message'] = isset($data['response']['message']) ? $data['response']['message'] : Response::$statusTexts[$httpStatusCode];
        $outgoingStatusCode = $this->suppress_response_codes ? 200 : $httpStatusCode;
        
        //load serializer, encode response structure into requested format
        $content = $this->container->get('serializer')->serialize($data, $this->responseFormat);
        
        //if JSONP, use _callback param
        if ($this->isJsonp) {
            $content = sprintf("%s(%s);", $this->jsonpCallback, $content);
        }
        
        //set the final response
        $e->setResponse(new Response($content, $outgoingStatusCode, array('content-type' => $this->formatHeaders[$this->responseFormat])));
    }
    
    /**
     * Called after a response has already been sent.  Any shutdown/bookeeping functionality should initiate here.
     */
    public function onApiTerminate(PostResponseEvent $e)
    {
        $e->getDispatcher()->dispatch(self::API_TERMINATE, $e);
    }
    
    
    protected function validateRequest(Request $request)
    {
        //check for jsonp, make sure it's valid if so
        if ('jsonp' === $this->responseFormat) {
            $this->responseFormat = 'json';
            $this->isJsonp = true;
            if (!$this->jsonpCallback = $request->query->get('_callback', false)) {
                throw new HttpException(400, "The [_callback] parameter was not specified, and is required for JSONP responses.");
            }
        }
 
        //make sure the request format is valid, exception if not
        if(!isset($this->formatHeaders[$this->responseFormat])) {
            throw new HttpException(415);
        }
        
    }
    
    public function negotiateResponseFormat(Request $request)
    {
        //TODO: eventual robust content negotiation here, for now just check query string
        $responseFormat = strtolower($request->query->get('_format', $this->defaultResponseFormat));
        
        return $responseFormat;
    }

}
