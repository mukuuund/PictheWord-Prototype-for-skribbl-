<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sessionId = $_POST['sessionId'] ?? '';
    $message = $_POST['message'] ?? '';
    $username = $_POST['username'] ?? '';

    if (!$sessionId || !$message || !$username) {
        echo json_encode(['status' => 'error', 'message' => 'Session ID, message, and username are required']);
        exit;
    }

    try {
        // Fetch the word for the session
        $stmt = $pdo->prepare("SELECT w.Word FROM GameSessions gs
                                JOIN Words w ON gs.WordID = w.WordID
                                WHERE gs.SessionID = ?");
        $stmt->execute([$sessionId]);
        $word = $stmt->fetchColumn();

        // Check if the message matches the word
        if (strcasecmp(trim($message), trim($word)) === 0) {
            echo json_encode(['status' => 'success', 'message' => 'Correct guess!', 'gameOver' => true]);
        } else {
            echo json_encode(['status' => 'success', 'message' => $username . ': ' . htmlspecialchars($message), 'gameOver' => false]);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
