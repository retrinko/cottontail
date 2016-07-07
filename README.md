# retrinko/cottontail

__retrinko/cottontail__ is a wrapper for [php-amqplib/php-amqplib](https://github.com/php-amqplib/php-amqplib) and provides implementations for some basic comunication patterns as:
 
 - Publisher
 - Subscriber
 - RPC-Server
 - RPC-Client

## Installation

Install the latest version with

    $ composer require retrinko/cottontail

## Samples

### Publisher
    
    <?php
    
    use Retrinko\CottonTail\Publisher\Publishers\BasicPublisher;
    
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $rabbitUserName = 'user';
    $rabbitUserPass = 'pass';
    $rabbitServerIP = '111.111.111.111';
    $rabbitServerPort = '5672';
    $queue = 'queue';
    
    try
    {
        $publisher = new BasicPublisher($rabbitServerIP, $rabbitServerPort,
                                        $rabbitUserName, $rabbitUserPass);
        $publisher->setDestination($queue);
        $publisher->publish('Hello world!');
    }
    catch (\Exception $e)
    {
        printf('[!] Exception!: %s' . PHP_EOL, $e->getMessage());
    }
    
    printf(PHP_EOL);
    
### Subscriber

    <?php
    
    require_once __DIR__ . '/../vendor/autoload.php';
    date_default_timezone_set('UTC');
        
    use Retrinko\CottonTail\Subscriber\AbstractSubscriber;
    
    $rabbitUserName = 'user';
    $rabbitUserPass = 'pass';
    $rabbitServerIP = '111.111.111.111';
    $rabbitServerPort = '5672';
    $queue = 'queue';
    
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
        $subscriber = new BasicSusbscriber($rabbitServerIP, $rabbitServerPort, $rabbitUserName,
                                           $rabbitUserPass, $queue);
        //$subscriber->setNumberOfMessagesToConsume(1);
        $subscriber->run();
    
    }
    catch (\Exception $e)
    {
        printf('[!] Exception!: %s' . PHP_EOL, $e->getMessage());
    }
    
    printf(PHP_EOL);
    
   
### RPC Server    

    <?php
    
    require_once __DIR__ . '/../vendor/autoload.php';
    date_default_timezone_set('UTC');
    
    use Retrinko\CottonTail\Rpc\Server;
    use Retrinko\Serializer\Serializers\PhpSerializer;
    
    $rabbitUserName = 'user';
    $rabbitUserPass = 'pass';
    $rabbitServerIP = '111.111.111.111';
    $rabbitServerPort = '5672';
    $queue = 'queue';
    
    try
    {
        $server = new Server($rabbitServerIP, $rabbitServerPort, $rabbitUserName,
                             $rabbitUserPass, $queue);
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
    
        
### RPC Client

    <?php
    
    require_once __DIR__ . '/../vendor/autoload.php';
    date_default_timezone_set('UTC');
    
    use Retrinko\CottonTail\Rpc\Client;
    use Retrinko\Serializer\Serializers\PhpSerializer;
    
    $rabbitUserName = 'user';
    $rabbitUserPass = 'pass';
    $rabbitServerIP = '111.111.111.111';
    $rabbitServerPort = '5672';
    $exchange = 'exchange';
    $destination = 'destination';
    $remoteProcedure = 'hello';
    $params = ['name' => 'World!!'];
    
    try
    {
        $client = new Client($rabbitServerIP, $rabbitServerPort, $rabbitUserName,
                             $rabbitUserPass, $exchange);
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
    
    