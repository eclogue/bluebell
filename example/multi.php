<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/9/12
 * @time: 下午9:18
 */
class Channel extends Threaded {
    /* setting a value on the channel shall cause waiters to wake up */
    public function __set($key, $value) {
        return $this->synchronized(function() use ($key, $value) {
            if ($key !== 'data') {
                print_r($this->data);
                $this->data->enqueue($value);

            }
            $this[$key] = $value;
            return $this->notify();
        });
    }

    /* getting a value on the channel shall cause callers to wait until it's available */
    public function __get($key) {
        return $this->synchronized(function() use($key) {
            print_r($this->data->count());
            while (!isset($this[$key]))
                $this->wait();
            return $this[$key];
        });
    }

    public $data;

    public function __construct()
    {
        $this->data = new SplQueue();
    }
}
class Routine extends Threaded {
    public function __construct(Channel $channel) {
        $this->channel = $channel;
    }

    public function run() {
        /* sending on the channel */
        $this
            ->channel["message"] = "Hello World";
        $this
            ->channel["gold"] = 3.462;
    }
    protected $channel;
}
$channel = new Channel();
$pool = new Pool(4);
$pool->submit(
    new Routine($channel));
/* recving on the channel */
printf("Message: %s, Gold: %.3f\n",
    $channel["message"],
    $channel["gold"]);

//
//class Future extends Thread {
//    private function __construct(Closure $closure, array $args = []) {
//        $this->closure = $closure;
//        $this->args    = $args;
//    }
//    public function run() {
//        $this->synchronized(function() {
//            $this->result =
//                (array) ($this->closure)(...$this->args);
//            $this->notify();
//        });
//    }
//    public function getResult() {
//        return $this->synchronized(function(){
//            while (!$this->result)
//                $this->wait();
//            return $this->result;
//        });
//    }
//
//    public static function of(Closure $closure, array $args = []) {
//        $future =
//            new self($closure, $args);
//        $future->start();
//        return $future;
//    }
//
//    protected $owner;
//    protected $closure;
//    protected $args;
//    protected $result;
//}
///* some data */
//$test = ["Hello", "World"];
///* a closure to execute in background and foreground */
//$closure = function($test) {
//    return $test;
//};
///* make call in background thread */
//$future = Future::of($closure, [$test]);
///* get result of background and foreground call */
//var_dump($future->getResult(), $closure($test));
