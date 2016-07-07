<?php

require_once __DIR__ . '/../../vendor/autoload.php';
date_default_timezone_set('UTC');

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Retrinko\CottonTail\Rpc\Client;
use Retrinko\Serializer\Serializers\PhpSerializer;

$rabbitUserName = 'user';
$rabbitUserPass = 'password';
$rabbitServerHostNameOrIP = 'rabbit-server.test';
$rabbitServerPort = '5672';
$exchange = 'orders.router';
$destination = 'server';
$remoteProcedure = 'hello';
$params = ['name' => 'World!!'];

$logger = new Logger('RPC-CLIENT');
$logger->pushHandler(new StreamHandler('php://stdout'));

try
{
    $serializer = new PhpSerializer();
    $client = new Client($rabbitServerHostNameOrIP, $rabbitServerPort, $rabbitUserName,
                         $rabbitUserPass, $exchange);
    $client->setLogger($logger);
    $client->setSerializer($serializer);
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