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

$total_posts = count(get_posts('new'));
$total_subs = count(get_subs());
?>
        <?php if (!$subObj && empty($_GET['sort'])): ?>
        <div class="hero">
            <h1>📢 Anonymous Publishing, Zero Surveillance</h1>
            <p>
                kloaq is a privacy-first publishing platform. No JavaScript. No tracking. No cookies. 
                Just pure, unadulterated free speech powered entirely by server-side rendering.
            </p>
            <div class="hero-features">
                <div class="hero-feature">
                    <span class="hero-feature-icon">🔒</span>
                    <span>Zero Tracking</span>
                </div>
                <div class="hero-feature">
                    <span class="hero-feature-icon">🚫</span>
                    <span>No JavaScript</span>
                </div>
                <div class="hero-feature">
                    <span class="hero-feature-icon">🍪</span>
                    <span>No Cookies</span>
                </div>
                <div class="hero-feature">
                    <span class="hero-feature-icon">🌐</span>
                    <span>Tor-Friendly</span>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="layout">
            <main>
                <?php if ($subObj): ?>
                    <div class="sidebar-box" style="margin-bottom: var(--space-lg);">
                        <h3 style="margin-bottom: var(--space-sm); font-size: 1.5rem; text-transform: none;">/s/<?= htmlspecialchars($subObj['name']) ?></h3>
                        <?php if (!empty($subObj['description'])): ?>
                            <p class="sidebar-description"><?= htmlspecialchars($subObj['description']) ?></p>
                        <?php endif; ?>
                        <a href="?action=submit&sub=<?= urlencode($subObj['name']) ?>" class="btn-primary">Create Post in /s/<?= htmlspecialchars($subObj['name']) ?></a>
                    </div>
                <?php endif; ?>

                <div class="sort-bar">
                    <a href="?<?= $subObj ? 'sub=' . urlencode($subObj['name']) . '&' : '' ?>sort=hot" class="sort-btn <?= $sort === 'hot' ? 'active' : '' ?>">🔥 Hot</a>
                    <a href="?<?= $subObj ? 'sub=' . urlencode($subObj['name']) . '&' : '' ?>sort=new" class="sort-btn <?= $sort === 'new' ? 'active' : '' ?>">🆕 New</a>
                    <a href="?<?= $subObj ? 'sub=' . urlencode($subObj['name']) . '&' : '' ?>sort=top" class="sort-btn <?= $sort === 'top' ? 'active' : '' ?>">⭐ Top</a>
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
                            <a href="?sub=<?= urlencode($post['sub'] ?? 'main') ?>" class="post-meta-sub">/s/<?= htmlspecialchars($post['sub'] ?? 'main') ?></a>
                            <span class="post-meta-separator">•</span>
                            <span>Posted by <strong><?= htmlspecialchars($post['author'] ?? 'anon') ?></strong></span>
                            <span class="post-meta-separator">•</span>
                            <span><?= format_time($post['created_at']) ?> ago</span>
                        </div>
                        <h2 class="post-title"><a href="?action=view&id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a></h2>
                        <p class="post-preview"><?= htmlspecialchars(substr($post['content'], 0, 200)) ?><?= strlen($post['content']) > 200 ? '...' : '' ?></p>
                        <div class="post-actions">
                            <a href="?action=view&id=<?= $post['id'] ?>">💬 <?= get_comment_count($post['id']) ?> comments</a>
                            <a href="?action=view&id=<?= $post['id'] ?>">Read more →</a>
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
                    <h3>Create a Post</h3>
                    <p class="sidebar-description">
                        Share your thoughts with the community. Completely anonymous. Zero tracking.
                    </p>
                    <a href="?action=submit" class="btn-primary">+ Create Post</a>
                </div>
                
                <div class="sidebar-box">
                    <h3>Platform Stats</h3>
                    <div class="sidebar-stats">
                        <div class="sidebar-stat">
                            <div class="sidebar-stat-number"><?= $total_posts ?></div>
                            <div class="sidebar-stat-label">Posts</div>
                        </div>
                        <div class="sidebar-stat">
                            <div class="sidebar-stat-number"><?= $total_subs ?></div>
                            <div class="sidebar-stat-label">Communities</div>
                        </div>
                    </div>
                </div>
                
                <div class="sidebar-box">
                    <h3>Our Principles</h3>
                    <ul class="sidebar-list">
                        <li>Zero JavaScript execution</li>
                        <li>No tracking or cookies</li>
                        <li>Anonymous by default</li>
                        <li>Server-side only</li>
                        <li>Tor Browser compatible</li>
                        <li>RAM-only content storage</li>
                    </ul>
                </div>
                
                <div class="sidebar-box">
                    <h3>Why kloaq?</h3>
                    <p class="sidebar-description">
                        The modern web is a surveillance engine. kloaq is the antidote. We remove the client-side execution layer to eliminate fingerprinting and de-anonymization.
                    </p>
                    <p class="sidebar-description" style="margin-bottom: 0;">
                        <strong>Your privacy is not a feature—it's our foundation.</strong>
                    </p>
                </div>
                
                <div class="sidebar-box">
                    <h3>Browse Communities</h3>
                    <p class="sidebar-description">
                        Explore subKloaqs (communities) or create your own.
                    </p>
                    <a href="?action=subs" class="btn-secondary" style="width: 100%; text-align: center; text-decoration: none; display: block;">View All Communities</a>
                </div>
            </aside>
        </div>
    </div>
</body>
</html>