<?php

require_once __DIR__ . '/../vendor/autoload.php';
date_default_timezone_set('UTC');

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Retrinko\CottonTail\Subscriber\AbstractSubscriber;

$rabbitUserName = 'user';
$rabbitUserPass = 'password';
$rabbitServerHostNameOrIP = 'rabbit-server.test';
$rabbitServerPort = '5672';
$queue = 'test-01';
$vhost = '/my-vhost';

$logger = new Logger('SUBSCRIBER');
$logger->pushHandler(new StreamHandler('php://stdout'));

class BasicSusbscriber extends AbstractSubscriber
{
    /**
     * Method for processing $this->currentReceivedMessage
     * @return void
     */
    protected function callback()
    {
        $this->logger->notice('PROCESSING!', [$this->currentReceivedMessage->body,
                                              $this->currentReceivedMessage->get_properties()]);
    }
}


try
{
    $subscriber = new BasicSusbscriber($rabbitServerHostNameOrIP, $rabbitServerPort, $rabbitUserName,
                                       $rabbitUserPass, $queue, $vhost);
    $subscriber->setLogger($logger);
    //$subscriber->setNumberOfMessagesToConsume(1);
    $subscriber->run();

}
catch (\Exception $e)
{
    printf('[!] Exception!: %s' . PHP_EOL, $e->getMessage());
}

printf(PHP_EOL);