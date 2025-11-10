<?php 
require_once 'auth_check.php';
require_once 'security.php';
require_once 'db_config.php';

initSecureSession();
setSecurityHeaders();


$user_role = $_SESSION['role'] ?? 'user';
if ($user_role !== 'admin') {
    
    logSecurityEvent('unauthorized_admin_access', 'User attempted to access admin dashboard without admin role');
    
    
    $_SESSION['error_message'] = 'Access denied. You do not have permission to view the admin dashboard.';
    header('Location: index.php');
    exit;
}

$page_title = "Admin Dashboard - Dubz Adventours";
include 'header.php'; 


$db = getDBConnection();

try {
    
    $stats_query = "SELECT 
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM tours) as total_tours,
        (SELECT COUNT(*) FROM tour_bookings) as total_bookings,
        (SELECT COUNT(*) FROM contact_messages) as total_messages,
        (SELECT COALESCE(SUM(total_price), 0) FROM tour_bookings) as total_revenue,
        (SELECT COUNT(*) FROM tour_bookings WHERE status = 'pending') as pending_bookings,
        (SELECT COUNT(*) FROM tour_bookings WHERE status = 'confirmed') as confirmed_bookings,
        (SELECT COUNT(*) FROM tour_bookings WHERE status = 'cancelled') as cancelled_bookings";
    
    $stats_stmt = $db->query($stats_query);
    $stats = $stats_stmt->fetch();
    
  
    $bookings_query = "SELECT 
        tb.id,
        tb.tour_title,
        tb.customer_name,
        tb.customer_email,
        tb.customer_phone,
        tb.number_of_people,
        tb.total_price,
        tb.preferred_date,
        tb.special_requests,
        tb.status,
        tb.created_at
    FROM tour_bookings tb
    ORDER BY tb.created_at DESC
    LIMIT 50";
    
    $bookings_stmt = $db->query($bookings_query);
    $bookings = $bookings_stmt->fetchAll();
    
   
    $messages_query = "SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 20";
    $messages_stmt = $db->query($messages_query);
    $messages = $messages_stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<main style="padding: 2rem 0; min-height: calc(100vh - 150px);">
    <div class="container">
        <h1 style="color: var(--primary-orange); margin-bottom: 2rem; text-align: center;">
            📊 Admin Dashboard
        </h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
            <div class="transparent-box" style="text-align: center; padding: 2rem;">
                <div style="font-size: 3rem; color: var(--primary-orange);">👥</div>
                <h2 style="font-size: 2.5rem; color: var(--primary-orange); margin: 0.5rem 0;"><?php echo $stats['total_users']; ?></h2>
                <p style="color: #666; font-size: 1.1rem;">Total Users</p>
            </div>
            
            <div class="transparent-box" style="text-align: center; padding: 2rem;">
                <div style="font-size: 3rem; color: var(--deep-blue);">🏔️</div>
                <h2 style="font-size: 2.5rem; color: var(--deep-blue); margin: 0.5rem 0;"><?php echo $stats['total_tours']; ?></h2>
                <p style="color: #666; font-size: 1.1rem;">Active Tours</p>
            </div>
            
            <div class="transparent-box" style="text-align: center; padding: 2rem;">
                <div style="font-size: 3rem; color: var(--sunset-yellow);">📅</div>
                <h2 style="font-size: 2.5rem; color: var(--sunset-yellow); margin: 0.5rem 0;"><?php echo $stats['total_bookings']; ?></h2>
                <p style="color: #666; font-size: 1.1rem;">Total Bookings</p>
            </div>
            
            <div class="transparent-box" style="text-align: center; padding: 2rem;">
                <div style="font-size: 3rem; color: #28a745;">💰</div>
                <h2 style="font-size: 2.5rem; color: #28a745; margin: 0.5rem 0;">₱<?php echo number_format($stats['total_revenue'], 2); ?></h2>
                <p style="color: #666; font-size: 1.1rem;">Total Revenue</p>
            </div>
        </div>
        
        
        <div class="transparent-box" style="margin-bottom: 2rem; padding: 1.5rem;">
            <h3 style="color: var(--primary-orange); margin-bottom: 1rem;">Booking Status Summary</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div style="padding: 1rem; background: rgba(255,193,7,0.1); border-radius: 10px;">
                    <span style="font-size: 1.5rem; color: #ffc107;">⏳</span>
                    <strong style="font-size: 1.5rem; color: #ffc107; margin-left: 0.5rem;"><?php echo $stats['pending_bookings']; ?></strong>
                    <p style="color: #666; margin: 0.5rem 0 0 0;">Pending</p>
                </div>
                <div style="padding: 1rem; background: rgba(40,167,69,0.1); border-radius: 10px;">
                    <span style="font-size: 1.5rem; color: #28a745;">✅</span>
                    <strong style="font-size: 1.5rem; color: #28a745; margin-left: 0.5rem;"><?php echo $stats['confirmed_bookings']; ?></strong>
                    <p style="color: #666; margin: 0.5rem 0 0 0;">Confirmed</p>
                </div>
                <div style="padding: 1rem; background: rgba(220,53,69,0.1); border-radius: 10px;">
                    <span style="font-size: 1.5rem; color: #dc3545;">❌</span>
                    <strong style="font-size: 1.5rem; color: #dc3545; margin-left: 0.5rem;"><?php echo $stats['cancelled_bookings']; ?></strong>
                    <p style="color: #666; margin: 0.5rem 0 0 0;">Cancelled</p>
                </div>
            </div>
        </div>
        
      
        <div class="transparent-box" style="margin-bottom: 2rem; padding: 1.5rem; overflow-x: auto;">
            <h3 style="color: var(--primary-orange); margin-bottom: 1rem;">📋 Recent Bookings</h3>
            <?php if (empty($bookings)): ?>
                <p style="text-align: center; color: #666; padding: 2rem;">No bookings yet.</p>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: rgba(255,107,53,0.1); border-bottom: 2px solid var(--primary-orange);">
                            <th style="padding: 1rem; text-align: left;">ID</th>
                            <th style="padding: 1rem; text-align: left;">Tour</th>
                            <th style="padding: 1rem; text-align: left;">Customer</th>
                            <th style="padding: 1rem; text-align: left;">Email</th>
                            <th style="padding: 1rem; text-align: left;">Phone</th>
                            <th style="padding: 1rem; text-align: center;">People</th>
                            <th style="padding: 1rem; text-align: right;">Total</th>
                            <th style="padding: 1rem; text-align: left;">Date</th>
                            <th style="padding: 1rem; text-align: center;">Status</th>
                            <th style="padding: 1rem; text-align: center;">Actions</th>
                            <th style="padding: 1rem; text-align: left;">Booked On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr id="booking-row-<?php echo $booking['id']; ?>" style="border-bottom: 1px solid rgba(0,0,0,0.1);">
                                <td style="padding: 1rem;">#<?php echo $booking['id']; ?></td>
                                <td style="padding: 1rem; font-weight: 500;"><?php echo htmlspecialchars($booking['tour_title']); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                                <td style="padding: 1rem;"><a href="mailto:<?php echo htmlspecialchars($booking['customer_email']); ?>" style="color: var(--deep-blue);"><?php echo htmlspecialchars($booking['customer_email']); ?></a></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($booking['customer_phone'] ?? 'N/A'); ?></td>
                                <td style="padding: 1rem; text-align: center;"><?php echo $booking['number_of_people']; ?></td>
                                <td style="padding: 1rem; text-align: right; font-weight: 600; color: #28a745;">₱<?php echo number_format($booking['total_price'], 2); ?></td>
                                <td style="padding: 1rem;"><?php echo date('M d, Y', strtotime($booking['preferred_date'])); ?></td>
                                <td style="padding: 1rem; text-align: center;" id="status-cell-<?php echo $booking['id']; ?>">
                                    <?php 
                                    $status = $booking['status'];
                                    $status_color = $status === 'confirmed' ? '#28a745' : ($status === 'cancelled' ? '#dc3545' : '#ffc107');
                                    $status_icon = $status === 'confirmed' ? '✅' : ($status === 'cancelled' ? '❌' : '⏳');
                                    ?>
                                    <span id="status-badge-<?php echo $booking['id']; ?>" style="background: <?php echo $status_color; ?>; color: white; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.85rem; font-weight: 500;">
                                        <?php echo $status_icon . ' ' . ucfirst($status); ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; text-align: center;" id="actions-cell-<?php echo $booking['id']; ?>">
                                    <?php if ($booking['status'] === 'pending'): ?>
                                        <button onclick="updateBookingStatus(<?php echo $booking['id']; ?>, 'confirmed')" style="background: #28a745; color: white; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer; font-size: 0.85rem; margin-right: 0.3rem;" title="Confirm Booking">
                                            ✅ Confirm
                                        </button>
                                        <button onclick="updateBookingStatus(<?php echo $booking['id']; ?>, 'cancelled')" style="background: #dc3545; color: white; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer; font-size: 0.85rem;" title="Cancel Booking">
                                            ❌ Cancel
                                        </button>
                                    <?php elseif ($booking['status'] === 'confirmed'): ?>
                                        <button onclick="updateBookingStatus(<?php echo $booking['id']; ?>, 'pending')" style="background: #ffc107; color: white; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer; font-size: 0.85rem; margin-right: 0.3rem;" title="Set to Pending">
                                            ⏳ Pending
                                        </button>
                                        <button onclick="updateBookingStatus(<?php echo $booking['id']; ?>, 'cancelled')" style="background: #dc3545; color: white; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer; font-size: 0.85rem;" title="Cancel Booking">
                                            ❌ Cancel
                                        </button>
                                    <?php else: // cancelled ?>
                                        <button onclick="updateBookingStatus(<?php echo $booking['id']; ?>, 'pending')" style="background: #ffc107; color: white; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer; font-size: 0.85rem; margin-right: 0.3rem;" title="Set to Pending">
                                            ⏳ Pending
                                        </button>
                                        <button onclick="updateBookingStatus(<?php echo $booking['id']; ?>, 'confirmed')" style="background: #28a745; color: white; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer; font-size: 0.85rem;" title="Confirm Booking">
                                            ✅ Confirm
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem; font-size: 0.9rem; color: #666;"><?php echo date('M d, Y H:i', strtotime($booking['created_at'])); ?></td>
                            </tr>
                            <?php if (!empty($booking['special_requests'])): ?>
                                <tr style="background: rgba(255,107,53,0.05);">
                                    <td colspan="11" style="padding: 0.5rem 1rem; font-size: 0.9rem; color: #666;">
                                        <strong>Special Requests:</strong> <?php echo htmlspecialchars($booking['special_requests']); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        
        <div class="transparent-box" style="padding: 1.5rem; overflow-x: auto;">
            <h3 style="color: var(--primary-orange); margin-bottom: 1rem;">✉️ Recent Contact Messages</h3>
            <?php if (empty($messages)): ?>
                <p style="text-align: center; color: #666; padding: 2rem;">No messages yet.</p>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: rgba(255,107,53,0.1); border-bottom: 2px solid var(--primary-orange);">
                            <th style="padding: 1rem; text-align: left;">ID</th>
                            <th style="padding: 1rem; text-align: left;">Name</th>
                            <th style="padding: 1rem; text-align: left;">Email</th>
                            <th style="padding: 1rem; text-align: left;">Phone</th>
                            <th style="padding: 1rem; text-align: left;">Subject</th>
                            <th style="padding: 1rem; text-align: left;">Message</th>
                            <th style="padding: 1rem; text-align: left;">Received</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $msg): ?>
                            <tr style="border-bottom: 1px solid rgba(0,0,0,0.1);">
                                <td style="padding: 1rem;">#<?php echo $msg['id']; ?></td>
                                <td style="padding: 1rem; font-weight: 500;"><?php echo htmlspecialchars($msg['name']); ?></td>
                                <td style="padding: 1rem;"><a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" style="color: var(--deep-blue);"><?php echo htmlspecialchars($msg['email']); ?></a></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($msg['phone'] ?? 'N/A'); ?></td>
                                <td style="padding: 1rem; font-weight: 500;"><?php echo htmlspecialchars($msg['subject']); ?></td>
                                <td style="padding: 1rem; max-width: 300px;">
                                    <?php 
                                    $message = htmlspecialchars($msg['message']);
                                    echo strlen($message) > 100 ? substr($message, 0, 100) . '...' : $message;
                                    ?>
                                </td>
                                <td style="padding: 1rem; font-size: 0.9rem; color: #666;"><?php echo date('M d, Y H:i', strtotime($msg['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>

const csrfToken = '<?php echo generateCSRFToken(); ?>';


function updateBookingStatus(bookingId, newStatus) {
   
    const confirmMessage = newStatus === 'confirmed' 
        ? 'Are you sure you want to CONFIRM this booking?' 
        : newStatus === 'cancelled'
        ? 'Are you sure you want to CANCEL this booking?'
        : 'Are you sure you want to set this booking to PENDING?';
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    
    const actionsCell = document.getElementById('actions-cell-' + bookingId);
    const originalButtons = actionsCell.innerHTML;
    actionsCell.innerHTML = '<span style="color: #666;">Updating...</span>';
    
    
    const formData = new FormData();
    formData.append('booking_id', bookingId);
    formData.append('status', newStatus);
    formData.append('csrf_token', csrfToken);
    
    fetch('update_booking_status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            
            const statusBadge = document.getElementById('status-badge-' + bookingId);
            const statusIcon = newStatus === 'confirmed' ? '✅' : (newStatus === 'cancelled' ? '❌' : '⏳');
            const statusColor = newStatus === 'confirmed' ? '#28a745' : (newStatus === 'cancelled' ? '#dc3545' : '#ffc107');
            
            statusBadge.innerHTML = statusIcon + ' ' + newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
            statusBadge.style.background = statusColor;
            
            
            updateActionButtons(bookingId, newStatus);
            
            
            showNotification('Success! Booking #' + bookingId + ' status updated to "' + newStatus + '"', 'success');
            
            
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            
            actionsCell.innerHTML = originalButtons;
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        
        actionsCell.innerHTML = originalButtons;
        showNotification('Network error. Please try again.', 'error');
        console.error('Error:', error);
    });
}


function updateActionButtons(bookingId, status) {
    const actionsCell = document.getElementById('actions-cell-' + bookingId);
    
    if (status === 'pending') {
        actionsCell.innerHTML = `
            <button onclick="updateBookingStatus(${bookingId}, 'confirmed')" style="background: #28a745; color: white; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer; font-size: 0.85rem; margin-right: 0.3rem;" title="Confirm Booking">
                ✅ Confirm
            </button>
            <button onclick="updateBookingStatus(${bookingId}, 'cancelled')" style="background: #dc3545; color: white; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer; font-size: 0.85rem;" title="Cancel Booking">
                ❌ Cancel
            </button>
        `;
    } else if (status === 'confirmed') {
        actionsCell.innerHTML = `
            <button onclick="updateBookingStatus(${bookingId}, 'pending')" style="background: #ffc107; color: white; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer; font-size: 0.85rem; margin-right: 0.3rem;" title="Set to Pending">
                ⏳ Pending
            </button>
            <button onclick="updateBookingStatus(${bookingId}, 'cancelled')" style="background: #dc3545; color: white; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer; font-size: 0.85rem;" title="Cancel Booking">
                ❌ Cancel
            </button>
        `;
    } else { // cancelled
        actionsCell.innerHTML = `
            <button onclick="updateBookingStatus(${bookingId}, 'pending')" style="background: #ffc107; color: white; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer; font-size: 0.85rem; margin-right: 0.3rem;" title="Set to Pending">
                ⏳ Pending
            </button>
            <button onclick="updateBookingStatus(${bookingId}, 'confirmed')" style="background: #28a745; color: white; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer; font-size: 0.85rem;" title="Confirm Booking">
                ✅ Confirm
            </button>
        `;
    }
}


function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.padding = '1rem 1.5rem';
    notification.style.borderRadius = '8px';
    notification.style.color = 'white';
    notification.style.fontWeight = '500';
    notification.style.zIndex = '10000';
    notification.style.boxShadow = '0 4px 15px rgba(0,0,0,0.3)';
    notification.style.maxWidth = '400px';
    notification.style.background = type === 'success' ? '#28a745' : '#dc3545';
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    
    setTimeout(() => {
        notification.style.transition = 'opacity 0.5s';
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 500);
    }, 3000);
}
</script>

<?php include 'footer.php'; ?>
