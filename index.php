<?php 
require_once 'auth_check.php';
$page_title = "Home - Philippines Travel & Tours";


$error_message = '';
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

include 'header.php'; 
?>

<main>
    <?php if ($error_message): ?>
        <div class="container" style="padding-top: 2rem;">
            <div style="background: rgba(220, 53, 69, 0.1); border-left: 4px solid #dc3545; padding: 1rem 1.5rem; border-radius: 8px; color: #721c24;">
                <strong>⚠️ Access Denied:</strong> <?php echo htmlspecialchars($error_message); ?>
            </div>
        </div>
    <?php endif; ?>
    
    <section class="hero">
        <div class="hero-content">
            <h1 class="hero-title">Explore the Philippine Mountains</h1>
            <p class="hero-subtitle">Ready for your Mountain Expedition?.</p>
            <a href="tours.php" class="btn btn-primary">Book With Us</a>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>
