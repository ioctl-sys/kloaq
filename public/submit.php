<?php
require_once 'lib.php';
require 'header.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    
    if (strlen($title) < 3) {
        $error = 'Title must be at least 3 characters.';
    } elseif (strlen($content) < 10) {
        $error = 'Content must be at least 10 characters.';
    } else {
        $id = create_post($title, $content);
        header("Location: ?action=view&id=$id");
        exit;
    }
}
?>
        <div class="layout">
            <main style="max-width: 600px;">
                <h1 style="margin-bottom: 20px;">Create a Post</h1>
                
                <?php if ($error): ?>
                <div style="background: #fee; border: 1px solid #fcc; padding: 12px; border-radius: 4px; margin-bottom: 16px; color: #c00;">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>
                
                <div class="sidebar-box create-form">
                    <form method="post">
                        <input type="text" name="title" placeholder="Title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required minlength="3">
                        <textarea name="content" placeholder="Text (optional)" required minlength="10"><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                        <button type="submit">Post</button>
                    </form>
                </div>
                
                <p style="font-size: 0.85rem; color: #787c7e; margin-top: 16px;">
                    By posting, you agree to not be tracked. Because you won't be.
                </p>
            </main>
        </div>
    </div>
</body>
</html>