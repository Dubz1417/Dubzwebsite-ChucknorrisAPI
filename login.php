<?php
require_once 'security.php';
require_once 'db_config.php';

initSecureSession();
setSecurityHeaders();


if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password';
        } elseif (!checkLoginAttempts($username)) {
            $error = 'Too many login attempts. Please try again in 15 minutes.';
        } else {
            try {
                $db = getDBConnection();
                $stmt = $db->prepare("SELECT id, username, password, full_name, role FROM users WHERE username = :username");
                $stmt->execute(['username' => $username]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password'])) {
                    
                    clearLoginAttempts($username);
                    session_regenerate_id(true); 
                    
                    $_SESSION['logged_in'] = true;
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = $user['role'] ?? 'user';
                    
                    header('Location: index.php');
                    exit;
                } else {
                    recordLoginAttempt($username);
                    $error = 'Invalid username or password';
                }
            } catch (PDOException $e) {
                $error = 'Login failed. Please try again.';
            }
        }
    }
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dubz Adventours</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="login-page">
        <div class="login-container">
            <div class="login-logo">
                <img src="attached_assets/dubz Official LOGO_1761442704294.png" alt="Dubz Adventours Logo">
            </div>
            <div class="login-box transparent-box">
                <h1>Welcome</h1>
                <p class="login-subtitle">Login to For Mountain Expedition</p>
                
                <?php if ($error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST" class="login-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required placeholder="" autocomplete="username">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required placeholder="">
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-full">Login</button>
                </form>
                
                <div class="login-switch">
                    <p> <a href="signup.php" class="switch-link">Sign up here</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
