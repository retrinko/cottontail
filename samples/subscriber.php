<?php

require_once __DIR__ . '/../vendor/autoload.php';
date_default_timezone_set('UTC');

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Retrinko\CottonTail\Connectors\RabbitMQConnector;
use Retrinko\CottonTail\Subscriber\AbstractSubscriber;

$rabbitUserName = 'userName';
$rabbitUserPass = 'password';
$rabbitServerHostNameOrIP = 'your-server.com';
$rabbitServerPort = '5672';
$queue = 'queueName';
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
        $this->logger->info($payload);
    }
}


try
{

    $connector = new RabbitMQConnector($rabbitServerHostNameOrIP,
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