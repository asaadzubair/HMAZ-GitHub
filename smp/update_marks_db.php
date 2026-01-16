<?php
require 'config.php';

try {
    // Add unique constraint to marks table to prevent duplicates
    // We use IGNORE to avoid error if it already exists, but MySQL syntax for ADD UNIQUE INDEX IF NOT EXISTS is tricky in older versions.
    // simpler to try catch
    try {
        $pdo->exec("ALTER TABLE marks ADD UNIQUE INDEX unique_mark_entry (student_id, subject_id, exam_type)");
        echo "Added unique constraint to marks table.<br>";
    } catch (PDOException $e) {
        // Likely already exists or Duplicate entries exist preventing the index
        // If duplicates exist, we might need to clean them up.
        // For this task, let's assume clean state or handle it.
        echo "Notice: " . $e->getMessage() . "<br>";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
