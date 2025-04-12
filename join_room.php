<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $roomCode = $_POST['roomCode'] ?? '';

    if (!$username || !$roomCode) {
        echo json_encode(['status' => 'error', 'message' => 'Username and room code are required']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Find the session
        $stmt = $pdo->prepare("SELECT SessionID FROM GameSessions WHERE RoomCode = ?");
        $stmt->execute([$roomCode]);
        $sessionId = $stmt->fetchColumn();

        if (!$sessionId) {
            throw new Exception('Room not found');
        }

        // Insert the player
        $stmt = $pdo->prepare("INSERT INTO Users (Username, IsHost) VALUES (?, FALSE)");
        $stmt->execute([$username]);
        $playerId = $pdo->lastInsertId();

        // Add player to the session
        $stmt = $pdo->prepare("INSERT INTO PlayersInSessions (SessionID, PlayerID) VALUES (?, ?)");
        $stmt->execute([$sessionId, $playerId]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'sessionId' => $sessionId]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>
