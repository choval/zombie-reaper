#!/bin/env php
<?php
$ret = @ini_set('zend.assertions', '1');
if ($ret === false) {
    echo "ini_set failed\n";
    exit(1);
}
ini_set('assert.exception', '1');

require(__DIR__.'/../src/fns.php');

echo "This PID: ".getmypid()."\n";
echo "Generating zombie\n";
echo "\n";
$pid = pcntl_fork();
if ($pid == -1) {
    die('could not fork');
} elseif ($pid) {
    // parent
    sleep(1);
    $zombies = zombie_find();
    $zombies_count = count($zombies);
    assert($zombies_count === 1);
    echo "Zombies found: $zombies_count\n";
    $zombie = $zombies[0];
    print_r($zombie);
    echo "\n";

    echo "Killing zombie: {$zombie['pid']}\n";
    $res = zombie_kill($zombie['pid']);
    echo "Result: $res\n";
    echo "\n";

    echo "Checking zombies\n";
    $zombies = zombie_find();
    $zombies_count = count($zombies);
    assert($zombies_count === 0);
    echo "Zombies found: $zombies_count\n";
} else {
    // child
    exit(0);
}
