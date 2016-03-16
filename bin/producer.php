#!/usr/bin/env php
<?php
namespace Producer;

require dirname(__DIR__) . '/vendor/autoload.php';

$container = new ProducerContainer(
    $_SERVER['HOME'],
    getcwd(),
    STDOUT,
    STDERR
);

try {
    $command = $container->newCommand($argv);
    $exit = (int) $command();
    exit($exit);
} catch (\Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    exit(1);
}
