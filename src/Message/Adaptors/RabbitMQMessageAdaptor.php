<?php


namespace Retrinko\CottonTail\Message\Adaptors;

use PhpAmqpLib\Message\AMQPMessage;
use Retrinko\CottonTail\Exceptions\MessageException;
use Retrinko\CottonTail\Message\MessageInterface;
use Retrinko\CottonTail\Message\Messages\BasicMessage;
use Retrinko\CottonTail\Message\Messages\RpcRequestMessage;
use Retrinko\CottonTail\Message\Messages\RpcResponseMessage;

class RabbitMQMessageAdaptor implements MessageAdaptorInterface
{
    /**
     * @param AMQPMessage $amqpMessage
     *
     * @return BasicMessage|RpcRequestMessage|RpcResponseMessage
     * @throws MessageException
     */
    public function toMessageInterface($amqpMessage)
    {
        if (!$amqpMessage instanceof AMQPMessage)
        {
            throw MessageException::wrongMessageType(get_class($amqpMessage), 'AMQPMessage');
        }

        try
        {
            $msgType = $amqpMessage->get(MessageInterface::PROPERTY_TYPE);
        }
        catch (\Exception $e)
        {
            // Type is not defined in AMQPMessage properties => Load BasicMessage.
            $msgType = '';
        }

        // Instance proper message type
        switch ($msgType)
        {
            case MessageInterface::TYPE_RPC_RESPONSE:
                $message = new RpcResponseMessage($amqpMessage->body,
                                                  $amqpMessage->get_properties());
                break;
            case MessageInterface::TYPE_RPC_REQUEST:
                $message = new RpcRequestMessage($amqpMessage->body,
                                                 $amqpMessage->get_properties());
                break;
            case MessageInterface::TYPE_BASIC:
            default:
                $message = new BasicMessage($amqpMessage->body,
                                            $amqpMessage->get_properties());
                break;
        }

        // Set the original message
        $message->setOriginalMessage($amqpMessage);

        return $message;
    }

    /**
     * @param MessageInterface $message
     *
     * @return AMQPMessage
     */
    public function fromMessageInterface(MessageInterface $message)
    {
        return new AMQPMessage($message->getBody(), $message->getProperties());
    }
}