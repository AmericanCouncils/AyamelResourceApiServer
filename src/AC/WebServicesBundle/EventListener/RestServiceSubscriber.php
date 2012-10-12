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
 * TODO: Need to make formatHeaders configurable
 */


/**
 * A listener that monitors input/output for all REST api requests.
 *
 * @author Evan Villemez
 */
class RestServiceSubscriber implements EventSubscriberInterface {
    
    const API_REQUEST = 'ac.webservice.request';
    
    const API_EXCEPTION = 'ac.webservice.exception';
    
    const API_RESPONSE = 'ac.webservice.response';
    
    const API_TERMINATE = 'ac.webservice.terminate';

    /**
     * @var Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;
    
    
    /**
     * Array of content-type headers to used, keyed by requested serialization format.
     *
     * @var array
     */
    protected $formatHeaders = array(
        'json' => 'application/json',
        'xml' => 'application/xml',
        'yml' => 'text/yaml',
        'html' => 'text/html'
    );
    
    private $defaultResponseFormat;

    private $responseFormat;
    
    private $allowCodeSuppression;
    
    private $includeResponseData;
    
    private $exceptionMap;
    
    private $includeDevExceptions;

    private $isJsonp = false;

    private $jsonpCallback;    

    private $suppress_response_codes = false;

    /**
     * Constructor needs container and some behavior configs.
     *
     * @param ContainerInterface $container 
     * @param string $defaultResponseFormat 
     */
    public function __construct(ContainerInterface $container, $defaultResponseFormat, $includeResponseData, $allowCodeSuppression, $includeDevExceptions, $exceptionMap = array())
    {
        $this->container = $container;
        $this->defaultResponseFormat = $this->responseFormat = $defaultResponseFormat;
        $this->includeResponseData = $includeResponseData;
        $this->exceptionMap = $exceptionMap;
        $this->allowCodeSuppression = $allowCodeSuppression;
        $this->includeDevExceptions = $includeDevExceptions;
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
     * Note that this event listener is NOT registered - it is called manually by the ApiBootstrapListener (for now).
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
        $this->suppress_response_codes = ($this->allowCodeSuppression) ? $request->query->get('_suppress_codes', false) : false;
                
        $e->getDispatcher()->dispatch(self::API_REQUEST, $e);
    }

    /**
     * Called if an exception was thrown at any point.
     */
    public function onApiException(GetResponseForExceptionEvent $e) {
        //notify of generic API error, return early if a response gets set
        $e->getDispatcher()->dispatch(self::API_EXCEPTION, $e);
        if ($e->getResponse()) {
            return;
        }
        
        //handle exception body format
        $exception = $e->getException();
        $exceptionClass = get_class($exception);
        
        //preserve specific http exception codes and messages, otherwise it's 500
        $realHttpErrorCode = $outgoingHttpStatusCode = 500;
        $errorMessage = "Internal Server Error";
        if ($exception instanceof HttpException) {
            $realHttpErrorCode = $outgoingHttpStatusCode = $exception->getStatusCode();
            $errorMessage = ($exception->getMessage()) ? $exception->getMessage() : Response::$statusTexts[$realHttpErrorCode];
        } elseif (isset($this->exceptionMap[$exceptionClass])) {
            //check exception map for overrides
            $realHttpErrorCode = $this->exceptionMap[0];
            $errorMessage =
                (isset($this->exceptionMap[$exceptionClass][1]))
                ? $this->exceptionMap[$exceptionClass][1]
                : Response::$statusTexts[$realHttpErrorCode];
        }

        //set generic error data
        $errorData = array(
            'response' => array(
                'code' => $realHttpErrorCode,
                'message' => $errorMessage,
            )
        );
        
        //inject exception data if we're in dev mode and enabled
        if($this->includeDevExceptions && 'dev' === $this->container->get('kernel')->getEnvironment()) {
            $errorData['exception'] = array(
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => explode("#", $exception->getTraceAsString()),
            );
        }
        
        //serialize error content into requested format, if format is not supported by the serializer, do json
        $this->responseFormat = (in_array($this->responseFormat, array('json','xml','yml'))) ? $this->responseFormat : 'json';
        $content = $this->container->get('serializer')->serialize($errorData, $this->responseFormat);
        
        //check for code suppression
        if($this->suppress_response_codes) {
            $outgoingHttpStatusCode = 200;
        }
        
        //set response
        $e->setResponse(new Response($content, $outgoingHttpStatusCode, array('content-type' => $this->formatHeaders[$this->responseFormat])));
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
        
        //should we handle this return at all?
        if (!$data instanceof ServiceResponse && !is_array($data)) {
            return;
        }
        
        //set defaults
        $responseCode = 200;
        $headers = array();
        $template = null;
        
        //check specifically for service response
        if ($data instanceof ServiceResponse) {
            $responseCode = $data->getResponseCode();
            $headers = $data->getResponseHeaders();
            $data = $data->getResponseData();
        }

        $outgoingStatusCode = $this->suppress_response_codes ? 200 : $responseCode;
        
        //inject response data?
        if ($this->includeResponseData && is_array($data) && !isset($data['response'])) {
            $data['response'] = array(
                'code' => $responseCode,
                'message' => Response::$statusTexts[$responseCode],
            );
        }

        //render content accordingly
        if ($template) {
            $content = $this->container->get('templating')->render($template, $data);
        } else {
            //load serializer, encode response structure into requested format
            $content = $this->container->get('serializer')->serialize($data, $this->responseFormat);
        
            //if JSONP, use _callback param
            if ($this->isJsonp) {
                $content = sprintf("%s(%s);", $this->jsonpCallback, $content);
            }
        }
        
        //merge headers
        $headers = array_merge($headers, array('content-type' => $this->formatHeaders[$this->responseFormat]));
        
        //set the final response
        $e->setResponse(new Response($content, $outgoingStatusCode, $headers));
    }
    
    /**
     * Called after a response has already been sent.
     */
    public function onApiTerminate(PostResponseEvent $e)
    {
        $e->getDispatcher()->dispatch(self::API_TERMINATE, $e);
    }
    
    protected function validateRequest(Request $request)
    {
        //check for jsonp, make sure it's valid
        if ('jsonp' === $this->responseFormat) {
            $this->responseFormat = 'json';
            $this->isJsonp = true;
            if (!$this->jsonpCallback = $request->query->get('_callback', false)) {
                throw new HttpException(400, "The [_callback] parameter is missing, and is required for JSONP responses.");
            }
            
            if ("GET" !== $request->getMethod()) {
                throw new HttpException(400, "JSONP can only be used with GET requests.");
            }
        }
 
        //make sure the request format is valid, exception if not
        if(!isset($this->formatHeaders[$this->responseFormat])) {
            throw new HttpException(415);
        }
    }
    
    protected function negotiateResponseFormat(Request $request)
    {
        //TODO: eventual robust content negotiation here, for now just check request for explicit declaration
        $responseFormat = strtolower($request->get('_format', $this->defaultResponseFormat));
        
        return $responseFormat;
    }
}
