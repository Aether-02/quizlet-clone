<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$setId = $data['id'] ?? null;
$title = trim($data['title'] ?? '');
$description = trim($data['description'] ?? '');
$terms = $data['terms'] ?? [];

if (!$setId || empty($title) || empty($terms)) {
    echo json_encode(['success' => false, 'error' => 'Invalid data provided for update']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Update set metadata
    $stmt = $pdo->prepare("UPDATE study_sets SET title = ?, description = ? WHERE id = ?");
    $stmt->execute([$title, $description, $setId]);

    // 2. Remove old terms and insert updated ones using correct column `set_id`
    $stmt = $pdo->prepare("DELETE FROM terms WHERE set_id = ?");
    $stmt->execute([$setId]);

    $termStmt = $pdo->prepare("INSERT INTO terms (set_id, term, definition) VALUES (?, ?, ?)");
    foreach ($terms as $t) {
        $termStmt->execute([$setId, trim($t['term'] ?? ''), trim($t['definition'] ?? '')]);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>