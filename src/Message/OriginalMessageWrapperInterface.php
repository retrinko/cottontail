<?php


namespace Retrinko\CottonTail\Message;


interface OriginalMessageWrapperInterface
{
    /**
     * @param mixed $message
     *
     * @return void
     */
    public function setOriginalMessage($message);

    /**
     * @return mixed
     */
    public function getOriginalMessage();
}