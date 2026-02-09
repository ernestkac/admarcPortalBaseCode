<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
class Chat implements MessageComponentInterface {

    private $clients;
    private $url;

    public function __construct() {
        $this->clients =  array();
        $host = $host = gethostbyname(gethostname());
        $this->url = "ws://$host";
        echo "Server started on: " . $this->url . ":8080\n";
    }

    public function onOpen(ConnectionInterface $conn) {
       
        $this->clients[] = $conn;
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo "Message from {$from->resourceId}: {$msg}\n";
        foreach($this->clients as $client){
            if($client != $from){
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
    }
}
