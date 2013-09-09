<?php

namespace Ayamel\SearchBundle\RabbitMQ;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;

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


            throw new \RuntimeException("Not implemented.");


        return true;
    }

}
