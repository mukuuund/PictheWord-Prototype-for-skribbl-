<?php
include 'db_connection.php';

$sessionId = $_GET['sessionId'] ?? '';

if (!$sessionId) {
    echo json_encode(['status' => 'error', 'message' => 'Session ID is required']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM GameSessions WHERE SessionID = ?");
$stmt->execute([$sessionId]);
$session = $stmt->fetch();

if ($session) {
    echo json_encode(['status' => $session['IsActive'] ? 'active' : 'waiting']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Session not found']);
}
?>
