<?php
header('Content-Type: application/json');
require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$setId = $data['id'] ?? null;
$title = trim($data['title'] ?? '');
$description = trim($data['description'] ?? '');
$terms = $data['terms'] ?? [];

if (!$setId || empty($title) || count($terms) < 2) {
    echo json_encode(['success' => false, 'error' => 'Invalid data provided for update']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Update set metadata
    $stmt = $pdo->prepare("UPDATE study_sets SET title = ?, description = ? WHERE id = ?");
    $stmt->execute([$title, $description, $setId]);

    // 2. Remove old terms and insert updated ones
    $stmt = $pdo->prepare("DELETE FROM terms WHERE study_set_id = ?");
    $stmt->execute([$setId]);

    $termStmt = $pdo->prepare("INSERT INTO terms (study_set_id, term, definition) VALUES (?, ?, ?)");
    foreach ($terms as $t) {
        $termStmt->execute([$setId, trim($t['term']), trim($t['definition'])]);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>