<?php
/**
 * @license MIT
 * @copyright Copyright (c) 2017
 * @author: bugbear
 * @date: 2017/9/11
 * @time: 下午1:06
 */
require dirname(dirname(__FILE__)) . '/vendor/autoload.php';

use Bluebell\Channel;
use Bluebell\Scheduler;

function player(Channel $channel, $name) {
    while(true) {
        $ball = yield $channel->take();
//        var_dump($ball);
        $ball += 1;
        echo "$name play $ball " . PHP_EOL;
//        yield sleep(1);
        yield $channel->put($ball);
    }
}


function go(Scheduler $scheduler) {
    $chan = new Channel(1000);
    $ball = 0;
    yield $chan->put($ball);
    $scheduler->add(player($chan, 'ping'));
    $scheduler->add(player($chan, 'pong'));
    echo 'end' . PHP_EOL;
}

$scheduler = new Scheduler();
$scheduler->add(go($scheduler));
$scheduler->run();

