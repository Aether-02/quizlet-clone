<?php
header('Content-Type: application/json');
require_once 'db.php';

// Get JSON POST body
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['title'], $data['user_id'], $data['terms']) || empty($data['terms'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid input data']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Insert into study_sets
    $stmt = $pdo->prepare("INSERT INTO study_sets (user_id, title, description, visibility) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $data['user_id'],
        $data['title'],
        $data['description'] ?? '',
        $data['visibility'] ?? 'public'
    ]);
    
    $setId = $pdo->lastInsertId();

    // 2. Insert terms in batch
    $termStmt = $pdo->prepare("INSERT INTO terms (set_id, term, definition, card_order) VALUES (?, ?, ?, ?)");
    
    foreach ($data['terms'] as $index => $item) {
        $termStmt->execute([
            $setId,
            $item['term'],
            $item['definition'],
            $index
        ]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'set_id' => $setId, 'message' => 'Study set created successfully!']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>