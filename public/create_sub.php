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
            <main style="max-width: 600px;">
                <h1 style="margin-bottom: 20px;">Create a subKloaq</h1>

                <?php if ($error): ?>
                    <div class="error-box"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="sidebar-box create-form">
                    <form method="post">
                        <input type="text" name="name" placeholder="name (e.g. privacy_news)" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required minlength="3" maxlength="21" pattern="[a-z0-9_]+" title="lowercase letters, numbers, underscores">
                        <input type="text" name="title" placeholder="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required minlength="3" maxlength="60">
                        <textarea name="description" placeholder="description (optional)" maxlength="200"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                        <button type="submit">Create</button>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
