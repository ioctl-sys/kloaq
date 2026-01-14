<?php
function kloaq_load_dotenv($path) {
    if (!is_file($path) || !is_readable($path)) return;

    $lines = file($path, FILE_IGNORE_NEW_LINES);
    if ($lines === false) return;

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;

        // allow: export KEY=VALUE
        if (str_starts_with($line, 'export ')) {
            $line = trim(substr($line, 7));
        }

        $eq = strpos($line, '=');
        if ($eq === false) continue;

        $key = trim(substr($line, 0, $eq));
        $value = trim(substr($line, $eq + 1));
        if ($key === '') continue;

        // ignore invalid keys
        if (!preg_match('/^[A-Z0-9_]+$/', $key)) continue;

        // strip quotes
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }

        // don't override real environment
        if (getenv($key) !== false) continue;
        if (isset($_ENV[$key]) || isset($_SERVER[$key])) continue;

        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

// Load repo-root .env (one level above /public)
kloaq_load_dotenv(dirname(__DIR__) . '/.env');

// Error logging (helps diagnose blank 500s)
if (!is_dir(__DIR__ . '/data')) {
    @mkdir(__DIR__ . '/data', 0755, true);
}
if (is_dir(__DIR__ . '/data') && is_writable(__DIR__ . '/data')) {
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/data/php_errors.log');
}

$kloaqDebug = (getenv('KLOAQ_DEBUG') === '1');
if ($kloaqDebug) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

set_exception_handler(function ($e) use ($kloaqDebug) {
    error_log("[kloaq] Uncaught exception: " . $e);
    http_response_code(500);
    if ($kloaqDebug && !headers_sent()) {
        header('Content-Type: text/plain; charset=UTF-8');
    }
    if ($kloaqDebug) {
        echo "KLOAQ ERROR\n\n";
        echo (string)$e;
    }
});

session_start();

define('DATA_DIR', __DIR__ . '/data');
define('DB_FILE', DATA_DIR . '/accounts.db');
define('DB_ENCRYPTION_KEY', getenv('KLOAQ_DB_KEY') ?: 'change-this-to-secure-key-in-production');

if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0755, true);

function kloaq_ensure_db_writable() {
    if (!is_dir(DATA_DIR)) {
        @mkdir(DATA_DIR, 0755, true);
    }

    if (!is_writable(DATA_DIR)) {
        // Best-effort for dev; production should set proper ownership/permissions.
        @chmod(DATA_DIR, 0777);
    }

    if (file_exists(DB_FILE)) {
        if (!is_writable(DB_FILE)) {
            // If the DB was created by a different user (e.g., CLI), Apache may not be able to write.
            @chmod(DB_FILE, 0666);
        }
        if (!is_writable(DB_FILE)) {
            throw new RuntimeException('Accounts DB is not writable by the web server. Fix permissions for ' . DB_FILE . ' (e.g. chown/chmod public/data).');
        }
        return;
    }

    // Create the file with broad write perms for dev setups.
    if (is_writable(DATA_DIR)) {
        @touch(DB_FILE);
        @chmod(DB_FILE, 0666);
    }
}

// In-memory storage for ephemeral content (posts, comments, votes)
class MemoryStore {
    private const KEY_POSTS = 'kloaq_posts_v1';
    private const KEY_COMMENTS = 'kloaq_comments_v1';
    private const KEY_VOTES = 'kloaq_votes_v1';
    private const KEY_SUBS = 'kloaq_subs_v1';

    private static function apcuAvailable() {
        if (!function_exists('apcu_fetch') || !function_exists('apcu_store')) return false;
        // When running under the built-in server (CLI SAPI), APCu is often disabled unless apc.enable_cli=1
        $enabled = ini_get('apc.enabled');
        if ($enabled !== false && $enabled !== '' && $enabled != '1') return false;
        if (PHP_SAPI === 'cli' && ini_get('apc.enable_cli') != '1') return false;
        return true;
    }

    private static function get($key, $default) {
        if (self::apcuAvailable()) {
            $success = false;
            $value = apcu_fetch($key, $success);
            return $success ? $value : $default;
        }

        // Session fallback: persists across requests for the current user only.
        // This keeps dev working even without APCu installed.
        if (!isset($_SESSION['__memstore'])) $_SESSION['__memstore'] = [];
        return $_SESSION['__memstore'][$key] ?? $default;
    }

