<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE, GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db.php';

// Get ID from URL query string (?id=X) or JSON payload
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data['id'])) {
        $id = intval($data['id']);
    }
}

if ($id <= 0) {
    echo json_encode(["success" => false, "error" => "Study set ID is required"]);
    exit;
}

try {
    // Delete associated terms first (if foreign key cascade isn't active)
    $stmtTerms = $pdo->prepare("DELETE FROM terms WHERE set_id = ?");
    $stmtTerms->execute([$id]);

    // Delete the study set
    $stmtSet = $pdo->prepare("DELETE FROM study_sets WHERE id = ?");
    $stmtSet->execute([$id]);

    echo json_encode(["success" => true]);
} catch (\PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>