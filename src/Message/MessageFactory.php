<?php


namespace Retrinko\CottonTail\Message;


use PhpAmqpLib\Message\AMQPMessage;
use Retrinko\CottonTail\Message\Messages\BasicMessage;
use Retrinko\CottonTail\Message\Messages\RpcRequestMessage;
use Retrinko\CottonTail\Message\Messages\RpcResponseMessage;

class MessageFactory
{
    /**
     * @param AMQPMessage $amqpMessage
     *
     * @return BasicMessage|RpcRequestMessage|RpcResponseMessage
     *
     */
    public static function byAMQPMessage(AMQPMessage $amqpMessage)
    {
        try
        {
            $msgType = $amqpMessage->get(BasicMessage::PROPERTY_TYPE);
        }
        catch (\Exception $e)
        {
            // No type defined in AMQPMessage properties. Load BasicMessage.
            $msgType = '';
        }

        switch ($msgType)
        {
            case BasicMessage::TYPE_RPC_RESPONSE:
                $message = RpcResponseMessage::loadAMQPMessage($amqpMessage);
                break;
            case BasicMessage::TYPE_RPC_REQUEST:
                $message = RpcRequestMessage::loadAMQPMessage($amqpMessage);
                break;
            case BasicMessage::TYPE_PLAIN_TEXT:
            default:
                $message = BasicMessage::loadAMQPMessage($amqpMessage);
                break;
        }

        return $message;
    }
}