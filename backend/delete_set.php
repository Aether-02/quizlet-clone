<?php
header('Content-Type: application/json');
require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$setId = $data['id'] ?? null;

if (!$setId) {
    echo json_encode(['success' => false, 'error' => 'Study set ID is required']);
    exit;
}

try {
    // Foreign key constraints with ON DELETE CASCADE will handle terms automatically
    $stmt = $pdo->prepare("DELETE FROM study_sets WHERE id = ?");
    $stmt->execute([$setId]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>