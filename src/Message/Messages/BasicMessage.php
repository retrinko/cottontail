<?php


namespace Retrinko\CottonTail\Message\Messages;

use Retrinko\CottonTail\Exceptions\MessageException;
use Retrinko\CottonTail\Message\MessageInterface;
use Retrinko\CottonTail\Message\PayloadInterface;
use Retrinko\CottonTail\Message\Payloads\DefaultPayload;
use Retrinko\Serializer\SerializerFactory;

class BasicMessage implements MessageInterface
{
    /**
     * @var string
     */
    protected $body;
    /**
     * @var array
     */
    protected $properties = [];
    /**
     * @var array
     */
    protected $requiredProperties = [];
    /**
     * @var mixed
     */
    protected $originalMessage;

    /**
     * @param string $body
     * @param array $properties
     *
     * @throws MessageException
     */
    public function __construct($body = '', array $properties = [])
    {
        $this->checkRequiredPropertiesPresence($properties);
        $this->body = $body;
        $this->properties = $properties;

        // Populate undefined properties with default vaules.
        if ('' == $this->getTimestamp())
        {
            $this->setTimestamp(time());
        }
        if ('' == $this->getType())
        {
            $this->setType(MessageInterface::TYPE_BASIC);
        }
        if ('' == $this->getContentType())
        {
            $this->setContentType(MessageInterface::CONTENT_TYPE_PLAIN_TEXT);
        }
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->getProperty(MessageInterface::PROPERTY_TIMESTAMP);
    }

    /**
     * @param string $name
     * @param string $default
     *
     * @return string
     */
    public function getProperty($name, $default = '')
    {
        return isset($this->properties[$name]) ? $this->properties[$name] : $default;
    }

    /**
     * @param int $timestamp
     *
     * @return $this
     */
    public function setTimestamp($timestamp)
    {
        return $this->setProperty(MessageInterface::PROPERTY_TIMESTAMP, $timestamp);
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function setProperty($name, $value)
    {
        $this->properties[$name] = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->getProperty(MessageInterface::PROPERTY_TYPE);
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        return $this->setProperty(MessageInterface::PROPERTY_TYPE, $type);
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->getProperty(MessageInterface::PROPERTY_CONTENT_TYPE);
    }

    /**
     * @param string $contentType
     *
     * @return $this
     */
    public function setContentType($contentType)
    {
        return $this->setProperty(MessageInterface::PROPERTY_CONTENT_TYPE, $contentType);
    }

    /**
     * @param array $properties
     *
     * @throws MessageException
     */
    protected function checkRequiredPropertiesPresence(array $properties)
    {
        foreach ($this->getRequiredProperties() as $requiredProperty)
        {
            if (!array_key_exists($requiredProperty, $properties))
            {
                throw MessageException::requiredPropertyMissing($requiredProperty);
            }
        }
    }

    /**
     * @return array
     */
    public function getRequiredProperties()
    {
        return $this->requiredProperties;
    }

    /**
     * @param array $requiredProperties
     *
     * @return $this
     */
    public function setRequiredProperties($requiredProperties)
    {
        $this->requiredProperties = $requiredProperties;

        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     *
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param array $properties
     *
     * @return $this
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasProperty($name)
    {
        $value = $this->getProperty($name, null);

        return !is_null($value);
    }

    /**
     * @return string
     */
    public function getAppId()
    {
        return $this->getProperty(MessageInterface::PROPERTY_APP_ID);
    }

    /**
     * @param string $appId
     *
     * @return $this
     */
    public function setAppId($appId)
    {
        return $this->setProperty(MessageInterface::PROPERTY_APP_ID, $appId);
    }

    /**
     * @return string
     */
    public function getCorrelationId()
    {
        return $this->getProperty(MessageInterface::PROPERTY_CORRELATION_ID);
    }

    /**
     * @param string $correlationId
     *
     * @return $this
     */
    public function setCorrelationId($correlationId)
    {
        return $this->setProperty(MessageInterface::PROPERTY_CORRELATION_ID, $correlationId);
    }

    /**
     * @return int miliseconds
     */
    public function getExpiration()
    {
        return $this->getProperty(MessageInterface::PROPERTY_EXPIRATION);
    }

    /**
     * @param int $expiration miliseconds
     *
     * @return $this
     */
    public function setExpiration($expiration)
    {
        return $this->setProperty(MessageInterface::PROPERTY_EXPIRATION, $expiration);
    }

    /**
     * @return string
     */
    public function getReplyTo()
    {
        return $this->getProperty(MessageInterface::PROPERTY_REPLY_TO);
    }

    /**
     * @param string $replyTo
     *
     * @return $this
     */
    public function setReplyTo($replyTo)
    {
        return $this->setProperty(MessageInterface::PROPERTY_REPLY_TO, $replyTo);
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->getProperty(MessageInterface::PROPERTY_USER_ID);
    }

    /**
     * @param string $userId
     *
     * @return $this
     */
    public function setUserId($userId)
    {
        return $this->setProperty(MessageInterface::PROPERTY_USER_ID, $userId);
    }

    /**
     * @return int
     */
    public function getDeliveryMode()
    {
        return $this->getProperty(MessageInterface::PROPERTY_DELIVERY_MODE);
    }

    /**
     * @param int $deliveryMode 1: AMQPMessage::DELIVERY_MODE_NON_PERSISTENT, 2: AMQPMessage::DELIVERY_MODE_PERSISTENT
     *
     * @return BasicMessage
     */
    public function setDeliveryMode($deliveryMode)
    {
        return $this->setProperty(MessageInterface::PROPERTY_DELIVERY_MODE, $deliveryMode);
    }

    /**
     * @param string $string
     * @param string $contentType
     *
     * @return $this
     */
    public function loadString($string, $contentType = MessageInterface::CONTENT_TYPE_PLAIN_TEXT)
    {
        $this->setBody($string);
        $this->setContentType($contentType);

        return $this;
    }

    /**
     * @return DefaultPayload
     * @throws \Retrinko\Serializer\Exceptions\Exception
     */
    public function getPayload()
    {
        $serializer = SerializerFactory::bySerializedContentType($this->getContentType());

        return new DefaultPayload($serializer->unserialize($this->getBody()));
    }

    /**
     * @param PayloadInterface $data
     * @param null $contentType
     *
     * @throws \Retrinko\Serializer\Exceptions\Exception
     */
    public function setPayload(PayloadInterface $data, $contentType = null)
    {
        $contentType = is_null($contentType) ? $this->getContentType() : $contentType;
        $this->setBody($data->serialize(SerializerFactory::bySerializedContentType($contentType)));
    }

    /**
     * @param mixed $message
     *
     * @return void
     */
    public function setOriginalMessage($message)
    {
        $this->originalMessage = $message;
    }

    /**
     * @return mixed
     */
    public function getOriginalMessage()
    {
        return $this->originalMessage;
    }
}