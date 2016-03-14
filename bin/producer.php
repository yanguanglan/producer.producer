#!/usr/bin/env php
<?php
namespace Producer;

require dirname(__DIR__) . '/vendor/autoload.php';

$config = parse_ini_file($_SERVER['HOME'] . '/.producer');

$container = new ProducerContainer($config, new Fsio(getcwd()));

$api = $container->newApi($container->newVcs());
var_export($api->fetchIssues());
