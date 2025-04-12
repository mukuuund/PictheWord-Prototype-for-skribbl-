<?php
require __DIR__ . '/vendor/autoload.php';

// Include fetch_word.php for word-fetching functionality
include 'fetch_word.php';

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

// Check if the script is being run from the command line
if (php_sapi_name() === 'cli') {
    // Check if the session ID is provided as a command-line argument
    $sessionId = $argv[1] ?? null;
    if (!$sessionId) {
        die("Session ID is required to start the server.\n");
    }
} else {
    // For web execution, use GET or POST request to get the session ID
    $sessionId = $_GET['sessionId'] ?? null;
    if (!$sessionId) {
        die("Session ID is required to start the server.\n");
    }
}

// Class definition for GameServer...
class GameServer implements MessageComponentInterface {
    protected $clients;
    protected $usernames; // Store usernames mapped by session IDs
    protected $word;      // Word to guess
    protected $pdo;       // Database connection

    public function __construct($sessionId, $pdo) {
        $this->clients = new \SplObjectStorage;
        $this->usernames = [];
        $this->pdo = $pdo;

        try {
            // Use fetchWord to fetch the word for the session
            $this->word = fetchWord($sessionId, $this->pdo);
            echo "Word for this game: {$this->word}\n";
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection: ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        switch ($data['type']) {
            case 'setUsername':
                $sessionId = $data['sessionId'];
                $this->usernames[$sessionId] = $data['username'];
                break;

            case 'draw':
                foreach ($this->clients as $client) {
                    $client->send(json_encode($data));
                }
                break;

            case 'clear':
                foreach ($this->clients as $client) {
                    $client->send(json_encode(['type' => 'clear']));
                }
                break;

            case 'chat':
                $sessionId = $data['sessionId'];
                $username = $this->usernames[$sessionId] ?? 'Anonymous';
                $message = $data['message'];

                if (strcasecmp($message, $this->word) === 0) {
                    $this->endGame($username);
                    break;
                }

                $chatMessage = [
                    'type' => 'chat',
                    'username' => $username,
                    'message' => $message
                ];
                foreach ($this->clients as $client) {
                    $client->send(json_encode($chatMessage));
                }
                break;
        }
    }

    private function endGame($winner) {
        $endMessage = [
            'type' => 'endGame',
            'winner' => $winner,
            'word' => $this->word
        ];

        foreach ($this->clients as $client) {
            $client->send(json_encode($endMessage));
        }

        echo "Game ended. Winner: $winner\n";

    
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection ({$conn->resourceId}) has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

// Include db_connection.php and create a PDO instance
include 'db_connection.php';

// Start the WebSocket server
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new GameServer($sessionId, $pdo)
        )
    ),
    8080
);

echo "WebSocket server running on ws://localhost:8080\n";
$server->run();
