<?php
require_once 'lib.php';

if (!is_admin()) {
    http_response_code(403);
    require 'header.php';
    echo '<div class="layout"><main><h1>403</h1><p>Admin access required.</p></main></div></div></body></html>';
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'ban') {
            $u = trim($_POST['username'] ?? '');
            if ($u === '') throw new Exception('Username required.');
            ban_account($u);
            $success = "Banned $u";
        } elseif ($action === 'unban') {
            $u = trim($_POST['username'] ?? '');
            if ($u === '') throw new Exception('Username required.');
            unban_account($u);
            $success = "Unbanned $u";
        } elseif ($action === 'restrict') {
            $u = trim($_POST['username'] ?? '');
            if ($u === '') throw new Exception('Username required.');
            restrict_account($u);
            $success = "Restricted $u";
        } elseif ($action === 'unrestrict') {
            $u = trim($_POST['username'] ?? '');
            if ($u === '') throw new Exception('Username required.');
            unrestrict_account($u);
            $success = "Unrestricted $u";
        } elseif ($action === 'delete_account') {
            $u = trim($_POST['username'] ?? '');
            if ($u === '') throw new Exception('Username required.');
            delete_account($u);
            $success = "Deleted account $u (and RAM content if present)";
        } elseif ($action === 'delete_post') {
            $id = (int)($_POST['post_id'] ?? 0);
            if ($id <= 0) throw new Exception('Post ID required.');
            $deleted = MemoryStore::deletePostById($id);
            $success = "Deleted {$deleted['posts']} post(s) and {$deleted['comments']} related comment(s).";
        } elseif ($action === 'delete_comment') {
            $id = (int)($_POST['comment_id'] ?? 0);
            if ($id <= 0) throw new Exception('Comment ID required.');
            $deleted = MemoryStore::deleteCommentById($id);
            $success = "Deleted {$deleted['comments']} comment(s).";
        } elseif ($action === 'delete_sub') {
            $name = trim($_POST['sub'] ?? '');
            if ($name === '') throw new Exception('Sub name required.');
            $res = MemoryStore::deleteSub($name);
            if (!$res['ok']) throw new Exception($res['error'] ?? 'Unable to delete sub.');
            $success = "Deleted sub /s/{$name} and removed {$res['posts']} post(s), {$res['comments']} comment(s).";
        } elseif ($action === 'purge_all') {
            MemoryStore::purgeAllContent();
            $success = 'Purged all RAM content (posts/comments/votes/subs reset to main).';
        } else {
            throw new Exception('Unknown action.');
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$accounts = list_accounts(200);
$subs = get_subs();
$posts = MemoryStore::getPosts();
$comments = MemoryStore::getComments();

require 'header.php';
?>
        <div class="layout">
            <main style="max-width: 900px;">
                <h1 style="margin-bottom: 20px;">🛡 Admin</h1>

                <?php if ($success): ?>
                    <div class="success-box"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="error-box"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="settings-box">
                    <h2>Overview</h2>
                    <p><strong>Accounts:</strong> <?= count($accounts) ?> (showing latest 200)</p>
                    <p><strong>subKloaqs:</strong> <?= count($subs) ?></p>
                    <p><strong>Posts in RAM:</strong> <?= count($posts) ?></p>
                    <p><strong>Comments in RAM:</strong> <?= count($comments) ?></p>
                </div>

                <div class="settings-box">
                    <h2>Account Controls</h2>
                    <form method="post" style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                        <input type="text" name="username" placeholder="username" required style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        <button class="btn-danger" type="submit" name="action" value="ban">Ban</button>
                        <button class="btn-secondary" type="submit" name="action" value="unban">Unban</button>
                        <button class="btn-danger" type="submit" name="action" value="restrict">Restrict (no post/comment)</button>
                        <button class="btn-secondary" type="submit" name="action" value="unrestrict">Unrestrict</button>
                        <button class="btn-danger" type="submit" name="action" value="delete_account" onclick="return confirm('Delete this account?');">Delete account</button>
                    </form>
                </div>

                <div class="settings-box">
                    <h2>Content Controls (RAM)</h2>
                    <form method="post" style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 10px;">
                        <input type="number" name="post_id" placeholder="post id" min="1" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width: 120px;">
                        <button class="btn-danger" type="submit" name="action" value="delete_post" onclick="return confirm('Delete this post and all its comments?');">Delete post</button>

                        <input type="number" name="comment_id" placeholder="comment id" min="1" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width: 140px;">
                        <button class="btn-danger" type="submit" name="action" value="delete_comment" onclick="return confirm('Delete this comment and its replies?');">Delete comment</button>
                    </form>

                    <form method="post" style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                        <input type="text" name="sub" placeholder="sub name (no /s/)" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; width: 200px;">
                        <button class="btn-danger" type="submit" name="action" value="delete_sub" onclick="return confirm('Delete sub and all posts in it?');">Delete sub</button>
                        <button class="btn-danger" type="submit" name="action" value="purge_all" onclick="return confirm('Purge ALL RAM content now?');">Purge all RAM content</button>
                    </form>
                </div>

                <div class="settings-box">
                    <h2>Accounts (latest 200)</h2>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                            <thead>
                                <tr>
                                    <th style="text-align:left; padding: 8px; border-bottom: 1px solid #edeff1;">Username</th>
                                    <th style="text-align:left; padding: 8px; border-bottom: 1px solid #edeff1;">Created</th>
                                    <th style="text-align:left; padding: 8px; border-bottom: 1px solid #edeff1;">Banned</th>
                                    <th style="text-align:left; padding: 8px; border-bottom: 1px solid #edeff1;">Restricted</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($accounts as $a): ?>
                                <tr>
                                    <td style="padding: 8px; border-bottom: 1px solid #f1f3f5;"><?= htmlspecialchars($a['username']) ?></td>
                                    <td style="padding: 8px; border-bottom: 1px solid #f1f3f5;"><?= htmlspecialchars($a['created_at']) ?></td>
                                    <td style="padding: 8px; border-bottom: 1px solid #f1f3f5;"><?= (int)$a['banned'] ? 'yes' : 'no' ?></td>
                                    <td style="padding: 8px; border-bottom: 1px solid #f1f3f5;"><?= (int)$a['restricted'] ? 'yes' : 'no' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="settings-box" style="background: #f8f9fa;">
                    <h2>Admin Access</h2>
                    <p>Admins are controlled via the <code>KLOAQ_ADMIN_USERS</code> environment variable (comma-separated usernames).</p>
                    <p style="font-size: 0.9rem; color: #787c7e;">Example: <code>export KLOAQ_ADMIN_USERS="alice,bob"</code></p>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
