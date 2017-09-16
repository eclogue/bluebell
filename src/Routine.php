<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/9/14
 * @time: 下午7:31
 */

namespace Bluebell;

use Threaded;

class Routine extends Threaded {

    protected $channel;

    protected $callable;

    protected $result;

    protected $args;

    public function __construct(Channel $channel, $callable, $args = null) {
        $this->channel = $channel;
        $this->callable = $callable;
        $this->args = $args;
    }

    public function run() {
        if (is_object($this->callable)) {
            echo 'fucntion ' . PHP_EOL;
            $callable = (array) $this->callable;
            $name = array_shift($callable);
            $this->result = call_user_func_array($name, ...$callable);
            var_dump($this->result);
        } else if ($this->callable instanceof \Closure) {
            echo "123123\n";
            $this->callable->bindTo($this);
            $this->result = ($this->callable)();
        }
//        $this->result = ($this->callable)();
    }

    public static function call(Channel $channel, $callable) {
        $thread = new self($channel, $callable);
//        if($thread->start()){
//            return $thread;
//        }

        return $thread;
    }
}