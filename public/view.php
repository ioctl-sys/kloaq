<?php
require_once 'lib.php';

$id = $_GET['id'] ?? 0;
$post = get_post($id);

if (!$post) {
    http_response_code(404);
    require 'header.php';
    echo '<div class="layout"><main><h1>Post not found</h1><a href="?" class="back-link">Back to all posts</a></main></div></div></body></html>';
    exit;
}

require 'header.php';
?>
        <a href="?" class="back-link">← Back to all posts</a>

        <?php if (($_GET['err'] ?? '') === 'restricted'): ?>
            <div class="error-box">Your account is restricted and cannot comment right now.</div>
        <?php endif; ?>
        
        <div class="layout post-view">
            <main>
                <div class="post">
                    <div class="vote-col">
                        <form method="post" action="?action=vote&type=post&id=<?= $post['id'] ?>&sort=<?= $_GET['sort'] ?? 'hot' ?>">
                            <input type="hidden" name="value" value="1">
                            <button type="submit" class="vote-btn" <?= ip_too_recent('post', $post['id']) ? 'disabled' : '' ?>>&#9650;</button>
                        </form>
                        <div class="vote-count"><?= $post['votes'] ?></div>
                        <form method="post" action="?action=vote&type=post&id=<?= $post['id'] ?>&sort=<?= $_GET['sort'] ?? 'hot' ?>">
                            <input type="hidden" name="value" value="-1">
                            <button type="submit" class="vote-btn" <?= ip_too_recent('post', $post['id']) ? 'disabled' : '' ?>>&#9660;</button>
                        </form>
                    </div>
                    <div class="post-content">
                        <div class="post-meta">
                            <a href="?sub=<?= urlencode($post['sub'] ?? 'main') ?>" class="post-meta-sub">/s/<?= htmlspecialchars($post['sub'] ?? 'main') ?></a>
                            <span class="post-meta-separator">•</span>
                            <span>Posted by <strong><?= htmlspecialchars($post['author'] ?? 'anon') ?></strong></span>
                            <span class="post-meta-separator">•</span>
                            <span><?= format_time($post['created_at']) ?> ago</span>
                        </div>
                        <h1 class="post-title"><?= htmlspecialchars($post['title']) ?></h1>
                        <div class="post-content-full">
                            <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="comment-form">
                    <h3 style="margin-bottom: var(--space-md); color: var(--text-primary);">💬 Add a Comment</h3>
                    <form method="post" action="?action=comment&post_id=<?= $post['id'] ?>">
                        <textarea name="content" placeholder="What are your thoughts? (Minimum 3 characters)" required minlength="3"></textarea>
                        <button type="submit">Post Comment</button>
                    </form>
                </div>
                
                <div class="comment-thread">
                    <?php
                    function render_comments($post_id, $parent_id = null, $depth = 0) {
                        $comments = get_comments($post_id, $parent_id);
                        foreach ($comments as $comment):
                    ?>
                    <div class="comment">
                        <div class="comment-meta">
                            <strong><?= htmlspecialchars($comment['author'] ?? 'anon') ?></strong>
                            <span style="margin-left: 8px;"><?= $comment['votes'] ?> points</span>
                            <span style="margin-left: 8px;"><?= format_time($comment['created_at']) ?> ago</span>
                        </div>
                        <div class="comment-content"><?= htmlspecialchars($comment['content']) ?></div>
                        <div class="comment-actions">
                            <form method="post" action="?action=vote&type=comment&id=<?= $comment['id'] ?>&post_id=<?= $post_id ?>" style="display: inline;">
                                <input type="hidden" name="value" value="1">
                                <a href="#" onclick="this.closest('form').submit(); return false;">&#9650;</a>
                            </form>
                            <form method="post" action="?action=vote&type=comment&id=<?= $comment['id'] ?>&post_id=<?= $post_id ?>" style="display: inline;">
                                <input type="hidden" name="value" value="-1">
                                <a href="#" onclick="this.closest('form').submit(); return false;">&#9660;</a>
                            </form>
                            <a href="?action=reply&post_id=<?= $post_id ?>&parent_id=<?= $comment['id'] ?>">reply</a>
                        </div>
                        
                        <?php if ($depth < 5): ?>
                        <div class="comment-thread">
                            <?php render_comments($post_id, $comment['id'], $depth + 1); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['parent_id']) && $_GET['parent_id'] == $comment['id']): ?>
                        <div class="comment-form" style="margin-left: 0;">
                            <h4 style="margin-bottom: var(--space-sm); color: var(--text-primary); font-size: 0.9rem;">Reply to <?= htmlspecialchars($comment['author'] ?? 'anon') ?></h4>
                            <form method="post" action="?action=comment&post_id=<?= $post_id ?>&parent_id=<?= $comment['id'] ?>">
                                <textarea name="content" placeholder="Write your reply..." required minlength="3"></textarea>
                                <button type="submit">Post Reply</button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php
                        endforeach;
                    }
                    render_comments($post['id']);
                    ?>
                </div>
            </main>
        </div>
    </div>
</body>
</html>