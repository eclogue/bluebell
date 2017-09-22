<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/9/19
 * @time: 下午8:38
 */

namespace Bluebell\Socket;

use BFunky\HttpParser\HttpRequestParser;
use Bluebell\Poroutine;
use Bluebell\Scheduler;
use Bluebell\SystemCall;

class Worker extends \Threaded
{

    public $params;

    public $callable;

    protected $running = false;

    public function __construct(\Closure $callback)
    {

    }

    public function run()
    {
        $scheduler = new Scheduler();
        $scheduler->add($this->process());
        $scheduler->run();

    }

    public function process()
    {
        $channel = $this->worker->getChannel();
        while (true) {
            $socket = $channel->take();
            if ($socket) {
                yield $this->newTask($this->parseHttp($socket));
                continue;
            }

            $this->wait(5000); // 5ms
        }
    }

    public function select()
    {

    }

    public function newTask(\Generator $coroutine)
    {
        return new SystemCall(
            function(Poroutine $task, Scheduler $scheduler) use ($coroutine) {
                $scheduler->add($coroutine);
                $scheduler->schedule($task);
            }
        );
    }

    private function parseHttp($socket)
    {
        $data = yield fread($socket, 8192);
        $parser = new HttpRequestParser();
        $request = yield $parser->parse($data);
    }
}