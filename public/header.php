<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="kloaq - Anonymous publishing platform. No JavaScript. No tracking. No cookies. Free speech by design.">
    <title>kloaq - Anonymous Publishing Platform</title>
    <style>
        /* === RESET & BASE === */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            /* Brand Colors */
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --primary-light: #eef2ff;
            --accent: #ec4899;
            --accent-hover: #db2777;
            
            /* Neutral Colors */
            --bg-page: #f8fafc;
            --bg-card: #ffffff;
            --bg-sidebar: #f1f5f9;
            --border: #e2e8f0;
            --border-hover: #cbd5e1;
            
            /* Text Colors */
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --text-link: #6366f1;
            
            /* Status Colors */
            --success: #10b981;
            --success-bg: #d1fae5;
            --success-border: #6ee7b7;
            --error: #ef4444;
            --error-bg: #fee2e2;
            --error-border: #fca5a5;
            --warning: #f59e0b;
            --info: #3b82f6;
            
            /* Shadows */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            
            /* Spacing */
            --space-xs: 0.25rem;
            --space-sm: 0.5rem;
            --space-md: 1rem;
            --space-lg: 1.5rem;
            --space-xl: 2rem;
            --space-2xl: 3rem;
            
            /* Border Radius */
            --radius-sm: 0.25rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            --radius-full: 9999px;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: var(--bg-page);
            color: var(--text-primary);
            line-height: 1.6;
            font-size: 16px;
        }
        
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 var(--space-lg);
        }
        
        /* === HEADER === */
        header {
            background: var(--bg-card);
            border-bottom: 1px solid var(--border);
            padding: var(--space-md) 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow-sm);
        }
        
        header .container {
            display: flex;
            align-items: center;
            gap: var(--space-xl);
            flex-wrap: wrap;
        }
        
        .logo {
            font-weight: 800;
            font-size: 1.5rem;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            letter-spacing: -0.02em;
        }
        
        .logo-icon {
            font-size: 1.8rem;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .nav {
            display: flex;
            gap: var(--space-sm);
            margin-left: auto;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .nav a {
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 0.75rem;
            border-radius: var(--radius-md);
            transition: all 0.2s ease;
            font-size: 0.95rem;
        }
        
        .nav a:hover {
            color: var(--text-primary);
            background: var(--bg-sidebar);
        }
        
        .nav .btn-nav-primary {
            background: var(--primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-full);
            font-weight: 600;
        }
        
        .nav .btn-nav-primary:hover {
            background: var(--primary-hover);
        }
        
        .user-link {
            color: var(--primary);
            font-weight: 600;
        }
        
        /* === LAYOUT === */
        .layout {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: var(--space-xl);
            padding: var(--space-xl) 0;
        }
        
        /* === HERO SECTION === */
        .hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            color: white;
            padding: var(--space-2xl) var(--space-lg);
            border-radius: var(--radius-xl);
            margin-bottom: var(--space-xl);
            box-shadow: var(--shadow-lg);
        }
        
        .hero h1 {
            font-size: 2.25rem;
            font-weight: 800;
            margin-bottom: var(--space-md);
            line-height: 1.2;
        }
        
        .hero p {
            font-size: 1.125rem;
            opacity: 0.95;
            margin-bottom: var(--space-lg);
            max-width: 600px;
        }
        
        .hero-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: var(--space-md);
            margin-top: var(--space-lg);
        }
        
        .hero-feature {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            font-weight: 500;
        }
        
        .hero-feature-icon {
            font-size: 1.5rem;
        }
        
        /* === SORT BAR === */
        .sort-bar {
            display: flex;
            gap: var(--space-sm);
            margin-bottom: var(--space-lg);
            flex-wrap: wrap;
        }
        
        .sort-btn {
            padding: 0.5rem 1.25rem;
            border: 2px solid var(--border);
            background: var(--bg-card);
            border-radius: var(--radius-full);
            cursor: pointer;
            font-weight: 600;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.2s ease;
            font-size: 0.9rem;
        }
        
        .sort-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-1px);
        }
        
        .sort-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            box-shadow: var(--shadow-md);
        }
        
        /* === POST CARD === */
        .post {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            margin-bottom: var(--space-md);
            display: flex;
            transition: all 0.2s ease;
            overflow: hidden;
        }
        
        .post:hover {
            box-shadow: var(--shadow-md);
            border-color: var(--border-hover);
        }
        
        .vote-col {
            width: 50px;
            background: var(--bg-sidebar);
            padding: var(--space-md) var(--space-sm);
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: var(--space-xs);
        }
        
        .vote-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.5rem;
            color: var(--text-muted);
            padding: var(--space-xs);
            transition: all 0.2s ease;
            line-height: 1;
        }
        
        .vote-btn:hover:not(:disabled) {
            color: var(--primary);
            transform: scale(1.2);
        }
        
        .vote-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }
        
        .vote-count {
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--text-primary);
        }
        
        .post-content {
            padding: var(--space-lg);
            flex: 1;
            min-width: 0;
        }
        
        .post-meta {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: var(--space-sm);
            display: flex;
            flex-wrap: wrap;
            gap: var(--space-sm);
            align-items: center;
        }
        
        .post-meta-sub {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        
        .post-meta-sub:hover {
            text-decoration: underline;
        }
        
        .post-meta-separator {
            color: var(--border);
        }
        
        .post-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: var(--space-sm);
            line-height: 1.4;
        }
        
        .post-title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        
        .post-title a:hover {
            color: var(--primary);
        }
        
        .post-preview {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin-bottom: var(--space-md);
            line-height: 1.6;
        }
        
        .post-actions {
            display: flex;
            gap: var(--space-lg);
            font-size: 0.85rem;
            color: var(--text-muted);
            font-weight: 500;
        }
        
        .post-actions a {
            color: inherit;
            text-decoration: none;
            padding: var(--space-xs) var(--space-sm);
            border-radius: var(--radius-sm);
            transition: all 0.2s ease;
        }
        
        .post-actions a:hover {
            background: var(--bg-sidebar);
            color: var(--text-primary);
        }
        
        /* === SIDEBAR === */
        .sidebar {
            position: sticky;
            top: 90px;
            height: fit-content;
        }
        
        .sidebar-box {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            padding: var(--space-lg);
            margin-bottom: var(--space-lg);
            box-shadow: var(--shadow-sm);
        }
        
        .sidebar-box h3 {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: var(--space-md);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .sidebar-description {
            font-size: 0.9rem;
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: var(--space-md);
        }
        
        .sidebar-list {
            list-style: none;
            font-size: 0.9rem;
        }
        
        .sidebar-list li {
            padding: var(--space-sm) 0;
            color: var(--text-secondary);
            display: flex;
            align-items: start;
            gap: var(--space-sm);
        }
        
        .sidebar-list li::before {
            content: "✓";
            color: var(--success);
            font-weight: bold;
            flex-shrink: 0;
        }
        
        .sidebar-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-md);
        }
        
        .sidebar-stat {
            text-align: center;
            padding: var(--space-md);
            background: var(--bg-sidebar);
            border-radius: var(--radius-md);
        }
        
        .sidebar-stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .sidebar-stat-label {
            font-size: 0.8rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: var(--space-xs);
        }
        
        /* === BUTTONS === */
        .btn-primary {
            width: 100%;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-hover));
            color: white;
            border: none;
            border-radius: var(--radius-full);
            font-weight: 600;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .btn-secondary {
            background: var(--bg-sidebar);
            color: var(--text-primary);
            border: 1px solid var(--border);
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-md);
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
            font-size: 0.95rem;
        }
        
        .btn-secondary:hover {
            background: var(--border);
            border-color: var(--border-hover);
        }
        
        .btn-danger {
            background: var(--error);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-md);
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
            font-size: 0.95rem;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        /* === FORMS === */
        .create-form input,
        .create-form textarea,
        .auth-form input {
            width: 100%;
            padding: 0.75rem 1rem;
            margin-bottom: var(--space-md);
            border: 2px solid var(--border);
            border-radius: var(--radius-md);
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.2s ease;
            background: var(--bg-card);
        }
        
        .create-form input:focus,
        .create-form textarea:focus,
        .auth-form input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }
        
        .create-form textarea {
            min-height: 120px;
            resize: vertical;
            line-height: 1.6;
        }
        
        .create-form button {
            padding: 0.75rem 2rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius-full);
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.2s ease;
        }
        
        .create-form button:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: var(--space-sm);
            color: var(--text-primary);
            font-size: 0.9rem;
        }
        
        .form-hint {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: calc(-1 * var(--space-sm));
            margin-bottom: var(--space-md);
        }
        
        /* === COMMENTS === */
        .comment-thread {
            margin-top: var(--space-lg);
        }
        
        .comment {
            padding: var(--space-md) 0;
            border-left: 3px solid var(--border);
            padding-left: var(--space-lg);
            margin-left: var(--space-md);
            margin-top: var(--space-md);
        }
        
        .comment:hover {
            border-left-color: var(--primary);
        }
        
        .comment-meta {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: var(--space-sm);
            display: flex;
            gap: var(--space-md);
            align-items: center;
        }
        
        .comment-meta strong {
            color: var(--text-primary);
            font-weight: 600;
        }
        
        .comment-content {
            font-size: 0.95rem;
            color: var(--text-primary);
            line-height: 1.6;
            margin-bottom: var(--space-sm);
        }
        
        .comment-actions {
            display: flex;
            gap: var(--space-md);
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: var(--space-sm);
            font-weight: 500;
        }
        
        .comment-actions a {
            color: inherit;
            text-decoration: none;
            cursor: pointer;
            padding: var(--space-xs) var(--space-sm);
            border-radius: var(--radius-sm);
            transition: all 0.2s ease;
        }
        
        .comment-actions a:hover {
            background: var(--bg-sidebar);
            color: var(--text-primary);
        }
        
        .comment-form {
            margin-top: var(--space-lg);
            background: var(--bg-sidebar);
            padding: var(--space-lg);
            border-radius: var(--radius-lg);
        }
        
        .comment-form textarea {
            width: 100%;
            padding: var(--space-md);
            border: 2px solid var(--border);
            border-radius: var(--radius-md);
            min-height: 100px;
            resize: vertical;
            font-family: inherit;
            font-size: 0.95rem;
            background: var(--bg-card);
        }
        
        .comment-form textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }
        
        .comment-form button {
            margin-top: var(--space-md);
            padding: 0.5rem 1.5rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius-full);
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        
        .comment-form button:hover {
            background: var(--primary-hover);
        }
        
        /* === POST VIEW === */
        .post-view .post-title {
            font-size: 2rem;
            margin-bottom: var(--space-md);
            line-height: 1.3;
        }
        
        .post-view .post-content-full {
            padding: var(--space-xl);
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            line-height: 1.8;
            font-size: 1.05rem;
        }
        
        .back-link {
            color: var(--primary);
            text-decoration: none;
            margin-bottom: var(--space-lg);
            display: inline-flex;
            align-items: center;
            gap: var(--space-sm);
            font-weight: 500;
            padding: var(--space-sm) var(--space-md);
            border-radius: var(--radius-md);
            transition: all 0.2s ease;
        }
        
        .back-link:hover {
            background: var(--primary-light);
        }
        
        /* === AUTH FORMS === */
        .auth-form {
            max-width: 450px;
            margin: var(--space-2xl) auto;
        }
        
        .auth-form .sidebar-box {
            padding: var(--space-2xl);
        }
        
        .auth-form h2 {
            text-align: center;
            margin-bottom: var(--space-xl);
            font-size: 1.75rem;
            font-weight: 700;
        }
        
        .auth-form button {
            width: 100%;
            padding: 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 600;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.2s ease;
        }
        
        .auth-form button:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        .auth-form .switch-link {
            text-align: center;
            margin-top: var(--space-lg);
            color: var(--text-secondary);
            font-size: 0.95rem;
        }
        
        .auth-form .switch-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        
        .auth-form .switch-link a:hover {
            text-decoration: underline;
        }
        
        /* === ALERTS === */
        .error-box {
            background: var(--error-bg);
            border: 2px solid var(--error-border);
            padding: var(--space-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-lg);
            color: var(--error);
            font-weight: 500;
        }
        
        .success-box {
            background: var(--success-bg);
            border: 2px solid var(--success-border);
            padding: var(--space-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-lg);
            color: #047857;
            font-weight: 500;
        }
        
        .info-box {
            background: var(--primary-light);
            border: 2px solid var(--primary);
            padding: var(--space-lg);
            border-radius: var(--radius-lg);
            margin-top: var(--space-lg);
            font-size: 0.9rem;
            color: var(--text-secondary);
            line-height: 1.6;
        }
        
        .info-box strong {
            color: var(--text-primary);
            display: block;
            margin-bottom: var(--space-sm);
        }
        
        /* === SETTINGS === */
        .settings-box {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            padding: var(--space-xl);
            margin-bottom: var(--space-lg);
            box-shadow: var(--shadow-sm);
        }
        
        .settings-box h2 {
            font-size: 1.5rem;
            margin-bottom: var(--space-lg);
            color: var(--text-primary);
            font-weight: 700;
        }
        
        .settings-box h3 {
            font-size: 1.125rem;
            margin-bottom: var(--space-md);
            color: var(--text-primary);
            font-weight: 600;
        }
        
        .settings-box p {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin-bottom: var(--space-md);
            line-height: 1.6;
        }
        
        .settings-box .stat {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .stats-grid {
            display: flex;
            gap: var(--space-2xl);
            margin: var(--space-lg) 0;
        }
        
        .stat-item {
            text-align: center;
        }
        
        /* === RESPONSIVE === */
        @media (max-width: 960px) {
            .layout {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                position: static;
            }
            
            .hero h1 {
                font-size: 1.75rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .post-title {
                font-size: 1.1rem;
            }
            
            .post-view .post-title {
                font-size: 1.5rem;
            }
        }
        
        @media (max-width: 640px) {
            .container {
                padding: 0 var(--space-md);
            }
            
            header .container {
                gap: var(--space-md);
            }
            
            .nav {
                gap: var(--space-xs);
            }
            
            .nav a {
                padding: 0.4rem 0.6rem;
                font-size: 0.85rem;
            }
            
            .hero {
                padding: var(--space-lg);
            }
            
            .hero h1 {
                font-size: 1.5rem;
            }
            
            .post-content {
                padding: var(--space-md);
            }
            
            .sidebar-box {
                padding: var(--space-md);
            }
            
            .auth-form .sidebar-box {
                padding: var(--space-lg);
            }
        }
        
        /* === UTILITY CLASSES === */
        .text-center { text-align: center; }
        .mb-0 { margin-bottom: 0; }
        .mt-lg { margin-top: var(--space-lg); }
        .mb-lg { margin-bottom: var(--space-lg); }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <a href="?" class="logo">
                <span class="logo-icon">◉</span>
                <span>kloaq</span>
            </a>
            <nav class="nav">
                <a href="?">hot</a>
                <a href="?sort=new">new</a>
                <a href="?sort=top">top</a>
                <a href="?action=subs">communities</a>
                <?php if (is_admin()): ?>
                    <a href="?action=admin">admin</a>
                <?php endif; ?>
                <?php if (is_logged_in()): ?>
                    <a href="?action=settings" class="user-link"><?= htmlspecialchars(current_user()) ?></a>
                    <a href="?action=logout">logout</a>
                <?php else: ?>
                    <a href="?action=signin">sign in</a>
                    <a href="?action=signup" class="btn-nav-primary">sign up</a>
                <?php endif; ?>
                <a href="?action=submit" class="btn-nav-primary">+ post</a>
            </nav>
        </div>
    </header>
    <div class="container">
