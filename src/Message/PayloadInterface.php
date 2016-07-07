<?php


namespace Retrinko\CottonTail\Message;

use Retrinko\Serializer\Interfaces\SerializerInterface;

interface PayloadInterface
{
    /**
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function setData($data);

    /**
     * @return mixed
     */
    public function getData();

    /**
     * @param SerializerInterface $serializer
     *
     * @return string
     */
    public function serialize(SerializerInterface $serializer);
}