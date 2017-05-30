<?php


namespace Retrinko\CottonTail\Serializer;


interface SerializerInterface
{
    /**
     * @param mixed $input
     * @return string
     */
    public function serialize($input);

    /**
     * @param string $input
     * @return mixed
     */
    public function unserialize($input);

    /**
     * @return string Format: type/lang[/serizalizationFunctions]
     */
    public function getSerializedContentType();
}