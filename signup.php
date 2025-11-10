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
$success = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $full_name = sanitizeInput($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        
        foreach (['username' => $username, 'email' => $email, 'full_name' => $full_name] as $field => $value) {
            $attack = detectAttackPatterns($value);
            if ($attack) {
                logSecurityEvent('attack_detected', "Type: $attack, Field: $field");
                $error = 'Invalid input detected. Please use valid characters only.';
                break;
            }
        }
        
        
        if ($error === '' && (empty($username) || empty($email) || empty($password))) {
            $error = 'Please fill in all required fields';
        } elseif ($error === '' && strlen($username) < 3) {
            $error = 'Username must be at least 3 characters';
        } elseif ($error === '' && strlen($username) > 30) {
            $error = 'Username must be less than 30 characters';
        } elseif ($error === '' && !validateEmail($email)) {
            $error = 'Please enter a valid email address';
        } elseif ($error === '' && $password !== $confirm_password) {
            $error = 'Passwords do not match';
        } elseif ($error === '') {
            
            $passwordCheck = validatePassword($password);
            if (!$passwordCheck['valid']) {
                $error = implode('. ', $passwordCheck['errors']);
            }
        }
        
        if ($error === '') {
            try {
                $db = getDBConnection();
                
                
                $stmt = $db->prepare("SELECT id FROM users WHERE username = :username");
                $stmt->execute(['username' => $username]);
                if ($stmt->fetch()) {
                    $error = 'Username already exists';
                } else {
                    
                    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
                    $stmt->execute(['email' => $email]);
                    if ($stmt->fetch()) {
                        $error = 'Email already registered';
                    } else {
                        
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name) VALUES (:username, :email, :password, :full_name)");
                        $stmt->execute([
                            'username' => $username,
                            'email' => $email,
                            'password' => $hashed_password,
                            'full_name' => $full_name
                        ]);
                        
                        $success = 'Account created successfully! You can now login.';
                    }
                }
            } catch (PDOException $e) {
                $error = 'Registration failed. Please try again.';
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
    <title>Sign Up - Dubz Adventours</title>
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
                <h1>Create Account</h1>
                <p class="login-subtitle">Join us to explore amazing Philippine Mountains </p>
                
                <?php if ($error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
                    <div class="login-switch">
                        <p><a href="login.php" class="switch-link">Go to Login</a></p>
                    </div>
                <?php else: ?>
                    <form method="POST" class="login-form">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <div class="form-group">
                            <label for="full_name">Name </label>
                            <input type="text" id="full_name" name="full_name" placeholder="" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="username">Username </label>
                            <input type="text" id="username" name="username" required placeholder="" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address </label>
                            <input type="email" id="email" name="email" required placeholder="" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password </label>
                            <input type="password" id="password" name="password" required placeholder="">
                            <small style="color: #666; font-size: 0.85rem; margin-top: 4px; display: block;"></small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password </label>
                            <input type="password" id="confirm_password" name="confirm_password" required placeholder="">
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-full">Sign Up</button>
                    </form>
                    
                    <div class="login-switch">
                        <p> <a href="login.php" class="switch-link">Login here</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
