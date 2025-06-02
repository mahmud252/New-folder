<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use React\Socket\Server as SocketServer;
use React\EventLoop\Factory;

class MessageServer implements MessageComponentInterface {
    public function onOpen(ConnectionInterface $conn) {
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo "Message from {$from->resourceId}: $msg\n";
        // Broadcast the message to all connected clients
        foreach ($from->httpRequest->getConnection()->webSocket->clients as $client) {
            if ($client !== $from) {
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

// Set up WebSocket server
$loop = Factory::create();
$webSock = new SocketServer('0.0.0.0:8080', [], $loop);
$webSocket = new Ratchet\Server\IoServer(
    new Ratchet\Http\WsServer(
        new MessageServer()
    ),
    $webSock
);

echo "WebSocket server running at ws://127.0.0.1:8080\n";
$loop->run();
