<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/9/12
 * @time: 下午1:20
 */

namespace Bluebell;

use Thread;

class Deferred extends Thread
{

    private $done = false;

    private $result;

    private $error;

    private $callback;

    public function __construct($callback = null)
    {
        $this->callback = $callback;
    }

    public function run() {
        $this->synchronized(function() {
            if (is_array($this->callback)) {
                $this->result = call_user_func($this->callback);
            } else {
                $this->result = ($this->callback)();
            }

            $this->done = true;
            $this->notify();
        });
    }

    public function getResult() {
        return $this->synchronized(function(){
            while (!$this->done) {
                $this->wait();
            }
            return $this->result;
        });
    }

    public function resolve()
    {
        $this->done = true;
    }

    public static function factory($callback) {
        $defer = new self($callback);
        $defer->start();

        return $defer;
    }


    public function error($callback) {

    }


}