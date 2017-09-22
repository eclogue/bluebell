<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/9/19
 * @time: 下午7:45
 */

namespace Bluebell\Socket;

use Worker;
use Bluebell\Channel;

class Server extends Worker
{

    private $config = [];

    private $host;

    private $port;

    protected $event = [];

    private $channel;

    public function __construct($config = [])
    {
        $this->config = $config;
        $this->channel = new Channel();

    }

    public function run()
    {
        $dsn = 'tcp://%s:%s';
        $dsn = printf($dsn, $this->host, $this->port);
        $socket = @stream_socket_server($dsn, $errorCode, $errorMessage);
        if (!$socket) throw new \Exception($errorCode, $errorMessage);
        stream_set_blocking($socket, 0);
        while (true) {
            $connection = stream_socket_accept($socket, 0);
            if ($connection === false) {
                $this->synchronized(function ()
                {
                    $this->wait(5000); // 5ms
                });
                continue;
            }
            $this->channel->put($connection);
        }
    }

    public function getChannel()
    {
        return $this->channel;
    }

    public function process()
    {

    }

    public function close()
    {

    }

    public function shutdown()
    {

    }

    public function register($event, $callable)
    {

    }

}