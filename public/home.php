<?php
require_once 'lib.php';

$sort = $_GET['sort'] ?? 'hot';
$sub = $_GET['sub'] ?? '';
$subObj = $sub ? get_sub($sub) : null;
$posts = get_posts($sort);

if ($subObj) {
    $posts = array_values(array_filter($posts, fn($p) => ($p['sub'] ?? 'main') === $subObj['name']));
}

require 'header.php';
?>
        <div class="layout">
            <main>
                <?php if ($subObj): ?>
                    <div class="sidebar-box" style="margin-bottom: 16px;">
                        <h3 style="margin-bottom: 6px;">/s/<?= htmlspecialchars($subObj['name']) ?></h3>
                        <?php if (!empty($subObj['description'])): ?>
                            <p style="font-size: 0.85rem; color: #4a4a4a;"><?= htmlspecialchars($subObj['description']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="sort-bar">
                    <a href="?<?= $subObj ? 'sub=' . urlencode($subObj['name']) . '&' : '' ?>sort=hot" class="sort-btn <?= $sort === 'hot' ? 'active' : '' ?>">Hot</a>
                    <a href="?<?= $subObj ? 'sub=' . urlencode($subObj['name']) . '&' : '' ?>sort=new" class="sort-btn <?= $sort === 'new' ? 'active' : '' ?>">New</a>
                    <a href="?<?= $subObj ? 'sub=' . urlencode($subObj['name']) . '&' : '' ?>sort=top" class="sort-btn <?= $sort === 'top' ? 'active' : '' ?>">Top</a>
                </div>
                
                <?php foreach ($posts as $post): ?>
                <div class="post">
                    <div class="vote-col">
                        <form method="post" action="?action=vote&type=post&id=<?= $post['id'] ?>&sort=<?= $sort ?>">
                            <input type="hidden" name="value" value="1">
                            <button type="submit" class="vote-btn" <?= ip_too_recent('post', $post['id']) ? 'disabled' : '' ?>>&#9650;</button>
                        </form>
                        <div class="vote-count"><?= $post['votes'] ?></div>
                        <form method="post" action="?action=vote&type=post&id=<?= $post['id'] ?>&sort=<?= $sort ?>">
                            <input type="hidden" name="value" value="-1">
                            <button type="submit" class="vote-btn" <?= ip_too_recent('post', $post['id']) ? 'disabled' : '' ?>>&#9660;</button>
                        </form>
                    </div>
                    <div class="post-content">
                        <div class="post-meta">
                            <a href="?sub=<?= urlencode($post['sub'] ?? 'main') ?>" style="color: #0079d3; text-decoration: none;">/s/<?= htmlspecialchars($post['sub'] ?? 'main') ?></a>
                            · Posted by <strong><?= htmlspecialchars($post['author'] ?? 'anon') ?></strong>
                            <?= format_time($post['created_at']) ?> ago
                        </div>
                        <h2 class="post-title"><a href="?action=view&id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a></h2>
                        <p class="post-preview"><?= htmlspecialchars(substr($post['content'], 0, 150)) ?><?= strlen($post['content']) > 150 ? '...' : '' ?></p>
                        <div class="post-actions">
                            <a href="?action=view&id=<?= $post['id'] ?>"><?= get_comment_count($post['id']) ?> comments</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($posts)): ?>
                <div class="post">
                    <div class="post-content">
                        <p>No posts yet. <a href="?action=submit">Be the first to submit!</a></p>
                    </div>
                </div>
                <?php endif; ?>
            </main>
            
            <aside class="sidebar">
                <div class="sidebar-box">
                    <h3>kloaq</h3>
                    <p style="font-size: 0.85rem; color: #4a4a4a; margin-bottom: 12px;">
                        Anonymous link aggregator. No tracking. No cookies. Zero JavaScript.
                    </p>
                    <a href="?action=submit" class="btn-primary">Create Post</a>
                </div>
                
                <div class="sidebar-box">
                    <h3>Rules</h3>
                    <ul style="font-size: 0.85rem; padding-left: 16px; color: #4a4a4a;">
                        <li>No tracking</li>
                        <li>No cookies</li>
                        <li>No JavaScript</li>
                        <li>Anonymous posting</li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</body>
</html>