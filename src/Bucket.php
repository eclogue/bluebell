<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/9/16
 * @time: 下午8:17
 */

namespace Bluebell;


class Bucket implements \Serializable
{

    public $queue;

    public function __construct()
    {
        $this->queue = new \SplQueue();
    }

    public function serialize() {
        return serialize($this->queue);
    }
    public function unserialize($data) {
        $this->queue = unserialize($data);
    }

    public function enqueue($value)
    {
        $this->queue->enqueue($value);
    }

    public function dequeue() {
        $this->queue->dequeue();
    }
}