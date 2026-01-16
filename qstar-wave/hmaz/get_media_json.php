<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
check_auth();

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT id, filename as file_name, filepath as file_path FROM media ORDER BY created_at DESC");
    $media = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($media);
} catch (Exception $e) {
    echo json_encode([]);
}
