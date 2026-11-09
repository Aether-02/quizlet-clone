<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'db.php';

try {
    // Fetch all study sets along with the count of terms using a LEFT JOIN
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
} catch (\PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>