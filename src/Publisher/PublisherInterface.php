<?php


namespace Retrinko\CottonTail\Publisher;


interface PublisherInterface
{
    /**
     * @param string $data
     *
     * @return void
     */
    public function publish($data);
}