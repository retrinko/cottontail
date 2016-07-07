<?php


namespace Retrinko\CottonTail\Publisher\Publishers;


use PhpAmqpLib\Message\AMQPMessage;
use Retrinko\CottonTail\Message\Messages\BasicMessage;

class EventPublisher extends BasicPublisher
{
    const PROPERTY_TYPE_VALUE = 'event.';

    /**
     * @var string
     */
    protected $eventType;

    /**
     * @param string $eventType
     *
     * @return $this
     */
    public function setEventType($eventType)
    {
        $this->eventType = (string)$eventType;

        return $this;
    }

    /**
     * @param string $data
     *
     * @return AMQPMessage
     */
    protected function composeAMQPMessage($data)
    {
        $message = new AMQPMessage($data);
        $eventType = isset($this->eventType) 
            ? self::PROPERTY_TYPE_VALUE. '.' . $this->eventType 
            : self::PROPERTY_TYPE_VALUE;
        $message->set(BasicMessage::PROPERTY_TYPE, $eventType);

        return $message;
    }
}