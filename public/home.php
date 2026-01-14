<?php
require_once 'lib.php';

$sort = $_GET['sort'] ?? 'hot';
$posts = get_posts($sort);

require 'header.php';
?>
        <div class="layout">
            <main>
                <div class="sort-bar">
                    <a href="?" class="sort-btn <?= $sort === 'hot' ? 'active' : '' ?>">Hot</a>
                    <a href="?sort=new" class="sort-btn <?= $sort === 'new' ? 'active' : '' ?>">New</a>
                    <a href="?sort=top" class="sort-btn <?= $sort === 'top' ? 'active' : '' ?>">Top</a>
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
                        <div class="post-meta">Posted <?= format_time($post['created_at']) ?> ago</div>
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