<?php
require_once 'lib.php';

// Redirect if not logged in
if (!is_logged_in()) {
    header("Location: ?action=signin");
    exit;
}

$username = current_user();
$account = get_account_info($username);
$stats = MemoryStore::getUserStats($username);
$success = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete_content') {
        $deleted = MemoryStore::deleteUserContent($username);
        $success = "Deleted {$deleted['posts']} posts and {$deleted['comments']} comments from memory.";
        $stats = MemoryStore::getUserStats($username); // Refresh stats
    }
    
    if ($action === 'delete_account') {
        $confirm = $_POST['confirm_username'] ?? '';
        if ($confirm === $username) {
            delete_account($username);
            logout_user();
            header("Location: ?");
            exit;
        } else {
            $error = 'Username confirmation did not match.';
        }
    }
    
    if ($action === 'logout_all') {
        // Since we don't track sessions, just logout current
        logout_user();
        header("Location: ?");
        exit;
    }
}

require 'header.php';
?>
        <div class="layout">
            <main style="max-width: 800px;">
                <h1 style="margin-bottom: var(--space-xl); font-size: 2rem;">⚙️ Account Settings</h1>
                
                <?php if ($success): ?>
                <div class="success-box"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="error-box"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <!-- Account Info -->
                <div class="settings-box">
                    <h2>👤 Account Information</h2>
                    <div style="display: grid; gap: var(--space-md);">
                        <div>
                            <strong style="color: var(--text-secondary); font-size: 0.9rem;">Username</strong>
                            <p style="font-size: 1.1rem; margin-top: var(--space-xs);"><?= htmlspecialchars($username) ?></p>
                        </div>
                        <div>
                            <strong style="color: var(--text-secondary); font-size: 0.9rem;">Member Since</strong>
                            <p style="font-size: 1.1rem; margin-top: var(--space-xs);"><?= htmlspecialchars($account['created_at'] ?? 'Unknown') ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Content Stats -->
                <div class="settings-box">
                    <h2>📊 Your Content in Memory</h2>
                    <p>This is ephemeral content stored in RAM. It will be wiped when the server restarts.</p>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat"><?= $stats['posts'] ?></div>
                            <div style="color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; margin-top: var(--space-xs);">Posts</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat"><?= $stats['comments'] ?></div>
                            <div style="color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; margin-top: var(--space-xs);">Comments</div>
                        </div>
                    </div>
                </div>
                
                <!-- Delete Content -->
                <div class="settings-box">
                    <h2>🗑 Delete My Content</h2>
                    <p>Instantly remove all your posts and comments from RAM. Your account stays active.</p>
                    
                    <?php if ($stats['posts'] > 0 || $stats['comments'] > 0): ?>
                    <form method="post" onsubmit="return confirm('Delete all your posts and comments? This cannot be undone.');">
                        <input type="hidden" name="action" value="delete_content">
                        <button type="submit" class="btn-danger">Delete All My Content</button>
                    </form>
                    <?php else: ?>
                    <p style="color: #060; font-style: italic;">✓ You have no content in memory.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Danger Zone -->
                <div class="settings-box" style="border-color: #ff4500;">
                    <h2 style="color: #ff4500;">⚠ Danger Zone</h2>
                    
                    <h3>Delete Account</h3>
                    <p>Permanently delete your account and all content. This cannot be undone.</p>
                    
                    <details style="margin-top: 12px;">
                        <summary style="cursor: pointer; color: #ff4500; font-weight: bold;">I want to delete my account</summary>
                        <form method="post" style="margin-top: 12px;">
                            <input type="hidden" name="action" value="delete_account">
                            <p style="margin-bottom: 8px;">Type your username to confirm:</p>
                            <input type="text" name="confirm_username" placeholder="<?= htmlspecialchars($username) ?>" 
                                   style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 8px; width: 200px;">
                            <br>
                            <button type="submit" class="btn-danger">Permanently Delete Account</button>
                        </form>
                    </details>
                </div>
                
                <!-- Logout -->
                <div class="settings-box">
                    <h2>Session</h2>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="action" value="logout_all">
                        <button type="submit" class="btn-secondary">Log Out</button>
                    </form>
                </div>
                
                <!-- Privacy Info -->
                <div class="settings-box" style="background: var(--bg-sidebar); border: 2px solid var(--primary-light);">
                    <h2>🔒 How Your Data is Stored</h2>
                    
                    <div style="margin-bottom: var(--space-lg);">
                        <h3 style="color: var(--primary); margin-bottom: var(--space-sm);">Account (Persistent)</h3>
                        <ul class="sidebar-list">
                            <li>Username (public identifier)</li>
                            <li>Password hash (Argon2ID encrypted, never plain text)</li>
                            <li>Account creation date</li>
                            <li>Stored in encrypted SQLite database</li>
                        </ul>
                    </div>
                    
                    <div style="margin-bottom: var(--space-lg);">
                        <h3 style="color: var(--primary); margin-bottom: var(--space-sm);">Content (Ephemeral - RAM Only)</h3>
                        <ul class="sidebar-list">
                            <li>Posts (title, content, votes, timestamp)</li>
                            <li>Comments (content, votes, timestamp)</li>
                            <li>Your votes on other content</li>
                            <li><strong>Never written to disk</strong></li>
                            <li>Wiped on every server restart</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 style="color: var(--success); margin-bottom: var(--space-sm);">What We DON'T Store</h3>
                        <ul class="sidebar-list">
                            <li>IP addresses</li>
                            <li>Email or any personal info</li>
                            <li>Browser fingerprints</li>
                            <li>Tracking cookies</li>
                            <li>Third-party analytics</li>
                        </ul>
                    </div>
                </div>
            </main>
            
            <aside class="sidebar">
                <div class="sidebar-box">
                    <h3>Quick Actions</h3>
                    <div style="display: grid; gap: var(--space-sm);">
                        <a href="?" class="btn-secondary" style="text-decoration: none; text-align: center; display: block;">View Your Posts</a>
                        <a href="?action=submit" class="btn-primary">Create New Post</a>
                    </div>
                </div>
                
                <div class="sidebar-box">
                    <h3>Privacy First</h3>
                    <p class="sidebar-description">
                        kloaq is built from the ground up with privacy as the foundation, not an afterthought.
                    </p>
                    <ul class="sidebar-list">
                        <li>Zero JavaScript</li>
                        <li>No tracking pixels</li>
                        <li>Tor Browser compatible</li>
                        <li>RAM-only content</li>
                    </ul>
                </div>
                
                <div class="sidebar-box">
                    <h3>Need Help?</h3>
                    <p class="sidebar-description">
                        Have questions about your account or privacy? Check out our documentation or community guidelines.
                    </p>
                </div>
            </aside>
        </div>
    </div>
</body>
</html>
