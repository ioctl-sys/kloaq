<?php
require_once 'lib.php';

if (!is_logged_in()) {
    header('Location: ?action=signin');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';

    $result = create_sub($name, $title, $description);
    if ($result['ok']) {
        header('Location: ?sub=' . urlencode($result['name']));
        exit;
    }
    $error = $result['error'] ?? 'Unable to create subKloaq.';
}

require 'header.php';
?>
        <div class="layout">
            <main style="max-width: 700px;">
                <a href="?action=subs" class="back-link">← Back to communities</a>
                
                <div class="sidebar-box" style="margin-bottom: var(--space-xl);">
                    <h1 style="margin-bottom: var(--space-md); font-size: 2rem;">🏗️ Create a Community</h1>
                    <p class="sidebar-description">
                        Start a new subKloaq community. Choose a descriptive name and write a clear description to help others understand what it's about.
                    </p>
                </div>

                <?php if ($error): ?>
                    <div class="error-box"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="sidebar-box create-form">
                    <form method="post">
                        <label class="form-label">Community Name</label>
                        <input type="text" name="name" placeholder="e.g., privacy_news, open_source, tech_talk" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required minlength="3" maxlength="21" pattern="[a-z0-9_]+" title="lowercase letters, numbers, underscores">
                        <p class="form-hint">3-21 characters. Lowercase letters, numbers, and underscores only.</p>
                        
                        <label class="form-label">Display Title</label>
                        <input type="text" name="title" placeholder="A friendly title for your community" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required minlength="3" maxlength="60">
                        <p class="form-hint">This is shown to users (3-60 characters).</p>
                        
                        <label class="form-label">Description (Optional)</label>
                        <textarea name="description" placeholder="What is this community about? What kind of posts belong here?" maxlength="200"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                        <p class="form-hint">Help others understand your community's purpose (max 200 characters).</p>
                        
                        <button type="submit">Create Community</button>
                    </form>
                </div>
                
                <div class="info-box">
                    <strong>📌 Community Guidelines</strong>
                    <p style="margin-bottom: var(--space-sm);">
                        • Choose a clear, descriptive name<br>
                        • Write a helpful description<br>
                        • Communities are public and anyone can post<br>
                        • Stay within platform rules and guidelines
                    </p>
                </div>
            </main>
            
            <aside class="sidebar">
                <div class="sidebar-box">
                    <h3>Naming Tips</h3>
                    <ul class="sidebar-list">
                        <li>Use lowercase only</li>
                        <li>Be specific and clear</li>
                        <li>Avoid generic names</li>
                        <li>Use underscores for spaces</li>
                    </ul>
                </div>
                
                <div class="sidebar-box">
                    <h3>Good Examples</h3>
                    <p class="sidebar-description">
                        <strong>privacy_tools</strong><br>
                        <em>"Tools and software for digital privacy"</em>
                    </p>
                    <p class="sidebar-description">
                        <strong>tech_news</strong><br>
                        <em>"Latest technology news and updates"</em>
                    </p>
                    <p class="sidebar-description" style="margin-bottom: 0;">
                        <strong>open_source</strong><br>
                        <em>"Discussions about FOSS projects"</em>
                    </p>
                </div>
            </aside>
        </div>
    </div>
</body>
</html>
