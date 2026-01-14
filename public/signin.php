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
                <h2 style="text-align: center; margin-bottom: 20px;">Sign In</h2>
                
                <?php if ($error): ?>
                <div class="error-box"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <form method="post">
                    <input type="text" name="username" placeholder="Username" 
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit">Sign In</button>
                </form>
                
                <div class="switch-link">
                    Don't have an account? <a href="?action=signup">Sign up</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
