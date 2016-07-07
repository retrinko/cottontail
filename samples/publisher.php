<?php

require_once __DIR__ . '/../vendor/autoload.php';
date_default_timezone_set('UTC');

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Retrinko\CottonTail\Publisher\Publishers\BasicPublisher;


$rabbitUserName = 'user';
$rabbitUserPass = 'password';
$rabbitServerHostNameOrIP = 'rabbit-server.test';
$rabbitServerPort = '5672';
$queue = 'test';
//$vhost = '/my-vhost';

$logger = new Logger('PUBLISHER');
$logger->pushHandler(new StreamHandler('php://stdout'));

try
{
    $publisher = new BasicPublisher($rabbitServerHostNameOrIP, $rabbitServerPort,
                                    $rabbitUserName, $rabbitUserPass/*, $vhost*/);
    $publisher->setDestination($queue);
    $publisher->setLogger($logger);
    $publisher->publish('Hello world!');
}
catch (\Exception $e)
{
    printf('[!] Exception!: %s' . PHP_EOL, $e->getMessage());
}

printf(PHP_EOL);