<?php
header('Content-Type: application/json');
require_once 'db.php';

// Check if a specific set ID is requested
if (isset($_GET['id'])) {
    $setId = intval($_GET['id']);
    
    // Fetch Set Details
    $stmt = $pdo->prepare("SELECT * FROM study_sets WHERE id = ?");
    $stmt->execute([$setId]);
    $set = $stmt->fetch();

    if (!$set) {
        echo json_encode(['error' => 'Study set not found']);
        exit;
    }

    // Fetch Associated Terms
    $termStmt = $pdo->prepare("SELECT id, term, definition, card_order FROM terms WHERE set_id = ? ORDER BY card_order ASC");
    $termStmt->execute([$setId]);
    $set['terms'] = $termStmt->fetchAll();

    echo json_encode($set);
} else {
    // Fetch all public study sets for the homepage/dashboard
    $stmt = $pdo->query("SELECT s.id, s.title, s.description, u.username, s.created_at, 
                        (SELECT COUNT(*) FROM terms t WHERE t.set_id = s.id) as term_count 
                        FROM study_sets s 
                        JOIN users u ON s.user_id = u.id 
                        ORDER BY s.created_at DESC");
    $sets = $stmt->fetchAll();
    
    echo json_encode($sets);
}
?>