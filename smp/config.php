<?php
// config.php
$host = 'localhost';
$user = 'root';
$pass = ''; // Default for Laragon/XAMPP
$dbname = 'smp_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

session_start();
define('SCHOOL_NAME', 'Government High School Karyal');
define('SCHOOL_ADDRESS', 'Tehsil Raiwind, District Lahore');
?>
