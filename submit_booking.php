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
    
    $user_id = $_SESSION['user_id'] ?? null;
    
    
    $tour_id = intval($_POST['tour_id'] ?? 0);
    $tour_title = trim($_POST['tour_name'] ?? '');
    $tour_price = floatval($_POST['tour_price'] ?? 0);
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_email = trim($_POST['customer_email'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    $number_of_people = intval($_POST['number_of_people'] ?? 1);
    $preferred_date = trim($_POST['travel_date'] ?? '');
    $special_requests = trim($_POST['special_requests'] ?? '');
    
    
    $total_price = $tour_price * $number_of_people;
    
    
    if (empty($tour_title) || empty($customer_name) || empty($customer_email) || empty($preferred_date)) {
        throw new Exception('Please fill in all required fields');
    }
    
    if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please enter a valid email address');
    }
    
    if ($number_of_people < 1) {
        throw new Exception('Number of people must be at least 1');
    }
    
    
    $date = new DateTime($preferred_date);
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    
    if ($date < $today) {
        throw new Exception('Travel date must be in the future');
    }
    
    
    $db = getDBConnection();
    $stmt = $db->prepare("INSERT INTO tour_bookings (tour_id, tour_title, customer_name, customer_email, customer_phone, number_of_people, total_price, preferred_date, special_requests) 
                         VALUES (:tour_id, :tour_title, :customer_name, :customer_email, :customer_phone, :number_of_people, :total_price, :preferred_date, :special_requests)");
    
    $result = $stmt->execute([
        'tour_id' => $tour_id > 0 ? $tour_id : null,
        'tour_title' => $tour_title,
        'customer_name' => $customer_name,
        'customer_email' => $customer_email,
        'customer_phone' => $customer_phone,
        'number_of_people' => $number_of_people,
        'total_price' => $total_price,
        'preferred_date' => $preferred_date,
        'special_requests' => $special_requests
    ]);
    
    if ($result) {
        $total_price = $tour_price * $number_of_people;
        echo json_encode([
            'success' => true,
            'message' => 'Booking request submitted successfully! Total: ₱' . number_format($total_price, 2) . '. We\'ll contact you shortly to confirm your reservation.'
        ]);
    } else {
        throw new Exception('Failed to save booking');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
