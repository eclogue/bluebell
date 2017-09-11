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
        if ($this->closed) {
            return false;
        }

        if ($this->takeQueue->count()) {
            $resolve = $this->takeQueue->dequeue();
            return $resolve($data);
        }

        if ($this->isBuffered() && !$this->buffer->isFull()) {
            $this->buffer->push($data);
            return true;
        }

        $this->putQueue->enqueue($data);

        return true;
    }

    public function take()
    {
        if ($this->closed) {
            return false;
        }
        if ($this->isBuffered()) {
            while (!$this->buffer->isEmpty()) {
                $task = $this->buffer->pop();
                yield $task;
            }

            return null;
        }

        while ($this->putQueue->count()) {
            $task = $this->putQueue->dequeue();
            yield $task;
        }

        // no values, block
        $task = $this->wrap();
        $this->takeQueue->enqueue($task);

        return $task;
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

    public function wrap()
    {
        $func =  function ($value) {
            $this->takeQueue->enqueue($value);
            yield true;
        };

        $func->bindTo($this);
        return $func;
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