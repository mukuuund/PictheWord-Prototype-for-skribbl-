<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sessionId = $_POST['sessionId'] ?? '';
    $numPlayers = $_POST['numPlayers'] ?? '';

    if (!$sessionId || !$numPlayers) {
        echo json_encode(['status' => 'error', 'message' => 'Session ID and number of players are required']);
        exit;
    }

    try {
        // Mark the game as active
        $stmt = $pdo->prepare("UPDATE GameSessions SET IsActive = TRUE WHERE SessionID = ?");
        $stmt->execute([$sessionId]);

        // Check if the update was successful
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Game started successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to start the game. Invalid session ID.']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
