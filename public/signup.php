<?php
require_once 'lib.php';

// Redirect if already logged in
if (is_logged_in()) {
    header("Location: ?");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    
    if (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters.';
    } elseif (strlen($username) > 20) {
        $error = 'Username must be 20 characters or less.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = 'Username can only contain letters, numbers, and underscores.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        if (create_account($username, $password)) {
            login_user($username);
            header("Location: ?");
            exit;
        } else {
            $error = 'Username already taken.';
        }
    }
}

require 'header.php';
?>
        <div class="auth-form">
            <div class="sidebar-box">
                <h2 style="text-align: center; margin-bottom: 20px;">Create Account</h2>
                
                <?php if ($error): ?>
                <div class="error-box"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <form method="post">
                    <input type="text" name="username" placeholder="Username" 
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
                           required minlength="3" maxlength="20" 
                           pattern="[a-zA-Z0-9_]+" 
                           title="Letters, numbers, and underscores only">
                    <input type="password" name="password" placeholder="Password" required minlength="8">
                    <input type="password" name="confirm" placeholder="Confirm Password" required>
                    <button type="submit">Sign Up</button>
                </form>
                
                <div class="switch-link">
                    Already have an account? <a href="?action=signin">Sign in</a>
                </div>
            </div>
            
            <div style="margin-top: 20px; padding: 16px; background: #f8f9fa; border-radius: 4px; font-size: 0.85rem; color: #787c7e;">
                <strong>Privacy notice:</strong><br>
                • Your password is hashed with Argon2ID (never stored in plain text)<br>
                • Only your username and password hash are saved<br>
                • All posts/comments are stored in RAM only and will be wiped on server restart<br>
                • You can delete all your data anytime from Settings
            </div>
        </div>
    </div>
</body>
</html>
