<?php
require_once 'auth_check.php';
require_once 'security.php';
require_once 'db_config.php';

initSecureSession();
setSecurityHeaders();

header('Content-Type: application/json');


$user_role = $_SESSION['role'] ?? 'user';
if ($user_role !== 'admin') {
    logSecurityEvent('unauthorized_booking_update', 'Non-admin user attempted to update booking status');
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Access denied. Only administrators can update booking status.'
    ]);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

try {
    
    $booking_id = intval($_POST['booking_id'] ?? 0);
    $new_status = trim($_POST['status'] ?? '');
    
    
    if ($booking_id <= 0) {
        throw new Exception('Invalid booking ID');
    }
    
   
    $allowed_statuses = ['pending', 'confirmed', 'cancelled'];
    if (!in_array($new_status, $allowed_statuses)) {
        throw new Exception('Invalid status. Must be: pending, confirmed, or cancelled');
    }
    
   
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($csrf_token)) {
        throw new Exception('Invalid security token. Please refresh the page and try again.');
    }
    
    
    $db = getDBConnection();
    
    
    $stmt = $db->prepare("SELECT tour_title, customer_name, status FROM tour_bookings WHERE id = :id");
    $stmt->execute(['id' => $booking_id]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        throw new Exception('Booking not found');
    }
    
    $old_status = $booking['status'];
    
    
    $update_stmt = $db->prepare("UPDATE tour_bookings SET status = :status WHERE id = :id");
    $result = $update_stmt->execute([
        'status' => $new_status,
        'id' => $booking_id
    ]);
    
    if ($result) {
        
        logSecurityEvent('booking_status_updated', 
            "Booking #$booking_id ({$booking['tour_title']} - {$booking['customer_name']}) status changed from '$old_status' to '$new_status'");
        
        
        echo json_encode([
            'success' => true,
            'message' => "Booking #$booking_id status updated to '$new_status'",
            'booking_id' => $booking_id,
            'new_status' => $new_status,
            'old_status' => $old_status
        ]);
    } else {
        throw new Exception('Failed to update booking status');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