    private static function set($key, $value) {
        if (self::apcuAvailable()) {
            apcu_store($key, $value);
            return;
        }
        if (!isset($_SESSION['__memstore'])) $_SESSION['__memstore'] = [];
        $_SESSION['__memstore'][$key] = $value;
    }

    public static function getPosts() { return self::get(self::KEY_POSTS, []); }
    public static function setPosts($posts) { self::set(self::KEY_POSTS, $posts); }

    public static function getComments() { return self::get(self::KEY_COMMENTS, []); }
    public static function setComments($comments) { self::set(self::KEY_COMMENTS, $comments); }

    public static function getVotes() { return self::get(self::KEY_VOTES, []); }
    public static function setVotes($votes) { self::set(self::KEY_VOTES, $votes); }

    public static function getSubs() {
        $subs = self::get(self::KEY_SUBS, []);
        if (empty($subs)) {
            $subs = [
                'main' => [
                    'name' => 'main',
                    'title' => 'main',
                    'description' => 'Default subKloaq',
                    'created_at' => date('Y-m-d H:i:s'),
                    'creator' => 'system'
                ]
            ];
            self::set(self::KEY_SUBS, $subs);
        }
        return $subs;
    }

    public static function setSubs($subs) { self::set(self::KEY_SUBS, $subs); }
    
    // Delete all content by a specific user
    public static function deleteUserContent($username) {
        // Get post IDs by this user (to delete associated comments)
        $posts = self::getPosts();
        $userPostIds = array_column(array_filter($posts, fn($p) => ($p['author'] ?? 'anon') === $username), 'id');
        
        // Remove user's posts
        $newPosts = array_values(array_filter($posts, fn($p) => ($p['author'] ?? 'anon') !== $username));
        self::setPosts($newPosts);
        
        // Remove user's comments
        $comments = self::getComments();
        $newComments = array_values(array_filter($comments, fn($c) => ($c['author'] ?? 'anon') !== $username));
        self::setComments($newComments);
        
        // Remove user's votes
        $votes = self::getVotes();
        foreach ($votes as $key => $vote) {
            if (($vote['user'] ?? '') === $username) {
                unset($votes[$key]);
            }
        }
        self::setVotes($votes);
        
        return ['posts' => count($userPostIds), 'comments' => count($comments) - count($newComments)];
    }
    
    // Get stats for a user
    public static function getUserStats($username) {
        $posts = array_filter(self::getPosts(), fn($p) => ($p['author'] ?? 'anon') === $username);
        $comments = array_filter(self::getComments(), fn($c) => ($c['author'] ?? 'anon') === $username);
        return [
            'posts' => count($posts),
            'comments' => count($comments)
        ];
    }

    public static function deletePostById($postId) {
        $postId = (int)$postId;
        $posts = self::getPosts();
        $before = count($posts);
        $posts = array_values(array_filter($posts, fn($p) => (int)$p['id'] !== $postId));
        self::setPosts($posts);

        // Remove comments for this post (all depths)
        $comments = self::getComments();
        $cBefore = count($comments);
        $comments = array_values(array_filter($comments, fn($c) => (int)$c['post_id'] !== $postId));
        self::setComments($comments);

        return ['posts' => $before - count($posts), 'comments' => $cBefore - count($comments)];
    }

    public static function deleteCommentById($commentId) {
        $commentId = (int)$commentId;
        $comments = self::getComments();
        $byId = [];
        foreach ($comments as $c) $byId[(int)$c['id']] = $c;
        if (!isset($byId[$commentId])) return ['comments' => 0];

        // Collect descendants
        $toDelete = [$commentId => true];
        $changed = true;
        while ($changed) {
            $changed = false;
            foreach ($comments as $c) {
                $pid = $c['parent_id'] === null ? null : (int)$c['parent_id'];
                if ($pid !== null && isset($toDelete[$pid]) && !isset($toDelete[(int)$c['id']])) {
                    $toDelete[(int)$c['id']] = true;
                    $changed = true;
                }
            }
        }

        $before = count($comments);
        $comments = array_values(array_filter($comments, fn($c) => !isset($toDelete[(int)$c['id']])));
        self::setComments($comments);
        return ['comments' => $before - count($comments)];
    }

