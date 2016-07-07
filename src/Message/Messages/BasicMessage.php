<?php


namespace Retrinko\CottonTail\Message\Messages;

use PhpAmqpLib\Message\AMQPMessage;
use Retrinko\CottonTail\Exceptions\MessageException;
use Retrinko\CottonTail\Message\PayloadInterface;
use Retrinko\Serializer\SerializerFactory;

class BasicMessage
{
    const PROPERTY_CONTENT_TYPE   = 'content_type';
    const PROPERTY_CORRELATION_ID = 'correlation_id';
    const PROPERTY_REPLY_TO       = 'reply_to';
    const PROPERTY_EXPIRATION     = 'expiration';
    const PROPERTY_TIMESTAMP      = 'timestamp';
    const PROPERTY_USER_ID        = 'user_id';
    const PROPERTY_APP_ID         = 'app_id';
    const PROPERTY_TYPE           = 'type';

    const TYPE_RPC_REQUEST  = 'rpc-request';
    const TYPE_RPC_RESPONSE = 'rpc-response';
    const TYPE_PLAIN_TEXT   = 'text';

    const CONTENT_TYPE_JSON       = 'application/json';
    const CONTENT_TYPE_PHP        = 'application/php.serialize.base64_encode';
    const CONTENT_TYPE_PLAIN_TEXT = 'text/plain';

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
     * @param string $body
     * @param array $properties
     *
     * @throws MessageException
     */
    public function __construct($body = '', $properties = [])
    {
        $this->body = $body;
        $this->properties = $properties;

        // Populate undefined properties with default vaules.
        if ('' == $this->getTimestamp())
        {
            $this->setTimestamp(time());
        }
        if ('' == $this->getType())
        {
            $this->setType(static::TYPE_PLAIN_TEXT);
        }
        if ('' == $this->getContentType())
        {
            $this->setContentType(static::CONTENT_TYPE_PLAIN_TEXT);
        }
    }

    /**
     * @param AMQPMessage $amqpMessage
     *
     * @return static
     * @throws MessageException
     */
    public static function loadAMQPMessage(AMQPMessage $amqpMessage)
    {
        $message = new static($amqpMessage->body, $amqpMessage->get_properties());
        $message->checkRequiredPropertiesPresence($amqpMessage);

        return $message;
    }

    /**
     * @return AMQPMessage
     */
    public function toAMQPMessage()
    {
        return new AMQPMessage($this->getBody(), $this->getProperties());
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
     * @return string
     */
    public function getContentType()
    {
        return $this->getProperty(static::PROPERTY_CONTENT_TYPE);
    }

    /**
     * @param string $contentType
     *
     * @return $this
     */
    public function setContentType($contentType)
    {
        return $this->setProperty(static::PROPERTY_CONTENT_TYPE, $contentType);
    }

    /**
     * @return string
     */
    public function getAppId()
    {
        return $this->getProperty(static::PROPERTY_APP_ID);
    }

    /**
     * @param string $appId
     *
     * @return $this
     */
    public function setAppId($appId)
    {
        return $this->setProperty(static::PROPERTY_APP_ID, $appId);
    }

    /**
     * @return string
     */
    public function getCorrelationId()
    {
        return $this->getProperty(static::PROPERTY_CORRELATION_ID);
    }

    /**
     * @param string $correlationId
     *
     * @return $this
     */
    public function setCorrelationId($correlationId)
    {
        return $this->setProperty(static::PROPERTY_CORRELATION_ID, $correlationId);
    }

    /**
     * @return int miliseconds
     */
    public function getExpiration()
    {
        return $this->getProperty(static::PROPERTY_EXPIRATION);
    }

    /**
     * @param int $expiration miliseconds
     *
     * @return $this
     */
    public function setExpiration($expiration)
    {
        return $this->setProperty(static::PROPERTY_EXPIRATION, $expiration);
    }

    /**
     * @return string
     */
    public function getReplyTo()
    {
        return $this->getProperty(static::PROPERTY_REPLY_TO);
    }

    /**
     * @param string $replyTo
     *
     * @return $this
     */
    public function setReplyTo($replyTo)
    {
        return $this->setProperty(static::PROPERTY_REPLY_TO, $replyTo);
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->getProperty(static::PROPERTY_TIMESTAMP);
    }

    /**
     * @param int $timestamp
     *
     * @return $this
     */
    public function setTimestamp($timestamp)
    {
        return $this->setProperty(static::PROPERTY_TIMESTAMP, $timestamp);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->getProperty(static::PROPERTY_TYPE);
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        return $this->setProperty(static::PROPERTY_TYPE, $type);
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->getProperty(static::PROPERTY_USER_ID);
    }

    /**
     * @param string $userId
     *
     * @return $this
     */
    public function setUserId($userId)
    {
        return $this->setProperty(static::PROPERTY_USER_ID, $userId);
    }

    /**
     * @param string $string
     * @param string $contentType
     *
     * @return $this
     */
    public function loadString($string, $contentType = self::CONTENT_TYPE_PLAIN_TEXT)
    {
        $this->setBody($string);
        $this->setContentType($contentType);

        return $this;
    }

    /**
     * @return mixed
     * @throws \Retrinko\Serializer\Exceptions\Exception
     */
    public function getPayload()
    {
        return SerializerFactory::bySerializedContentType($this->getContentType())
                                ->unserialize($this->getBody());
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
     * @param AMQPMessage $amqpMessage
     *
     * @throws MessageException
     */
    protected function checkRequiredPropertiesPresence(AMQPMessage $amqpMessage)
    {
        foreach ($this->getRequiredProperties() as $requiredProperty)
        {
            if (!array_key_exists($requiredProperty, $amqpMessage->get_properties()))
            {
                throw MessageException::requiredPropertyMissing($requiredProperty);
            }
        }
    }

}