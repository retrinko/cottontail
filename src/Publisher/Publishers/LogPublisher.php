<?php


namespace Retrinko\CottonTail\Publisher\Publishers;


use PhpAmqpLib\Message\AMQPMessage;
use Retrinko\CottonTail\Message\Messages\BasicMessage;

class LogPublisher extends BasicPublisher
{
    const PROPERTY_TYPE_VALUE = 'log';
    /**
     * @param string $data
     *
     * @return AMQPMessage
     */
    protected function composeAMQPMessage($data)
    {
        $message = new AMQPMessage($data);
        $message->set(BasicMessage::PROPERTY_TYPE, self::PROPERTY_TYPE_VALUE);

        return $message;
    }
}