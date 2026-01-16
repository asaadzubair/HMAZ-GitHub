<?php
require 'config.php';

try {
    // 1. Add Profile Fields to Users Table (if not exists)
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN name VARCHAR(100) AFTER role");
        $pdo->exec("ALTER TABLE users ADD COLUMN email VARCHAR(100) AFTER name");
        $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20) AFTER email");
        // Update admin name
        $pdo->exec("UPDATE users SET name = 'Principal', email = 'principal@school.com' WHERE username = 'principal'");
    } catch (Exception $e) { /* Ignore if exists */ }

    // 2. Create Classes Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS classes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        grade VARCHAR(20) NOT NULL, -- e.g. 9, 10
        section VARCHAR(10) NOT NULL, -- e.g. A, B
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(grade, section)
    )");

    // 3. Seed Classes
    $countClasses = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();
    if ($countClasses == 0) {
        $defaults = [
            ['9', 'A'], ['9', 'B'], ['10', 'A'], ['10', 'B']
        ];
        $stmt = $pdo->prepare("INSERT INTO classes (grade, section) VALUES (?, ?)");
        foreach($defaults as $d) {
            $stmt->execute($d);
        }
    }

    // 4. Seed Subjects (New)
    $countSubjects = $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
    if ($countSubjects == 0) {
        $subjects = [
            ['Mathematics', '9'], ['Mathematics', '10'],
            ['English', '9'], ['English', '10'],
            ['Urdu', '9'], ['Urdu', '10'],
            ['Physics', '9'], ['Physics', '10'],
            ['Chemistry', '9'], ['Chemistry', '10'],
            ['Biology', '9'], ['Biology', '10'],
            ['Computer Science', '9'], ['Computer Science', '10'],
            ['Islamiyat', '9'], ['Islamiyat', '10'],
            ['Pak Studies', '9'], ['Pak Studies', '10']
        ];
        $stmt = $pdo->prepare("INSERT INTO subjects (name, class_level) VALUES (?, ?)");
        foreach($subjects as $s) {
            $stmt->execute($s);
        }
        echo "Subjects seeded.<br>";
    }

    echo "Database schema updated successfully.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
