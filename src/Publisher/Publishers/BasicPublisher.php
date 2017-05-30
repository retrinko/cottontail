<?php


namespace Retrinko\CottonTail\Publisher\Publishers;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Retrinko\CottonTail\Connectors\ConnectorInterface;
use Retrinko\CottonTail\Exceptions\PublisherException;
use Retrinko\CottonTail\Message\MessageInterface;
use Retrinko\CottonTail\Message\Messages\BasicMessage;
use Retrinko\CottonTail\Publisher\PublisherInterface;
use Retrinko\CottonTail\Serializer\SerializerAwareTrait;
use Retrinko\CottonTail\Serializer\Serializers\JsonSerializer;

class BasicPublisher implements PublisherInterface
{
    use LoggerAwareTrait;
    use SerializerAwareTrait;

    /**
     * @var ConnectorInterface
     */
    protected $connector;
    /**
     * @var string
     */
    protected $publicationsQueue;
    /**
     * @var string
     */
    protected $publicationsExchange;
    /**
     * @var string
     */
    protected $publicationsRoutingKey;
    /**
     * @var bool
     */
    protected $publishOnQueue = false;
    /**
     * @var bool
     */
    protected $publishOnExchange = false;
    /**
     * @var int
     */
    protected $deliveryMode = MessageInterface::DELIVERY_MODE_NON_PERSISTENT;

    /**
     * @param ConnectorInterface $connector
     */
    public function __construct(ConnectorInterface $connector)
    {
        $this->logger = new NullLogger();
        $this->serializer = new JsonSerializer();
        $this->connector = $connector;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->connector->setLogger($logger);
    }

    /**
     * @param int $deliveryMode MessageInterface::DELIVERY_MODE_NON_PERSISTENT|MessageInterface::DELIVERY_MODE_PERSISTENT
     */
    public function setDeliveryMode($deliveryMode)
    {
        $this->deliveryMode = $deliveryMode;
    }

    /**
     * @return int
     */
    public function getDeliveryMode()
    {
        return $this->deliveryMode;
    }

    /**
     * @param string $queueNameOrExchangeName
     * @param string $exchangeRoutingKey
     *
     * @return $this
     */
    public function setDestination($queueNameOrExchangeName, $exchangeRoutingKey = '')
    {
        if ('' == $exchangeRoutingKey)
        {
            $this->publishOnQueue = true;
            $this->publishOnExchange = false;
            $this->publicationsQueue = $queueNameOrExchangeName;
        }
        else
        {
            $this->publishOnQueue = false;
            $this->publishOnExchange = true;
            $this->publicationsExchange = $queueNameOrExchangeName;
            $this->publicationsRoutingKey = $exchangeRoutingKey;
        }

        return $this;
    }

    /**
     * @param mixed $data
     * @param bool $closeConnectionAfterPublish
     *
     * @throws PublisherException
     */
    public function publish($data, $closeConnectionAfterPublish = false)
    {
        $this->checkDestination();

        // Compose message
        $message = new BasicMessage($this->getSerializer()->serialize($data));
        $message->setDeliveryMode($this->getDeliveryMode());
        $message->setContentType($this->getSerializer()->getSerializedContentType());

        // Publish message
        $this->publishMessage($message);

        // Close connection if needed
        if (true == $closeConnectionAfterPublish)
        {
            $this->connector->closeConnection();
        }
    }

    /**
     * @throws PublisherException
     */
    protected function checkDestination()
    {
        if (false == $this->publishOnExchange && false == $this->publishOnQueue)
        {
            throw PublisherException::noDestinationSet();
        }
    }

    /**
     * @param MessageInterface $message
     */
    protected function publishMessage(MessageInterface $message)
    {
        // Connect
        $this->connector->connect();

        // Publish message
        if (true == $this->publishOnQueue)
        {
            $this->connector->basicPublish($message, '', $this->publicationsQueue);
        }
        else
        {
            $this->connector->basicPublish($message, $this->publicationsExchange,
                                           $this->publicationsRoutingKey);
        }
    }

}