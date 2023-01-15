<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../dannsbass/autoload.php';

$http = new React\Http\HttpServer(function (Psr\Http\Message\ServerRequestInterface $request){
    return (new Dannsbass\CariHadis\CariHadisRequestHandler($request))->ambilRespon();
});

$socket = new React\Socket\SocketServer(isset($argv[1]) ? $argv[1] : '127.0.0.1:80');
$http->listen($socket);

echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;