    public static function deleteSub($name) {
        $name = strtolower(trim((string)$name));
        if ($name === 'main') return ['ok' => false, 'error' => 'Cannot delete main.'];
        $subs = self::getSubs();
        if (!isset($subs[$name])) return ['ok' => false, 'error' => 'Sub not found.'];
        unset($subs[$name]);
        self::setSubs($subs);

        $posts = self::getPosts();
        $postIds = [];
        foreach ($posts as $p) if (($p['sub'] ?? 'main') === $name) $postIds[(int)$p['id']] = true;
        $beforePosts = count($posts);
        $posts = array_values(array_filter($posts, fn($p) => ($p['sub'] ?? 'main') !== $name));
        self::setPosts($posts);

        $comments = self::getComments();
        $beforeComments = count($comments);
        $comments = array_values(array_filter($comments, fn($c) => !isset($postIds[(int)$c['post_id']])));
        self::setComments($comments);

        return ['ok' => true, 'posts' => $beforePosts - count($posts), 'comments' => $beforeComments - count($comments)];
    }

    public static function purgeAllContent() {
        self::setPosts([]);
        self::setComments([]);
        self::setVotes([]);
        self::setSubs([]); // getSubs() will re-seed main
        self::getSubs();
    }
}

// Session helpers
function current_user() {
    return $_SESSION['username'] ?? null;
}

function is_logged_in() {
    return isset($_SESSION['username']);
}

function login_user($username) {
    $_SESSION['username'] = $username;
    $_SESSION['login_time'] = time();
}

function logout_user() {
    session_destroy();
    session_start();
}

// Admin helpers
function admin_users() {
    $raw = getenv('KLOAQ_ADMIN_USERS') ?: '';
    $parts = array_filter(array_map('trim', explode(',', $raw)));
    $parts = array_map('strtolower', $parts);
    return array_values(array_unique($parts));
}

function is_admin() {
    $u = current_user();
    if (!$u) return false;
    return in_array(strtolower($u), admin_users(), true);
}

// Encrypted database for user accounts only
function get_db() {
    static $db = null;
    if ($db === null) {
        kloaq_ensure_db_writable();
        $db = new PDO('sqlite:' . DB_FILE);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Initialize accounts table with encryption support
        $db->exec("
            CREATE TABLE IF NOT EXISTS accounts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password_hash TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                banned INTEGER DEFAULT 0,
                restricted INTEGER DEFAULT 0
            )
        ");

        // Lightweight schema migration for older DBs
        $cols = $db->query("PRAGMA table_info(accounts)")->fetchAll(PDO::FETCH_ASSOC);
        $colNames = array_map(fn($c) => $c['name'], $cols);
        if (!in_array('restricted', $colNames, true)) {
            $db->exec("ALTER TABLE accounts ADD COLUMN restricted INTEGER DEFAULT 0");
        }

        // Dev-only bootstrap: create an initial admin account if configured
        $bootstrapUser = getenv('KLOAQ_BOOTSTRAP_ADMIN_USERNAME') ?: '';
        $bootstrapPass = getenv('KLOAQ_BOOTSTRAP_ADMIN_PASSWORD') ?: '';
        if ($bootstrapUser !== '' && $bootstrapPass !== '') {
            $bootstrapUser = trim($bootstrapUser);
            if ($bootstrapUser !== '') {
                $stmt = $db->prepare("SELECT 1 FROM accounts WHERE username = ? LIMIT 1");
                $stmt->execute([$bootstrapUser]);
                $exists = (bool)$stmt->fetchColumn();
                if (!$exists) {
                    $hash = password_hash($bootstrapPass, PASSWORD_ARGON2ID);
                    $ins = $db->prepare("INSERT INTO accounts (username, password_hash, banned, restricted) VALUES (?, ?, 0, 0)");
                    $ins->execute([$bootstrapUser, $hash]);
                }
            }
        }
    }
    return $db;
}

