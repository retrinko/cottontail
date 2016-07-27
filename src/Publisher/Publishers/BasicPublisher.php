<?php


namespace Retrinko\CottonTail\Publisher\Publishers;


use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Retrinko\CottonTail\Exceptions\PublisherException;
use Retrinko\CottonTail\Message\Messages\BasicMessage;
use Retrinko\CottonTail\Publisher\PublisherInterface;
use Retrinko\CottonTail\RabbitMQ\Connector;
use Retrinko\Serializer\Serializers\JsonSerializer;
use Retrinko\Serializer\Traits\SerializerAwareTrait;

class BasicPublisher implements PublisherInterface
{
    use LoggerAwareTrait;
    use SerializerAwareTrait;

    /**
     * @var Connector
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
     * @param string $server
     * @param int $port
     * @param string $user
     * @param string $pass
     * @param string $vhost
     * @param array $sslOptions
     */
    public function __construct($server, $port, $user, $pass, $vhost = '/', $sslOptions = [])
    {
        $this->logger = new NullLogger();
        $this->serializer = new JsonSerializer();
        $this->connector = new Connector($server, $port, $user, $pass, $vhost, $sslOptions);
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
        $amqpMessage = $this->composeAMQPMessage($data);

        // Publish message
        $this->publishAMQPMessage($amqpMessage);

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
     * @param mixed $data
     *
     * @return AMQPMessage
     */
    protected function composeAMQPMessage($data)
    {
        $message = new BasicMessage($this->getSerializer()->serialize($data));
        $message->setContentType($this->getSerializer()->getSerializedContentType());

        return $message->toAMQPMessage();
    }

    /**
     * @param AMQPMessage $amqpMessage
     */
    protected function publishAMQPMessage(AMQPMessage $amqpMessage)
    {
        // Connect
        $this->connector->connect();
        // Publish message
        if (true == $this->publishOnQueue)
        {
            $this->connector->basicPublish($amqpMessage, '', $this->publicationsQueue);
        }
        else
        {
            $this->connector->basicPublish($amqpMessage, $this->publicationsExchange,
                                           $this->publicationsRoutingKey);
        }
    }

}