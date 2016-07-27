<?php


namespace Retrinko\CottonTail\Message\Messages;


use PhpAmqpLib\Message\AMQPMessage;
use Retrinko\CottonTail\Exceptions\MessageException;
use Retrinko\CottonTail\Message\MessageInterface;
use Retrinko\CottonTail\Message\Payloads\RpcResponsePayload;

class RpcResponseMessage extends BasicMessage
{
    /**
     * @var array
     */
    protected $requiredProperties = [MessageInterface::PROPERTY_TYPE,
                                     MessageInterface::PROPERTY_CORRELATION_ID,
                                     MessageInterface::PROPERTY_CONTENT_TYPE];

    /**
     * @param string $body
     * @param array $properties
     */
    public function __construct($body = '', $properties = [])
    {
        parent::__construct($body, $properties);
        $this->setType(MessageInterface::TYPE_RPC_RESPONSE);
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
        if (MessageInterface::TYPE_RPC_RESPONSE != $amqpMessage->get(MessageInterface::PROPERTY_TYPE))
        {
            throw MessageException::wrongMessageType($this->getType(),
                                                     MessageInterface::TYPE_RPC_REQUEST);
        }
    }

}