<?php
require_once 'lib.php';
require 'header.php';

$subs = get_subs();
?>
        <div class="layout">
            <main style="max-width: 800px;">
                <h1 style="margin-bottom: 20px;">subKloaqs</h1>

                <div class="sidebar-box" style="margin-bottom: 16px;">
                    <p style="font-size: 0.9rem; color: #4a4a4a;">
                        subKloaqs are like communities. Posts are filed under a subKloaq.
                    </p>
                    <?php if (is_logged_in()): ?>
                        <p style="margin-top: 10px;"><a href="?action=create_sub" style="color: #0079d3; text-decoration: none; font-weight: 600;">Create a subKloaq</a></p>
                    <?php else: ?>
                        <p style="margin-top: 10px; color: #787c7e;">Sign in to create a subKloaq.</p>
                    <?php endif; ?>
                </div>

                <?php foreach ($subs as $sub): ?>
                    <div class="sidebar-box" style="margin-bottom: 10px;">
                        <h3 style="margin-bottom: 6px;"><a href="?sub=<?= urlencode($sub['name']) ?>" style="color: #1c1c1c; text-decoration: none;">/s/<?= htmlspecialchars($sub['name']) ?></a></h3>
                        <?php if (!empty($sub['description'])): ?>
                            <p style="font-size: 0.9rem; color: #4a4a4a;"><?= htmlspecialchars($sub['description']) ?></p>
                        <?php endif; ?>
                        <div style="margin-top: 8px; font-size: 0.8rem; color: #787c7e;">
                            Created <?= htmlspecialchars(format_time($sub['created_at'])) ?> ago by <?= htmlspecialchars($sub['creator'] ?? 'anon') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </main>
        </div>
    </div>
</body>
</html>