function load_json($file, $default = []) {
    // Legacy function - no longer used for runtime data
    if (!file_exists($file)) return $default;
    $content = file_get_contents($file);
    return $content ? json_decode($content, true) : $default;
}

function save_json($file, $data) {
    // Legacy function - no longer saves to disk
    // All post/comment/vote data stays in RAM
    return true;
}

function get_posts($sort = 'hot') {
    $posts = MemoryStore::getPosts();
    usort($posts, function($a, $b) use ($sort) {
        return match($sort) {
            'new' => strtotime($b['created_at']) - strtotime($a['created_at']),
            'top' => $b['votes'] - $a['votes'],
            default => ($b['votes'] * 1000 + 1000000) / (time() - strtotime($b['created_at']) + 1) - ($a['votes'] * 1000 + 1000000) / (time() - strtotime($a['created_at']) + 1)
        };
    });
    return $posts;
}

function get_subs() {
    $subs = MemoryStore::getSubs();
    ksort($subs);
    return $subs;
}

function get_sub($name) {
    $name = strtolower(trim((string)$name));
    $subs = MemoryStore::getSubs();
    return $subs[$name] ?? null;
}

function create_sub($name, $title, $description) {
    if (!is_logged_in()) return ['ok' => false, 'error' => 'You must be signed in to create a subKloaq.'];

    $name = strtolower(trim($name));
    $title = trim($title);
    $description = trim($description);

    if (strlen($name) < 3 || strlen($name) > 21) return ['ok' => false, 'error' => 'Name must be 3–21 characters.'];
    if (!preg_match('/^[a-z0-9_]+$/', $name)) return ['ok' => false, 'error' => 'Name can only contain lowercase letters, numbers, and underscores.'];
    if (strlen($title) < 3) return ['ok' => false, 'error' => 'Title must be at least 3 characters.'];

    $subs = MemoryStore::getSubs();
    if (isset($subs[$name])) return ['ok' => false, 'error' => 'That subKloaq already exists.'];

    $subs[$name] = [
        'name' => $name,
        'title' => $title,
        'description' => $description,
        'created_at' => date('Y-m-d H:i:s'),
        'creator' => current_user()
    ];
    MemoryStore::setSubs($subs);
    return ['ok' => true, 'name' => $name];
}

function get_post($id) {
    $posts = MemoryStore::getPosts();
    foreach ($posts as $p) if ($p['id'] == $id) return $p;
    return null;
}

function get_comments($post_id, $parent_id = null) {
    $comments = MemoryStore::getComments();
    $result = array_filter($comments, function($c) use ($post_id, $parent_id) {
        return $c['post_id'] == $post_id && $c['parent_id'] == $parent_id;
    });
    usort($result, function($a, $b) {
        return $b['votes'] - $a['votes'] ?: strtotime($a['created_at']) - strtotime($b['created_at']);
    });
    return array_values($result);
}

function vote($type, $id, $value) {
    $votes = MemoryStore::getVotes();
    $user = current_user();
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = $user ? "${type}_${id}_user_${user}" : "${type}_${id}_${ip}";
    
    if (isset($votes[$key])) return false;
    
    $votes[$key] = ['value' => $value, 'time' => time(), 'user' => $user];
    MemoryStore::setVotes($votes);
    
    if ($type === 'post') {
        $posts = MemoryStore::getPosts();
        foreach ($posts as &$p) if ($p['id'] == $id) { $p['votes'] += $value; break; }
        MemoryStore::setPosts($posts);
    } else {
        $comments = MemoryStore::getComments();
        foreach ($comments as &$c) if ($c['id'] == $id) { $c['votes'] += $value; break; }
        MemoryStore::setComments($comments);
    }
    return true;
}

