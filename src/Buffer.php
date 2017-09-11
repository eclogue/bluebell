<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/9/4
 * @time: 下午6:57
 */

namespace Bluebell;


class Buffer
{

    private $buffer;
    private $maxSize = 0;

    public function __construct($size)
    {
        $this->buffer = new \SplQueue();
        $this->maxSize = $size;
    }

    public function isFull()
    {
        return $this->buffer->count() >= $this->maxSize;
    }

    public function push($data)
    {
        $this->buffer->enqueue($data);
    }

    public function pop()
    {
        return $this->buffer->dequeue();
    }

    public function isEmpty()
    {
        return $this->buffer->isEmpty();
    }
}