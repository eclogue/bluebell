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
use Threaded;

class Channel extends Threaded
{

    public $putting = 0;

    public $taking = 0;

    public $buffer;

    public $closed = false;

    public $queue = [];


    public function __construct($size = 0)
    {
//        $this->putQueue = [];
//        $this->takeQueue = [];
//        $this->queue = [];
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
        return $this->synchronized(function () use ($data) {
            if ($this->closed) {
                return false;
            }

            $this->putting++;
            $this->queue[] = $data;
            if ($this->taking > 0) {
                return $this->notifyOne();
            }

            // buffered
            if ($this->isBuffered()) {
                // buffer full, block
                while ($this->buffer->isFull() && !$this->closed) {
                    $this->wait();
                }
                if ($this->closed) {
                    return false;
                }
                $data = $this->buffer->push($data);
                return $data;
            }

            if ($this->closed) {
                return false;
            }

            $this->wait();

            return true;
        });
    }

    public function take()
    {
        return $this->synchronized(function () {
            if ($this->closed) {
                return false;
            }
            // buffered
            if ($this->isBuffered() && !$this->buffer->isEmpty()) {
                $data = $this->buffer->pop();
                return $data;
            }
            while (!$this->closed && $this->putting < 1) {
                // no put, block
                $this->taking++;
                $this->wait();
                $this->taking--;
            }

            if ($this->closed) {
                return false;
            }


            $data = $this->current();
            $this->notifyOne();
            $this->putting--;

            return $data;

        });
    }


    public function select()
    {

    }

    public function current()
    {
//        var_dump($this->queue);
        if ($this->isEmpty($this->queue)) {
            return false;
        }

        $data = (array)$this->queue;
        $keys = array_keys($data);
        $ret = array_shift($data);
        $index = $keys[0];
//        echo "index--->+++" . $index . PHP_EOL;
        unset($this->queue[$index]);

        return $ret;
    }

    public function isEmpty($item)
    {
        if (is_object($item)) {
            $item = (array)$item;
            return empty(array_keys($item));
        }

        return empty($item);
    }


    public function close()
    {
        if ($this->closed) {
            throw new \RuntimeException('Channel already closed');
        }
        $this->closed = true;
        $this->takeQueue = null;
        $this->putQueue = null;
        $this->buffer = null;

        return true;
    }


}