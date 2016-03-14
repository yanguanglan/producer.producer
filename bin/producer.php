#!/usr/bin/env php
<?php
namespace Producer;

require dirname(__DIR__) . '/vendor/autoload.php';

$config = parse_ini_file($_SERVER['HOME'] . '/.producer');

$container = new ProducerContainer(
    $config,
    new Stdlog(STDOUT, STDERR),
    new Fsio(getcwd())
);

try {
    $command = $container->newCommand($argv);
    $exit = (int) $command();
    exit($exit);
} catch (\Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    exit(1);
}
