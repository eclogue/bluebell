<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/9/12
 * @time: 下午1:20
 */

namespace Bluebell;


class Deferred
{

    private $done = false;

    private $result;

    public function __construct($value = null)
    {
        $this->result = $value;
    }

    public function wait()
    {
        $i = 0;
//        while (!$this->done) {
//            echo 'wait::' . $i++ . PHP_EOL;
//            yield;
//        }

        yield $this->result;
    }

    public function resolve()
    {
        $this->done = true;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setResult($value)
    {
        $this->result = $value;
    }
}