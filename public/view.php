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
        <a href="?" class="back-link">Back to all posts</a>
        
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
                        <div class="post-meta">Posted <?= format_time($post['created_at']) ?> ago</div>
                        <h1 class="post-title"><?= htmlspecialchars($post['title']) ?></h1>
                        <div class="post-content-full">
                            <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="comment-form">
                    <form method="post" action="?action=comment&post_id=<?= $post['id'] ?>">
                        <textarea name="content" placeholder="What are your thoughts?" required></textarea>
                        <button type="submit">Comment</button>
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
                            <span><?= format_time($comment['created_at']) ?> ago</span>
                            <span style="margin-left: 8px;"><?= $comment['votes'] ?> points</span>
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
                        <div class="comment-form">
                            <form method="post" action="?action=comment&post_id=<?= $post_id ?>&parent_id=<?= $comment['id'] ?>">
                                <textarea name="content" placeholder="Write a reply..." required></textarea>
                                <button type="submit">Reply</button>
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