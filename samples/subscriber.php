<?php

require_once __DIR__ . '/../vendor/autoload.php';
date_default_timezone_set('UTC');

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Retrinko\CottonTail\Connectors\PhpAmqpLibConnector;
use Retrinko\CottonTail\Subscriber\AbstractSubscriber;

$rabbitUserName = 'test';
$rabbitUserPass = 'test';
$rabbitServerHostNameOrIP = 'your-rabbitmq-server.com';
$rabbitServerPort = '5672';
$queue = 'test';
$vhost = '/';

$logger = new Logger('SUBSCRIBER');
$logger->pushHandler(new StreamHandler('php://stdout'));

class BasicSusbscriber extends AbstractSubscriber
{
    /**
     * Method for processing $this->currentReceivedMessage
     * @throws Exception
     */
    protected function callback()
    {
        $this->logger->notice('PROCESSING!', [get_class($this->currentReceivedMessage),
                                              $this->currentReceivedMessage->getProperties()]);
        $payload = $this->currentReceivedMessage->getPayload();
        var_dump($payload);
    }
}


try
{

    $connector = new PhpAmqpLibConnector($rabbitServerHostNameOrIP,
                                         $rabbitServerPort,
                                         $rabbitUserName,
                                         $rabbitUserPass);
    $subscriber = new BasicSusbscriber($connector, $queue);
    $subscriber->setLogger($logger);
    $subscriber->requeueMessagesOnCallbackFails(true);
    //$subscriber->setNumberOfMessagesToConsume(1);
    $subscriber->run();

}
catch (\Exception $e)
{
    printf('[!] Exception!: %s' . PHP_EOL, $e->getMessage());
}

printf(PHP_EOL);