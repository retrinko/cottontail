<?php


namespace Retrinko\CottonTail\Publisher;


use Retrinko\CottonTail\Serializer\SerializerInterface;

interface PublisherInterface
{
    /**
     * @param mixed $data
     *
     * @return void
     */
    public function publish($data);

    /**
     * @return SerializerInterface
     */
    public function getSerializer();
}