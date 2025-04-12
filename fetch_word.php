<?php
include 'db_connection.php';

// Define a function to fetch the word
function fetchWord($sessionId, $pdo) {
    if (!$sessionId) {
        throw new Exception('Session ID is required');
    }

    // Fetch a random word from the Words table
    $stmt = $pdo->query("SELECT WordID, Word FROM Words ORDER BY RAND() LIMIT 1");
    $wordData = $stmt->fetch();

    if ($wordData) {
        $wordId = $wordData['WordID'];
        $word = $wordData['Word'];

        // Save the selected word to the session in the GameSessions table
        $stmt = $pdo->prepare("UPDATE GameSessions SET WordID = ? WHERE SessionID = ?");
        $stmt->execute([$wordId, $sessionId]);

        return $word;
    } else {
        throw new Exception('No words found in the database');
    }
}

// Handle both HTTP requests and inclusion
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $sessionId = $_GET['sessionId'] ?? '';

    try {
        $word = fetchWord($sessionId, $pdo);
        echo json_encode(['status' => 'success', 'word' => $word]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
