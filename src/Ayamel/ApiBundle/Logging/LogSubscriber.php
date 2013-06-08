<?php
namespace Ayamel\ApiBundle\Logging;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Log all API requests to Mongo AFTER the response has been sent.
 *
 * @package AyamelApiBundle
 * @author Evan Villemez
 */
class LogSubscriber implements EventSubscriberInterface
{
    private $container;
    private $logMessage = false;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'ac.webservice.request' => array('onApiRequest'),
            'ac.webservice.exception' => array('onApiException'),
            'ac.webservice.response' => array('onApiResponse'),
            'ac.webservice.terminate' => array('onApiTerminate')
        );
    }

    public function onApiRequest($e)
    {
        $req = $e->getRequest();

        $this->start = isset($GLOBALS['__start']) ? $GLOBALS['__start'] : microtime(true);

        $this->logMessage = array(
            'time' => new \DateTime('now'),
            'request' => array(
                'uri' => $req->getPathInfo(),
                'pattern' => $req->get('_route')->getPattern(),
                'method' => $req->getMethod(),
            ),
        );

        //set client
        if ($this->container->has('ayamel.api.client')) {
            $this->logMessage['client'] = $this->container->get('ayamel.api.client')->getId();
        } else {
            $this->logMessage['client'] = $req->getClientIp();
        }

    }

    public function onApiException($e)
    {
        if ($this->logMessage) {
            $exception = $e->getException();
            $this->logMessage['exception'] = array(
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
            );
        }
    }

    public function onApiResponse($e)
    {
        if ($this->logMessage) {

        }
    }

    public function onApiTerminate($e)
    {
        if ($this->logMessage) {
            $response = $e->getResponse();
            $request = $e->getRequest();
            
            $this->logMessage['response'] = array(
                'status' => $response->getStatusCode(),
                'type' => $response->headers->get('Content-Type'),
                'length' => $response->headers->get('Content-Length')
            );
            $this->logMessage['memory'] = memory_get_peak_usage();
            $this->logMessage['duration'] = microtime(true) - $this->start;

            $this->container->get('mongodb.odm.default_connection')->ayamel->logs->insert($this->logMessage);
        }
    }
}
