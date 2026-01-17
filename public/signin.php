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
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } elseif (is_banned($username)) {
        $error = 'This account has been banned.';
    } elseif (verify_account($username, $password)) {
        login_user($username);
        header("Location: ?");
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}

require 'header.php';
?>
        <div class="auth-form">
            <div class="sidebar-box">
                <h2>👋 Welcome Back</h2>
                
                <p class="sidebar-description text-center" style="margin-bottom: var(--space-xl);">
                    Sign in to your kloaq account to post and comment.
                </p>
                
                <?php if ($error): ?>
                <div class="error-box"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <form method="post">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" placeholder="Enter your username" 
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                    
                    <label class="form-label">Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required>
                    
                    <button type="submit">Sign In</button>
                </form>
                
                <div class="switch-link">
                    Don't have an account? <a href="?action=signup">Create one now</a>
                </div>
            </div>
            
            <div class="info-box">
                <strong>🔒 Your Privacy Matters</strong>
                <p>
                    kloaq doesn't track you. No cookies, no JavaScript, no surveillance. Your session is secure and anonymous.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
