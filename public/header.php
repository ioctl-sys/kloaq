<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>kloaq - Anonymous Link Aggregator</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #dae0e6; color: #1c1c1c; line-height: 1.5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        
        header { background: #fff; border-bottom: 1px solid #edeff1; padding: 10px 0; position: sticky; top: 0; z-index: 100; }
        header .container { display: flex; align-items: center; gap: 20px; }
        .logo { font-weight: bold; font-size: 1.3rem; color: #ff4500; text-decoration: none; }
        .nav { display: flex; gap: 15px; margin-left: auto; }
        .nav a { color: #878a8c; text-decoration: none; font-weight: 500; }
        .nav a:hover { color: #1c1c1c; }
        
        .layout { display: grid; grid-template-columns: 1fr 310px; gap: 24px; padding: 24px 0; }
        
        .sort-bar { display: flex; gap: 8px; margin-bottom: 16px; }
        .sort-btn { padding: 8px 16px; border: 1px solid #ccc; background: #fff; border-radius: 20px; cursor: pointer; font-weight: 500; color: #878a8c; text-decoration: none; }
        .sort-btn.active { background: #0079d3; color: #fff; border-color: #0079d3; }
        
        .post { background: #fff; border-radius: 4px; border: 1px solid #ccc; margin-bottom: 10px; display: flex; }
        .vote-col { width: 40px; background: #f8f9fa; padding: 8px 4px; text-align: center; border-radius: 4px 0 0 4px; }
        .vote-btn { background: none; border: none; cursor: pointer; font-size: 1.2rem; color: #878a8c; padding: 2px; }
        .vote-btn:hover { color: #ff4500; }
        .vote-count { font-weight: bold; font-size: 0.9rem; }
        .post-content { padding: 8px; flex: 1; }
        .post-meta { font-size: 0.75rem; color: #787c7e; margin-bottom: 4px; }
        .post-title { font-size: 1.1rem; font-weight: 500; color: #1c1c1c; margin-bottom: 4px; }
        .post-title a { color: inherit; text-decoration: none; }
        .post-title a:hover { color: #0079d3; }
        .post-preview { color: #4a4a4a; font-size: 0.9rem; margin-bottom: 8px; }
        .post-actions { display: flex; gap: 12px; font-size: 0.8rem; color: #878a8c; }
        .post-actions a { color: inherit; text-decoration: none; }
        .post-actions a:hover { background: #f8f9fa; }
        
        .sidebar { position: sticky; top: 70px; }
        .sidebar-box { background: #fff; border-radius: 4px; border: 1px solid #ccc; padding: 16px; margin-bottom: 16px; }
        .sidebar-box h3 { font-size: 0.9rem; color: #1c1c1c; margin-bottom: 12px; }
        .btn-primary { width: 100%; padding: 10px; background: #0079d3; color: #fff; border: none; border-radius: 20px; font-weight: bold; cursor: pointer; font-size: 0.9rem; }
        .btn-primary:hover { background: #006cbd; }
        
        .create-form input, .create-form textarea { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; font-family: inherit; }
        .create-form textarea { min-height: 100px; resize: vertical; }
        .create-form button { padding: 10px 20px; background: #0079d3; color: #fff; border: none; border-radius: 20px; cursor: pointer; font-weight: bold; }
        
        .comment-thread { margin-top: 16px; }
        .comment { padding: 8px 0; border-left: 2px solid #edeff1; padding-left: 12px; margin-left: 8px; }
        .comment-meta { font-size: 0.75rem; color: #787c7e; margin-bottom: 4px; }
        .comment-content { font-size: 0.9rem; color: #1c1c1c; }
        .comment-actions { display: flex; gap: 12px; font-size: 0.8rem; color: #878a8c; margin-top: 4px; }
        .comment-actions a { color: inherit; text-decoration: none; cursor: pointer; }
        
        .comment-form { margin-top: 10px; }
        .comment-form textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; min-height: 80px; resize: vertical; }
        .comment-form button { margin-top: 8px; padding: 6px 16px; background: #0079d3; color: #fff; border: none; border-radius: 20px; cursor: pointer; font-size: 0.85rem; }
        
        .post-view .post-title { font-size: 1.5rem; margin-bottom: 10px; }
        .post-view .post-content-full { padding: 16px; background: #fff; border-radius: 4px; border: 1px solid #ccc; }
        
        .back-link { color: #0079d3; text-decoration: none; margin-bottom: 16px; display: inline-block; }
        
        .auth-form { max-width: 400px; margin: 40px auto; }
        .auth-form input { width: 100%; padding: 12px; margin-bottom: 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 1rem; }
        .auth-form button { width: 100%; padding: 12px; background: #0079d3; color: #fff; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 1rem; }
        .auth-form button:hover { background: #006cbd; }
        .auth-form .switch-link { text-align: center; margin-top: 16px; color: #787c7e; }
        .auth-form .switch-link a { color: #0079d3; }
        .error-box { background: #fee; border: 1px solid #fcc; padding: 12px; border-radius: 4px; margin-bottom: 16px; color: #c00; }
        .success-box { background: #efe; border: 1px solid #cfc; padding: 12px; border-radius: 4px; margin-bottom: 16px; color: #060; }
        
        .settings-box { background: #fff; border-radius: 4px; border: 1px solid #ccc; padding: 20px; margin-bottom: 16px; }
        .settings-box h2 { font-size: 1.2rem; margin-bottom: 16px; color: #1c1c1c; }
        .settings-box h3 { font-size: 1rem; margin-bottom: 8px; color: #1c1c1c; }
        .settings-box p { color: #787c7e; font-size: 0.9rem; margin-bottom: 12px; }
        .settings-box .stat { font-size: 1.5rem; font-weight: bold; color: #0079d3; }
        .btn-danger { background: #ff4500; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-danger:hover { background: #e03d00; }
        .btn-secondary { background: #edeff1; color: #1c1c1c; border: 1px solid #ccc; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-secondary:hover { background: #dae0e6; }
        .user-link { color: #0079d3; font-weight: 500; }
        
        @media (max-width: 960px) {
            .layout { grid-template-columns: 1fr; }
            .sidebar { display: none; }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <a href="?" class="logo">◉ kloaq</a>
            <nav class="nav">
                <a href="?">hot</a>
                <a href="?sort=new">new</a>
                <a href="?sort=top">top</a>
                <a href="?action=subs">subs</a>
                <a href="?action=submit">submit</a>
                <?php if (is_admin()): ?>
                    <a href="?action=admin">admin</a>
                <?php endif; ?>
                <?php if (is_logged_in()): ?>
                    <a href="?action=settings" class="user-link"><?= htmlspecialchars(current_user()) ?></a>
                    <a href="?action=logout">logout</a>
                <?php else: ?>
                    <a href="?action=signin">sign in</a>
                    <a href="?action=signup">sign up</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <div class="container">
