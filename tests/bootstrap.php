<?php

$loader = require __DIR__.'/../vendor/autoload.php';
$loader->add('', __DIR__);

$config = parse_ini_file(__DIR__.'/config.ini');

foreach ($config as $key => $val) {
    if (!defined($key)) {
        define($key, $val);
    }
}
