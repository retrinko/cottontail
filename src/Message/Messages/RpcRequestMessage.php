<?php


namespace Retrinko\CottonTail\Message\Messages;


use PhpAmqpLib\Message\AMQPMessage;
use Retrinko\CottonTail\Exceptions\MessageException;
use Retrinko\CottonTail\Message\Payloads\RpcRequestPayload;

class RpcRequestMessage extends BasicMessage
{
    /**
     * @var array
     */
    protected $requiredProperties = [self::PROPERTY_TYPE,
                                     self::PROPERTY_CORRELATION_ID,
                                     self::PROPERTY_CONTENT_TYPE];

    /**
     * @param string $body
     * @param array $properties
     */
    public function __construct($body = '', $properties = [])
    {
        parent::__construct($body, $properties);
        $this->setType(static::TYPE_RPC_REQUEST);
    }

    /**
     * @param string $prefix
     *
     * @return string
     */
    public function generateCorrelationId($prefix = '')
    {
        return ('' == trim($prefix))
            ? uniqid()
            : trim($prefix) . '.' . uniqid();
    }

    /**
     * @return RpcRequestPayload
     */
    public function getPayload()
    {
        $data = parent::getPayload();

        return new RpcRequestPayload($data);
    }

    /**
     * @param AMQPMessage $amqpMessage
     *
     * @throws MessageException
     */
    protected function checkRequiredPropertiesPresence(AMQPMessage $amqpMessage)
    {
        parent::checkRequiredPropertiesPresence($amqpMessage);
        if (static::TYPE_RPC_REQUEST != $amqpMessage->get(static::PROPERTY_TYPE))
        {
            throw MessageException::wrongMessageType($this->getType(),
                                                     static::TYPE_RPC_REQUEST);
        }
    }


}