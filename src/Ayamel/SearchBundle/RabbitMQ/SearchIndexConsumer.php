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
        $batch = $this->container->getParameter('ayamel.search.elastica_resource_provider.batch');
        $logger = $this->container->get('monolog.logger.search');
        
        $ids = isset($body['ids']) ? $body['ids'] : $body['id'];

        $logger->info(sprintf("Attempting to asynchronously index [%s]", implode(', ', $ids)));

        try {
            if (isset($body['id'])) {
                $this->container->get('ayamel.search.resource_indexer')->indexResource($body['id']);
                $logger->info(sprintf('Indexed [%s]', $body['id']));
            }

            if (isset($body['ids'])) {
                $this->container->get('ayamel.search.resource_indexer')->indexResources($body['ids'], $batch);
                $logger->info(sprintf('Indexed multiple [%s]', $body['ids']));
            }
        } catch (IndexException $e) {

            if ($e instanceof BulkIndexException) {
                foreach ($e->getMessages() as $id => $message) {
                    $logger->info(sprintf('Indexing skipped [%s]: %s', $id, $message));
                }
            } else {
                $logger->info(sprintf('Indexing skipped [%s]: %s', $body['id'], $e->getMessage()));
            }
        }

        // Always return true to drop the message from the queue; whether or not
        // the indexing succeeded, it will almost certainly turn out the same way
        // if we try it again. If the resource needs to be modified to be indexed,
        // then that modification will trigger a reindexing event anyways.
        return true;
    }
}
