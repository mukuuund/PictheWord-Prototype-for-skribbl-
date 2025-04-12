<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';

    if (!$username) {
        echo json_encode(['status' => 'error', 'message' => 'Username is required']);
        exit;
    }

    $roomCode = bin2hex(random_bytes(3)); // Generate a 6-character room code

    try {
        $pdo->beginTransaction();

        // Insert the host into the Users table
        $stmt = $pdo->prepare("INSERT INTO Users (Username, IsHost) VALUES (?, TRUE)");
        $stmt->execute([$username]);
        $hostId = $pdo->lastInsertId();

        // Create a new game session
        $stmt = $pdo->prepare("INSERT INTO GameSessions (HostID, RoomCode) VALUES (?, ?)");
        $stmt->execute([$hostId, $roomCode]);
        $sessionId = $pdo->lastInsertId();

        $pdo->commit();
        echo json_encode(['status' => 'success', 'roomCode' => $roomCode, 'sessionId' => $sessionId]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>
