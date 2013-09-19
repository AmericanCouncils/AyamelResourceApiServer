<?php

namespace Ayamel\SearchBundle\RabbitMQ;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Ayamel\SearchBundle\Exception\IndexException;
use Ayamel\SearchBundle\Exception\BulkIndexException;

/**
 * Consumes messages via RabbitMQ to rebuild the search index
 * for Resources.
 *
 * @package AyamelSearchBundle
 * @author Evan Villemez
 */
class SearchIndexConsumer implements ConsumerInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Rebuild search index for Resources.
     *
     * @param AMQPMessage $msg
     */
    public function execute(AMQPMessage $msg)
    {
        $body = unserialize($msg->body);
        $batch = $this->container->get('ayamel.search.elastica_resource_provider.batch');

        try {
            if (isset($body['id'])) {
                $this->container->get('ayamel.search.indexer')->indexResource($body['id']);
            }
            
            if (isset($body['ids'])) {
                $this->container->get('ayamel.search.indexer')->indexResources($body['ids'], $batch);
            }
            
            return true;
        } catch (IndexException $e) {
            if ($e instanceof BulkIndexException) {
                return true;
            }
            
            return false;
        }
    }
}
