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
use Bluebell\Poroutine;
use Bluebell\Scheduler;

function player(Channel $channel, $name) {
    while(true) {
        $ball = yield $channel->take();
        var_dump($ball);
        $ball += 1;
        echo "$name play $ball " . PHP_EOL;
        yield $channel->put($ball);
    }
}


function go() {
    $chan = new Channel();
    $ball = 0;
    yield $chan->put($ball);
    yield player($chan, 'ping');
    yield player($chan, 'pong');
    echo 'end' . PHP_EOL;
}

$scheduler = new Scheduler();
$scheduler->add(go());
$scheduler->run();
