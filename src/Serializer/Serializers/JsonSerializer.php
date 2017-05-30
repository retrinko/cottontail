<?php

namespace Retrinko\CottonTail\Serializer\Serializers;

use Retrinko\CottonTail\Serializer\SerializerInterface;

/**
 * Class JsonSerializer
 */
class JsonSerializer implements SerializerInterface
{

    /**
     * Serialized content type.
     * type/lang[/serizalizationFunctions]
     */
    const SERIALIZED_CONTENT_TYPE = 'application/json';

    /**
     * @param mixed $input
     * @return string
     */
    public function serialize($input)
    {
        return json_encode($input);
    }

    /**
     * @param $input
     * @return mixed
     */
    public function unserialize($input)
    {
        return json_decode($input, true);
    }

    /**
     * @return string
     */
    public function getSerializedContentType()
    {
        return self::SERIALIZED_CONTENT_TYPE;
    }
}