<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/9/15
 * @time: 下午5:54
 */

require dirname(dirname(__FILE__)) . '/vendor/autoload.php';

use Bluebell\Channel;
use Bluebell\Scheduler;
use Bluebell\Routine;

$start = microtime(true);

function player($channel, $name) {
    $ball = 0;
    echo "!23123";
    var_dump($channel);
    while($ball < 1e6) {
        $ball = $channel->take();
        var_dump($ball);
        $ball += 1;
        echo "$name play $ball " . PHP_EOL;
//        yield sleep(1);
        $channel->put($ball);
    }

    return $ball;
}

class Test1 extends Threaded {
    public function __construct(Channel $channel) {
        $this->channel = $channel;
    }

    public function run() {
        for($i =0; $i < 5 * 10; $i++) {
            $ball = new SplStack();
            $this->channel->put($ball);
        }
    }
    protected $channel;
}


class Test2 extends Threaded {
    public function __construct(Channel $channel) {
        $this->channel = $channel;
    }

    public function run() {
        $start = microtime(true);
        for($i =0; $i < 5 ; $i++) {
            $data = $this->channel->take();
            var_dump($data);
//            echo "++++++" . $data . PHP_EOL;
        }
        $duration = microtime(true) - $start;
        echo "duration:" . $duration . PHP_EOL;
        echo "ops/s:" . (1e6 / $duration) . PHP_EOL;
    }
    protected $channel;
}
$channel = new Channel();
$pool = new \Pool(4);
$pool->submit(new Test1($channel));
$pool->submit(new Test2($channel));
$pool->submit(new Test2($channel));
$pool->submit(new Test1($channel));
//$pool->submit(new Test2($channel));
//function go(Scheduler $scheduler) {
//    $chan = new Channel();
//    $ball = 0;
//    $chan->put($ball);
//    yield player($chan, 'ping');
//    echo '123123';
//    yield player($chan, 'pong');
//}



////$pool = new Pool(2);
//////$pong =Routine::call($channel, ['player', 'pong']);
////$pool->submit(Routine::call($channel, ['player', 'ping']));
////$pool->submit( Routine::call($channel, ['player', 'pong']));
//$scheduler = new Scheduler();
//$scheduler->add(player($channel, 'ping'));
//$scheduler->add(player($channel, 'pong'));
//$scheduler->run();

