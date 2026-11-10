<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

try {
    if ($id > 0) {
        // Fetch a single study set and its terms (used by study.html)
        $stmt = $pdo->prepare("SELECT * FROM study_sets WHERE id = ?");
        $stmt->execute([$id]);
        $set = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$set) {
            echo json_encode(["success" => false, "error" => "Study set not found"]);
            exit;
        }

        $stmtTerms = $pdo->prepare("SELECT * FROM terms WHERE set_id = ? ORDER BY id ASC");
        $stmtTerms->execute([$id]);
        $terms = $stmtTerms->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "set" => $set,
            "terms" => $terms
        ]);
    } else {
        // Fetch all study sets with term counts (used by index.html dashboard)
        $sql = "SELECT s.*, COUNT(t.id) as term_count 
                FROM study_sets s 
                LEFT JOIN terms t ON s.id = t.set_id 
                GROUP BY s.id 
                ORDER BY s.created_at DESC";
                
        $stmt = $pdo->query($sql);
        $sets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "sets" => $sets
        ]);
    }
} catch (\PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>