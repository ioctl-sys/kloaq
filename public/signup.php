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
                <h2>🔐 Create Account</h2>
                
                <p class="sidebar-description text-center" style="margin-bottom: var(--space-xl);">
                    Join kloaq to publish anonymously and participate in communities.
                </p>
                
                <?php if ($error): ?>
                <div class="error-box"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <form method="post">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" placeholder="Choose a username" 
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
                           required minlength="3" maxlength="20" 
                           pattern="[a-zA-Z0-9_]+" 
                           title="Letters, numbers, and underscores only">
                    <p class="form-hint">3-20 characters. Letters, numbers, and underscores only.</p>
                    
                    <label class="form-label">Password</label>
                    <input type="password" name="password" placeholder="Create a password" required minlength="8">
                    <p class="form-hint">Minimum 8 characters. Choose a strong password.</p>
                    
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm" placeholder="Confirm your password" required>
                    
                    <button type="submit">Create Account</button>
                </form>
                
                <div class="switch-link">
                    Already have an account? <a href="?action=signin">Sign in instead</a>
                </div>
            </div>
            
            <div class="info-box">
                <strong>🔒 Privacy Notice</strong>
                <p>
                    • Your password is hashed with Argon2ID (never stored in plain text)<br>
                    • Only your username and password hash are saved<br>
                    • All posts/comments are stored in RAM only<br>
                    • No email required, no personal data collected<br>
                    • You can delete all your data anytime from Settings
                </p>
            </div>
        </div>
    </div>
</body>
</html>
