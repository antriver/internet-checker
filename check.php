<?php

$config = require __DIR__.'/config.php';

require __DIR__.'/Checker.php';
$checker = new Checker($config);

while (true) {
    print_r($checker->testAndLog());
    sleep(15);
}
