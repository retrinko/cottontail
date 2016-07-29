<?php

require_once __DIR__ . '/../../vendor/autoload.php';
date_default_timezone_set('UTC');

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Retrinko\CottonTail\Connectors\RabbitMQConnector;
use Retrinko\CottonTail\Rpc\Client;

$rabbitUserName = 'userName';
$rabbitUserPass = 'password';
$rabbitServerHostNameOrIP = 'your-server.com';
$rabbitServerPort = '5672';
$exchange = 'exchangeName';
$destination = 'destination';
$remoteProcedure = 'hello';
$params = ['name' => 'World!!'];

$logger = new Logger('RPC-CLIENT');
$logger->pushHandler(new StreamHandler('php://stdout'));

try
{
    $connector = new RabbitMQConnector($rabbitServerHostNameOrIP,
                                       $rabbitServerPort,
                                       $rabbitUserName,
                                       $rabbitUserPass);
    $client = new Client($connector, $exchange);
    $client->setLogger($logger);

    $response = $client->call($destination, $remoteProcedure, $params);
    var_dump($response);
}
catch (\Exception $e)
{
    printf('[!] Exception!: %s (File: %s, Line: %s)' . PHP_EOL,
           $e->getMessage(), $e->getFile(), $e->getLine());
    var_export($e->getTraceAsString());
}

printf(PHP_EOL);