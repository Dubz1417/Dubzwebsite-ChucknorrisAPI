<?php
session_start();
require_once 'db_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        throw new Exception('Please fill in all required fields');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please enter a valid email address');
    }
    
    
    $db = getDBConnection();
    $stmt = $db->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (:name, :email, :phone, :subject, :message)");
    
    $result = $stmt->execute([
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'subject' => $subject,
        'message' => $message
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Thank you for your message! We\'ll get back to you within 24 hours.'
        ]);
    } else {
        throw new Exception('Failed to save message');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
