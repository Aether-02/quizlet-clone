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

$title = trim($data['title'] ?? '');
$description = trim($data['description'] ?? '');
$terms = $data['terms'] ?? [];

if (empty($title) || empty($terms) || !is_array($terms)) {
    echo json_encode(['success' => false, 'error' => 'Invalid input data: Title or terms missing']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Insert study set metadata
    $stmt = $pdo->prepare("INSERT INTO study_sets (title, description) VALUES (?, ?)");
    $stmt->execute([$title, $description]);
    $setId = $pdo->lastInsertId();

    // 2. Insert terms (checking multiple possible key names for flexibility)
    $termStmt = $pdo->prepare("INSERT INTO terms (set_id, term, definition) VALUES (?, ?, ?)");
    $insertedCount = 0;

    foreach ($terms as $t) {
        $termVal = trim($t['term'] ?? $t['question'] ?? '');
        $defVal = trim($t['definition'] ?? $t['answer'] ?? '');

        if (!empty($termVal) && !empty($defVal)) {
            $termStmt->execute([$setId, $termVal, $defVal]);
            $insertedCount++;
        }
    }

    if ($insertedCount === 0) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Invalid input data: No valid term/definition pairs found']);
        exit;
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'set_id' => $setId]);
} catch (\PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>