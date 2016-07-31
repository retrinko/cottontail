<?php
namespace Retrinko\CottonTail\Connectors;

use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareInterface;

interface ConnectorInterface extends LoggerAwareInterface
{
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
     * @param AMQPMessage $amqpMessage
     * @param string $exchangeName Destination xchange name.
     * @param string $routingKeyOrQueueName Routing key (if xchange is set) or the destination
     *     queue name.
     *
     * @return void
     */
    public function basicPublish(AMQPMessage $amqpMessage, $exchangeName = '',
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
     * @param AMQPMessage $message
     */
    public function basicAck(AMQPMessage $message);

    /**
     * @param AMQPMessage $message
     * @param bool $requeueMessage
     */
    public function basicReject(AMQPMessage $message, $requeueMessage = false);

    /**
     * @param AMQPMessage $message
     */
    public function basicCancel(AMQPMessage $message);
}