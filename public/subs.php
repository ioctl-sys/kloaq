<?php
require_once 'lib.php';
require 'header.php';

$subs = get_subs();
$total_subs = count($subs);
?>
        <div class="layout">
            <main>
                <div class="sidebar-box" style="margin-bottom: var(--space-xl);">
                    <h1 style="margin-bottom: var(--space-md); font-size: 2rem;">🏘️ Communities (subKloaqs)</h1>
                    <p class="sidebar-description">
                        subKloaqs are like communities or categories where posts are organized. Browse existing communities or create your own.
                    </p>
                    <?php if (is_logged_in()): ?>
                        <a href="?action=create_sub" class="btn-primary" style="margin-top: var(--space-md);">+ Create New Community</a>
                    <?php else: ?>
                        <div class="info-box" style="margin-top: var(--space-md); margin-bottom: 0;">
                            <strong>Want to create a community?</strong>
                            <p style="margin-bottom: 0;">
                                <a href="?action=signin" style="color: var(--primary); font-weight: 600;">Sign in</a> or 
                                <a href="?action=signup" style="color: var(--primary); font-weight: 600;">create an account</a> to start your own subKloaq.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (empty($subs)): ?>
                    <div class="sidebar-box">
                        <p class="text-center" style="color: var(--text-secondary); padding: var(--space-xl);">
                            No communities yet. Be the first to create one!
                        </p>
                    </div>
                <?php else: ?>
                    <?php foreach ($subs as $sub): ?>
                        <div class="post" style="margin-bottom: var(--space-md);">
                            <div class="vote-col" style="background: linear-gradient(135deg, var(--primary-light), var(--bg-sidebar)); padding: var(--space-lg); align-items: flex-start;">
                                <div style="font-size: 2rem;">🏷️</div>
                            </div>
                            <div class="post-content">
                                <h3 style="margin-bottom: var(--space-sm); font-size: 1.25rem;">
                                    <a href="?sub=<?= urlencode($sub['name']) ?>" style="color: var(--text-primary); text-decoration: none; font-weight: 600;">/s/<?= htmlspecialchars($sub['name']) ?></a>
                                </h3>
                                <?php if (!empty($sub['description'])): ?>
                                    <p class="post-preview"><?= htmlspecialchars($sub['description']) ?></p>
                                <?php else: ?>
                                    <p class="post-preview" style="font-style: italic;">No description provided.</p>
                                <?php endif; ?>
                                <div class="post-meta" style="margin-top: var(--space-sm);">
                                    <span>Created by <strong><?= htmlspecialchars($sub['creator'] ?? 'anon') ?></strong></span>
                                    <span class="post-meta-separator">•</span>
                                    <span><?= htmlspecialchars(format_time($sub['created_at'])) ?> ago</span>
                                </div>
                                <div class="post-actions" style="margin-top: var(--space-md);">
                                    <a href="?sub=<?= urlencode($sub['name']) ?>">View Posts</a>
                                    <a href="?action=submit&sub=<?= urlencode($sub['name']) ?>">Create Post</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </main>
            
            <aside class="sidebar">
                <div class="sidebar-box">
                    <h3>Community Stats</h3>
                    <div class="sidebar-stat" style="text-align: center;">
                        <div class="sidebar-stat-number"><?= $total_subs ?></div>
                        <div class="sidebar-stat-label">Total Communities</div>
                    </div>
                </div>
                
                <div class="sidebar-box">
                    <h3>What are subKloaqs?</h3>
                    <p class="sidebar-description">
                        subKloaqs are topic-based communities where users can post and discuss specific subjects. Think of them as categories or forums.
                    </p>
                </div>
                
                <div class="sidebar-box">
                    <h3>Popular Topics</h3>
                    <ul class="sidebar-list">
                        <li>Privacy & Security</li>
                        <li>Technology</li>
                        <li>News & Discussion</li>
                        <li>Open Source</li>
                    </ul>
                    <p class="sidebar-description" style="margin-top: var(--space-md); margin-bottom: 0;">
                        Don't see your topic? Create a new community!
                    </p>
                </div>
                
                <div class="sidebar-box">
                    <h3>Guidelines</h3>
                    <ul class="sidebar-list">
                        <li>Use clear, descriptive names</li>
                        <li>Write helpful descriptions</li>
                        <li>Stay on topic</li>
                        <li>Be respectful</li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</body>
</html>
