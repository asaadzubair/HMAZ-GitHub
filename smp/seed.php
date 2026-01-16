<?php
// seed.php
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    // 1. Connect to MySQL Server (No DB) to Create DB
    $pdo = new PDO("mysql:host=$host;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create Database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS smp_db");
    $pdo->exec("USE smp_db");
    
} catch (PDOException $e) {
    die("DB Connection/Creation Failed: " . $e->getMessage());
}

// require 'config.php'; // We don't need this here since we already connected above


try {
    // 1. Create Tables
    $sql = file_get_contents('setup.sql');
    $pdo->exec($sql);
    echo "Tables created successfully.<br>";

    // 2. Create Admin User
    $password = password_hash('admin123', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    try {
        $stmt->execute(['principal', $password, 'admin']);
        echo "Admin user created (User: principal, Pass: admin123).<br>";
    } catch (Exception $e) {
        // Ignore duplicate entry error
        echo "Admin user likely already exists.<br>";
    }

    // 3. Create Dummy Teachers
    $teachers = [
        ['Ali Raza', 'Mathematics', '9-A, 10-A', '0300-1234567'],
        ['Sara Khan', 'English', '9-B, 10-B', '0300-7654321'],
        ['Usman Ahmed', 'Science', '9-A', '0333-1122334']
    ];

    foreach ($teachers as $t) {
        $stmt = $pdo->prepare("INSERT INTO teachers (name, subjects_taught, classes_handled, phone) VALUES (?, ?, ?, ?)");
        $stmt->execute($t);
        // Create user login for teacher
        $teacherId = $pdo->lastInsertId();
        $userPass = password_hash('123456', PASSWORD_BCRYPT);
        $username = strtolower(str_replace(' ', '', $t[0]));
        $stmtUser = $pdo->prepare("INSERT INTO users (username, password, role, teacher_id) VALUES (?, ?, 'teacher', ?)");
        try {
             $stmtUser->execute([$username, $userPass, $teacherId]);
             echo "Teacher $t[0] created (User: $username, Pass: 123456).<br>";
        } catch(Exception $e) {}
    }

    // 4. Create Dummy Students
    $students = [
        ['Ali Hassan', '1001', '9-A', '2010-05-15', 'Ahmed Hassan', 'Active'],
        ['Bilal Ahmed', '1002', '9-A', '2010-03-20', 'Riaz Ahmed', 'Active'],
        ['Zainab Bibi', '1003', '9-B', '2010-07-10', 'Khalid Hussain', 'Active']
    ];

    foreach ($students as $s) {
        $stmt = $pdo->prepare("INSERT INTO students (name, roll_number, class_section, dob, parent_name, status) VALUES (?, ?, ?, ?, ?, ?)");
        try {
            $stmt->execute($s);
        } catch(Exception $e) {}
    }
    
    echo "Dummy data seeded successfully.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
