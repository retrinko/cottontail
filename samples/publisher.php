<?php

require_once __DIR__ . '/../vendor/autoload.php';
date_default_timezone_set('UTC');

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Retrinko\CottonTail\Connectors\PhpAmqpLibConnector;
use Retrinko\CottonTail\Publisher\Publishers\BasicPublisher;


$rabbitUserName = 'test';
$rabbitUserPass = 'test';
$rabbitServerHostNameOrIP = 'your-rabbitmq-server.com';
$rabbitServerPort = '5672';
$queue = 'test';
//$vhost = '/my-vhost';
$numberOfMessagesToPublish = 3;

$logger = new Logger('PUBLISHER');
$logger->pushHandler(new StreamHandler('php://stdout'));

try
{
    $connector = new PhpAmqpLibConnector($rabbitServerHostNameOrIP,
                                         $rabbitServerPort,
                                         $rabbitUserName,
                                         $rabbitUserPass);
    $publisher = new BasicPublisher($connector);
    $publisher->setDestination($queue);
    $publisher->setLogger($logger);

    for ($i = 1; $i <= $numberOfMessagesToPublish; $i++)
    {
        $publisher->publish(sprintf('Hello world! (%s)', $i));
    }


}
catch (\Exception $e)
{
    printf('[!] Exception!: %s' . PHP_EOL, $e->getMessage());
}

printf(PHP_EOL);