<?php

require_once './vendor/autoload.php';

use Json2CLI\J2C;

$file = $_SERVER['argv'][1];
array_splice($_SERVER['argv'], 1, 1);

$j2c = new J2C();
$cli = $j2c->createFromFile($file);
$cli->run();