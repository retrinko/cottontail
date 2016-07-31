<?php
namespace Retrinko\CottonTail\Connectors;

use Psr\Log\LoggerAwareInterface;
use Retrinko\CottonTail\Message\Adaptors\MessageAdaptorInterface;
use Retrinko\CottonTail\Message\MessageInterface;

interface ConnectorInterface extends LoggerAwareInterface
{
    /**
     * @return MessageAdaptorInterface
     */
    public function getMesageAdaptor();

    /**
     * @return void
     */
    public function closeConnection();

    /**
     * @param bool $forceReconnection
     *
     * @return void
     */
    public function connect($forceReconnection = false);

    /**
     * @param string $queueName
     *
     * @return string
     */
    public function declareQueue($queueName = '');

    /**
     * @param MessageInterface $message
     * @param string $exchangeName Destination xchange name.
     * @param string $routingKeyOrQueueName Routing key (if xchange is set) or the destination
     *     queue name.
     *
     * @return void
     */
    public function basicPublish(MessageInterface $message, $exchangeName = '',
                                 $routingKeyOrQueueName = '');

    /**
     * @param string $queueName
     * @param callable $callback
     */
    public function basicConsume($queueName, $callback);

    /**
     * @param int $timeOut Time in seconds (0 = infinite wait).
     */
    public function wait($timeOut = 0);

    /**
     * @param int $prefechCount
     */
    public function defineQoS($prefechCount = 1);

    /**
     * @return array
     */
    public function getChannelCallbacks();

    /**
     * @param MessageInterface $amqpMessage
     */
    public function basicAck(MessageInterface $amqpMessage);

    /**
     * @param MessageInterface $message
     * @param bool $requeueMessage
     */
    public function basicReject(MessageInterface $message, $requeueMessage = false);

    /**
     * @param MessageInterface $message
     */
    public function basicCancel(MessageInterface $message);
}