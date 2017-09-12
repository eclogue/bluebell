<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/9/4
 * @time: 下午5:49
 */

namespace Bluebell;

use SplQueue;

class Channel
{

    private $putQueue;

    private $takeQueue;

    private $buffer;


    private $readCond;

    private $writeCond;

    private $mutexLock;

    private $closed = false;


    public function __construct($size = 0)
    {
        $this->putQueue = new SplQueue();
        $this->takeQueue = new SplQueue();
        if ($size) {
            $this->buffer = new Buffer($size);
        }
    }

    public function isBuffered()
    {
        return $this->buffer instanceof Buffer;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function put($data)
    {
//        echo "put <----" . $data . PHP_EOL;
        if ($this->closed) {
            return false;
        }

        if ($this->takeQueue->count()) {
            $task = $this->takeQueue->dequeue();
            $task->setResult($data);
            $task->resolve();
        }

        if ($this->isBuffered() && !$this->buffer->isFull()) {
            $this->buffer->push($data);
            return true;
        }

        $task = new Deferred($data);
        $this->putQueue->enqueue($task);
        return $task;
    }

    public function take()
    {
//        echo "take----->" . PHP_EOL;
        if ($this->closed) {
            return false;
        }
        if ($this->isBuffered() && !$this->buffer->isEmpty()) {
            $task = $this->buffer->pop();
            return $task;
        }

        if ($this->putQueue->count()) {
            $task = $this->putQueue->dequeue();
            $task->resolve();
            return $task->getResult();
        }

        // no values, block
        $task = new Deferred();
        $this->takeQueue->enqueue($task);

        return null;
//        return $task->wait();
    }


    public function select()
    {

    }

    public function reduce()
    {

    }

    public function delay()
    {

    }


    public function close()
    {
        if ($this->closed) {
            throw new \RuntimeException('Channel already closed');
        }
        $this->closed = true;
        $this->takeQueue = null;
        $this->putQueue = null;
    }

    public function resolve($data = null)
    {
        yield $data;
    }
}