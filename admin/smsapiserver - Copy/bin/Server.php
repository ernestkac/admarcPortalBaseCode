<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyApp\Chat;

require dirname(__DIR__) . '/vendor/autoload.php';

$webSock = new WsServer(new Chat);

$server = IoServer::factory(
    new HttpServer($webSock),
    8080
);

$server->run();
