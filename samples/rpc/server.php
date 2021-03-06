<?php

require_once __DIR__ . '/../../vendor/autoload.php';
date_default_timezone_set('UTC');

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Retrinko\CottonTail\Connectors\PhpAmqpLibConnector;
use Retrinko\CottonTail\Rpc\Server;

$rabbitUserName = 'test';
$rabbitUserPass = 'test';
$rabbitServerHostNameOrIP = 'your-rabbitmq-server.com';
$rabbitServerPort = '5672';
$queue = 'notifications.slack';

$logger = new Logger('RPC-SERVER');
$logger->pushHandler(new StreamHandler('php://stdout'));

try
{
    $connector = new PhpAmqpLibConnector($rabbitServerHostNameOrIP,
                                         $rabbitServerPort,
                                         $rabbitUserName,
                                         $rabbitUserPass);
    $server = new Server($connector, $queue);
    $server->setLogger($logger);
    $server->registerProceduresClass(new TestServer());
    $server->run();
}
catch (\Exception $e)
{
    printf('[!] Exception (%s)! (File: %s, Line: %s): %s' . PHP_EOL,
           get_class($e), $e->getFile(), $e->getLine(), $e->getMessage());
    print($e->getTraceAsString());
}

printf(PHP_EOL);

class TestServer
{
    /**
     * @param string $name
     *
     * @return string
     */
    public function hello($name = '')
    {
        $name = ('' != $name) ? ' ' . $name : $name;

        return 'Hello' . $name . '!';
    }
}