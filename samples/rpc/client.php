<?php

require_once __DIR__ . '/../../vendor/autoload.php';
date_default_timezone_set('UTC');

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Retrinko\CottonTail\Connectors\PhpAmqpLibConnector;
use Retrinko\CottonTail\Rpc\Client;

$rabbitUserName = 'test';
$rabbitUserPass = 'test';
$rabbitServerHostNameOrIP = 'your-rabbitmq-server.com';
$rabbitServerPort = '5672';
$exchange = 'notifications.router';
$destination = 'slack';
$remoteProcedure = 'hello';
$params = ['name' => 'World!!'];

$logger = new Logger('RPC-CLIENT');
$logger->pushHandler(new StreamHandler('php://stdout'));

try
{
    $connector = new PhpAmqpLibConnector($rabbitServerHostNameOrIP,
                                         $rabbitServerPort,
                                         $rabbitUserName,
                                         $rabbitUserPass);
    $client = new Client($connector, $exchange);
    $client->setLogger($logger);

    $response = $client->call($destination, $remoteProcedure, $params);
    if($response->isOK())
    {
        $logger->notice('OK Response received!');
        var_dump($response->getData());
    }
    else
    {
        $logger->error('Error response received', $response->getErrors());
    }
}
catch (\Exception $e)
{
    printf('[!] Exception!: %s (File: %s, Line: %s)' . PHP_EOL,
           $e->getMessage(), $e->getFile(), $e->getLine());
    var_export($e->getTraceAsString());
}

printf(PHP_EOL);