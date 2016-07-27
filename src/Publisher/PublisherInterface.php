<?php


namespace Retrinko\CottonTail\Publisher;


use Retrinko\Serializer\Interfaces\SerializerInterface;

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