<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/9/12
 * @time: 下午6:59
 */

require dirname(dirname(__FILE__)) . '/vendor/autoload.php';

use Bluebell\Channel;
use Bluebell\Scheduler;

$scheduler = new Scheduler();
$channel = new Channel();
$ops = 1e5;
function receive($chan, $ops) {
    $start = microtime(true);
    for ($i = 0; $i < $ops; $i++) {
        $val = yield $chan->take();
    }

    $duration = microtime(true) - $start;
    echo "duration:" . $duration . PHP_EOL;
    echo "ops/s:" . ($ops / $duration) . PHP_EOL;
}

function send($chan, $ops) {
    for ($i = 0; $i < $ops; $i++) {
        yield $chan->put($i);
    }
}

//$scheduler->add(send($channel, $ops));
//
//$scheduler->add(receive($channel, $ops));
//
//$scheduler->run();