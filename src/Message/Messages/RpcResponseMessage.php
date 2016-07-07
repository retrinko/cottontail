<?php


namespace Retrinko\CottonTail\Message\Messages;


use PhpAmqpLib\Message\AMQPMessage;
use Retrinko\CottonTail\Exceptions\MessageException;
use Retrinko\CottonTail\Message\Payloads\RpcResponsePayload;

class RpcResponseMessage extends BasicMessage
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
        $this->setType(static::TYPE_RPC_RESPONSE);
    }

    /**
     * @return RpcResponsePayload
     */
    public function getPayload()
    {
        $data = parent::getPayload();

        return new RpcResponsePayload($data);
    }

    /**
     * @param AMQPMessage $amqpMessage
     *
     * @throws MessageException
     */
    protected function checkRequiredPropertiesPresence(AMQPMessage $amqpMessage)
    {
        parent::checkRequiredPropertiesPresence($amqpMessage);
        if (static::TYPE_RPC_RESPONSE != $amqpMessage->get(static::PROPERTY_TYPE))
        {
            throw MessageException::wrongMessageType($this->getType(),
                                                     static::TYPE_RPC_REQUEST);
        }
    }

}