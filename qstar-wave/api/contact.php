<?php
require_once '../hmaz/includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $service = $_POST['service'] ?? '';
    $message = $_POST['message'] ?? '';

    if (empty($name) || empty($email) || empty($message)) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill all required fields.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO messages (name, email, phone, service_selected, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $service, $message]);
        echo json_encode(['status' => 'success', 'message' => 'Thank you! Your message has been sent.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Something went wrong. Please try again later.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
