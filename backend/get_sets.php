<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo json_encode(["success" => false, "error" => "Invalid study set ID"]);
    exit;
}

try {
    // Fetch the study set details
    $stmt = $pdo->prepare("SELECT * FROM study_sets WHERE id = ?");
    $stmt->execute([$id]);
    $set = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$set) {
        echo json_encode(["success" => false, "error" => "Study set not found"]);
        exit;
    }

    // Fetch all terms belonging to this study set
    $stmtTerms = $pdo->prepare("SELECT * FROM terms WHERE set_id = ? ORDER BY id ASC");
    $stmtTerms->execute([$id]);
    $terms = $stmtTerms->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "set" => $set,
        "terms" => $terms
    ]);
} catch (\PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>