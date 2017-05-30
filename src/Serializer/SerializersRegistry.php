<?php

namespace Retrinko\CottonTail\Serializer;

use Retrinko\CottonTail\Exceptions\SerializerRegistryException;
use Retrinko\CottonTail\Serializer\Serializers\JsonSerializer;
use Retrinko\CottonTail\Serializer\Serializers\NullSerializer;
use Retrinko\CottonTail\Serializer\Serializers\PhpSerializer;

class SerializersRegistry
{
    /**
     * @var SerializersRegistry
     */
    protected static $instance;
    /**
     * @var array
     */
    protected $registry;

    protected function __construct()
    {
        $this->registry[NullSerializer::SERIALIZED_CONTENT_TYPE] = new NullSerializer();
        $this->registry[PhpSerializer::SERIALIZED_CONTENT_TYPE] = new PhpSerializer();
        $this->registry[JsonSerializer::SERIALIZED_CONTENT_TYPE] = new JsonSerializer();
    }

    /**
     * @return SerializersRegistry
     */
    public static function getInstance()
    {
        if (is_null(self::$instance))
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return SerializersRegistry
     */
    public static function i()
    {
        return self::getInstance();
    }

    /**
     * @param SerializerInterface $serializer
     */
    public function registerSerializer(SerializerInterface $serializer)
    {
        $this->registry[$serializer->getSerializedContentType()] = $serializer;
    }

    /**
     * @param string $contentType
     *
     * @return SerializerInterface
     * @throws SerializerRegistryException
     */
    public function getSerializer($contentType)
    {
        if (!isset($this->registry[$contentType]))
        {
            throw SerializerRegistryException::notFound($contentType);
        }

        return $this->registry[$contentType];
    }
}