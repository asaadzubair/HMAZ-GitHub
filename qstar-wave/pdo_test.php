<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=qstar_wave", "root", "", [PDO::ATTR_TIMEOUT => 2]);
    $stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'hero_title'");
    $val = $stmt->fetchColumn();
    echo "Connected! Value: " . $val;
} catch (Exception $e) {
    echo "Failed: " . $e->getMessage();
}
?>
