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

$command = $container->newCommand($argv);
$command();
