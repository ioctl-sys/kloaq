<?php
require_once 'lib.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sub = trim($_POST['sub'] ?? 'main');
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    
    if (strlen($title) < 3) {
        $error = 'Title must be at least 3 characters.';
    } elseif (strlen($content) < 10) {
        $error = 'Content must be at least 10 characters.';
    } else {
        $id = create_post($title, $content, $sub);
        if ($id === false) {
            $error = 'Your account is restricted and cannot post right now.';
        } else {
            header("Location: ?action=view&id=$id");
            exit;
        }
    }
}

require 'header.php';
?>
        <div class="layout">
            <main style="max-width: 700px;">
                <a href="?" class="back-link">← Back to home</a>
                
                <div class="sidebar-box" style="margin-bottom: var(--space-xl);">
                    <h1 style="margin-bottom: var(--space-md); font-size: 2rem;">📝 Create a Post</h1>
                    <p class="sidebar-description">
                        Share your thoughts, links, or discussions with the community. Your post will be anonymous and stored ephemerally in RAM.
                    </p>
                </div>
                
                <?php if ($error): ?>
                <div class="error-box">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>
                
                <div class="sidebar-box create-form">
                    <form method="post">
                        <label class="form-label">Community (subKloaq)</label>
                        <input type="text" name="sub" placeholder="e.g., privacy, tech, news" value="<?= htmlspecialchars($_POST['sub'] ?? ($_GET['sub'] ?? 'main')) ?>" required minlength="3" maxlength="21" pattern="[a-z0-9_]+" title="lowercase letters, numbers, underscores">
                        <p class="form-hint">Lowercase letters, numbers, and underscores only. Default is "main".</p>
                        
                        <label class="form-label">Title</label>
                        <input type="text" name="title" placeholder="What's your post about?" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required minlength="3" maxlength="200">
                        <p class="form-hint">Be clear and descriptive (minimum 3 characters).</p>
                        
                        <label class="form-label">Content</label>
                        <textarea name="content" placeholder="Share your thoughts, provide context, or explain your link..." required minlength="10"><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                        <p class="form-hint">Minimum 10 characters. You can use line breaks for formatting.</p>
                        
                        <button type="submit">Publish Post</button>
                    </form>
                </div>
                
                <div class="info-box">
                    <strong>📌 Posting Guidelines</strong>
                    <p style="margin-bottom: var(--space-sm);">
                        • Posts are stored in RAM only and will be wiped on server restart<br>
                        • You can delete your content anytime from Settings<br>
                        • Be respectful and follow community standards<br>
                        • No tracking, cookies, or JavaScript—ever
                    </p>
                </div>
            </main>
            
            <aside class="sidebar">
                <div class="sidebar-box">
                    <h3>Posting Tips</h3>
                    <ul class="sidebar-list">
                        <li>Choose a relevant subKloaq</li>
                        <li>Write a clear, descriptive title</li>
                        <li>Provide context in your content</li>
                        <li>Engage with comments</li>
                    </ul>
                </div>
                
                <div class="sidebar-box">
                    <h3>Privacy Notice</h3>
                    <p class="sidebar-description">
                        Your post is completely anonymous. We don't store IP addresses, browser fingerprints, or any identifying metadata.
                    </p>
                </div>
                
                <div class="sidebar-box">
                    <h3>Browse Communities</h3>
                    <p class="sidebar-description">
                        Not sure which community to post in?
                    </p>
                    <a href="?action=subs" class="btn-secondary" style="width: 100%; text-align: center; text-decoration: none; display: block;">View All Communities</a>
                </div>
            </aside>
        </div>
    </div>
</body>
</html>