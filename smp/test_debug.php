<?php
require 'config.php';
try {
    $pdo->query("SELECT 1");
    echo "DB Connection OK";
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
}
phpinfo();
?>