function create_post($title, $content, $sub = 'main') {
    if (is_logged_in() && is_account_restricted(current_user())) return false;
    $posts = MemoryStore::getPosts();
    $id = $posts ? max(array_column($posts, 'id')) + 1 : 1;

    $sub = strtolower(trim((string)$sub));
    if (!$sub) $sub = 'main';
    if (!get_sub($sub)) $sub = 'main';

    $posts[] = [
        'id' => $id,
        'sub' => $sub,
        'title' => $title,
        'content' => $content,
        'author' => current_user() ?? 'anon',
        'votes' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    MemoryStore::setPosts($posts);
    return $id;
}

function create_comment($post_id, $parent_id, $content) {
    if (is_logged_in() && is_account_restricted(current_user())) return false;
    $comments = MemoryStore::getComments();
    $id = $comments ? max(array_column($comments, 'id')) + 1 : 1;
    $comments[] = [
        'id' => $id,
        'post_id' => $post_id,
        'parent_id' => $parent_id,
        'content' => $content,
        'author' => current_user() ?? 'anon',
        'votes' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    MemoryStore::setComments($comments);
    return $id;
}

function format_time($datetime) {
    $ts = strtotime($datetime);
    $diff = time() - $ts;
    if ($diff < 60) return "${diff}s";
    if ($diff < 3600) return intval($diff / 60) . "m";
    if ($diff < 86400) return intval($diff / 3600) . "h";
    if ($diff < 604800) return intval($diff / 86400) . "d";
    return intval($diff / 604800) . "w";
}

function ip_too_recent($type, $id) {
    $votes = MemoryStore::getVotes();
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = "${type}_${id}_${ip}";
    
    if (isset($votes[$key])) {
        return (time() - $votes[$key]['time']) < 60;
    }
    
    if ($type === 'post') {
        $posts = MemoryStore::getPosts();
        foreach ($posts as $p) if ($p['id'] == $id) {
            return (time() - strtotime($p['created_at'])) < 60;
        }
    }
    return false;
}

function get_comment_count($post_id) {
    $comments = MemoryStore::getComments();
    return count(array_filter($comments, fn($c) => $c['post_id'] == $post_id));
}

// User account management functions
function create_account($username, $password) {
    $db = get_db();
    $hash = password_hash($password, PASSWORD_ARGON2ID);
    
    try {
        $stmt = $db->prepare("INSERT INTO accounts (username, password_hash) VALUES (?, ?)");
        $stmt->execute([$username, $hash]);
        return true;
    } catch (PDOException $e) {
        return false; // Username already exists
    }
}

function verify_account($username, $password) {
    $db = get_db();
    $stmt = $db->prepare("SELECT password_hash, banned FROM accounts WHERE username = ?");
    $stmt->execute([$username]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$account || $account['banned']) return false;
    return password_verify($password, $account['password_hash']);
}

function set_account_banned($username, $banned) {
    $db = get_db();
    $stmt = $db->prepare("UPDATE accounts SET banned = ? WHERE username = ?");
    $stmt->execute([(int)!!$banned, $username]);
}

function ban_account($username) { set_account_banned($username, true); }
function unban_account($username) { set_account_banned($username, false); }

function set_account_restricted($username, $restricted) {
    $db = get_db();
    $stmt = $db->prepare("UPDATE accounts SET restricted = ? WHERE username = ?");
    $stmt->execute([(int)!!$restricted, $username]);
}

function restrict_account($username) { set_account_restricted($username, true); }
function unrestrict_account($username) { set_account_restricted($username, false); }

function is_account_restricted($username) {
    $db = get_db();
    $stmt = $db->prepare("SELECT restricted FROM accounts WHERE username = ?");
    $stmt->execute([$username]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    return $account && (int)$account['restricted'] === 1;
}

function is_banned($username) {
    $db = get_db();
    $stmt = $db->prepare("SELECT banned FROM accounts WHERE username = ?");
    $stmt->execute([$username]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    return $account && $account['banned'];
}

function list_accounts($limit = 200) {
    $db = get_db();
    $limit = max(1, min(1000, (int)$limit));
    $stmt = $db->query("SELECT username, created_at, banned, restricted FROM accounts ORDER BY created_at DESC LIMIT $limit");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function delete_account($username) {
    // First delete all user content from RAM
    $deleted = MemoryStore::deleteUserContent($username);
    
    // Then delete from database
    $db = get_db();
    $stmt = $db->prepare("DELETE FROM accounts WHERE username = ?");
    $stmt->execute([$username]);
    
    return $deleted;
}

function get_account_info($username) {
    $db = get_db();
    $stmt = $db->prepare("SELECT id, username, created_at, banned FROM accounts WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
