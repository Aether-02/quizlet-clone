<?php
// Aiven Cloud MySQL Configuration
$host = 'mysql-35efe400-createstudyset.g.aivencloud.com';
$port = '13450';
$db   = 'defaultdb';
$user = 'avnadmin';
$pass = 'YOUR_AIVEN_PASSWORD'; // Replace with your copied Aiven password
$cert = __DIR__ . '/ca.pem';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_SSL_CA       => $cert,
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo json_encode(['error' => 'Aiven Database Connection Failed: ' . $e->getMessage()]);
    exit;
}
?>