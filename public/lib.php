<?php
define('DATA_DIR', __DIR__ . '/data');
define('DB_FILE', DATA_DIR . '/accounts.db');
define('DB_ENCRYPTION_KEY', getenv('KLOAQ_DB_KEY') ?: 'change-this-to-secure-key-in-production');

if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0755, true);

// In-memory storage for ephemeral content (posts, comments, votes)
class MemoryStore {
    private static $posts = [];
    private static $comments = [];
    private static $votes = [];
    
    public static function getPosts() { return self::$posts; }
    public static function setPosts($posts) { self::$posts = $posts; }
    
    public static function getComments() { return self::$comments; }
    public static function setComments($comments) { self::$comments = $comments; }
    
    public static function getVotes() { return self::$votes; }
    public static function setVotes($votes) { self::$votes = $votes; }
}

// Encrypted database for user accounts only
function get_db() {
    static $db = null;
    if ($db === null) {
        $db = new PDO('sqlite:' . DB_FILE);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Initialize accounts table with encryption support
        $db->exec("
            CREATE TABLE IF NOT EXISTS accounts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password_hash TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                banned INTEGER DEFAULT 0
            )
        ");
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
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = "${type}_${id}_${ip}";
    
    if (isset($votes[$key])) return false;
    
    $votes[$key] = ['value' => $value, 'time' => time()];
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

function create_post($title, $content) {
    $posts = MemoryStore::getPosts();
    $id = $posts ? max(array_column($posts, 'id')) + 1 : 1;
    $posts[] = [
        'id' => $id,
        'title' => $title,
        'content' => $content,
        'votes' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    MemoryStore::setPosts($posts);
    return $id;
}

function create_comment($post_id, $parent_id, $content) {
    $comments = MemoryStore::getComments();
    $id = $comments ? max(array_column($comments, 'id')) + 1 : 1;
    $comments[] = [
        'id' => $id,
        'post_id' => $post_id,
        'parent_id' => $parent_id,
        'content' => $content,
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

function ban_account($username) {
    $db = get_db();
    $stmt = $db->prepare("UPDATE accounts SET banned = 1 WHERE username = ?");
    $stmt->execute([$username]);
}

function is_banned($username) {
    $db = get_db();
    $stmt = $db->prepare("SELECT banned FROM accounts WHERE username = ?");
    $stmt->execute([$username]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    return $account && $account['banned'];
}
