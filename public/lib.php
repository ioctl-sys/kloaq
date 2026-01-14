<?php
define('DATA_DIR', __DIR__ . '/data');
define('POSTS_FILE', DATA_DIR . '/posts.json');
define('COMMENTS_FILE', DATA_DIR . '/comments.json');
define('VOTES_FILE', DATA_DIR . '/votes.json');

if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0755, true);

function load_json($file, $default = []) {
    if (!file_exists($file)) return $default;
    $content = file_get_contents($file);
    return $content ? json_decode($content, true) : $default;
}

function save_json($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

function get_posts($sort = 'hot') {
    $posts = load_json(POSTS_FILE);
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
    $posts = load_json(POSTS_FILE);
    foreach ($posts as $p) if ($p['id'] == $id) return $p;
    return null;
}

function get_comments($post_id, $parent_id = null) {
    $comments = load_json(COMMENTS_FILE);
    $result = array_filter($comments, function($c) use ($post_id, $parent_id) {
        return $c['post_id'] == $post_id && $c['parent_id'] == $parent_id;
    });
    usort($result, function($a, $b) {
        return $b['votes'] - $a['votes'] ?: strtotime($a['created_at']) - strtotime($b['created_at']);
    });
    return array_values($result);
}

function vote($type, $id, $value) {
    $votes = load_json(VOTES_FILE);
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = "${type}_${id}_${ip}";
    
    if (isset($votes[$key])) return false;
    
    $votes[$key] = ['value' => $value, 'time' => time()];
    save_json(VOTES_FILE, $votes);
    
    if ($type === 'post') {
        $posts = load_json(POSTS_FILE);
        foreach ($posts as &$p) if ($p['id'] == $id) { $p['votes'] += $value; break; }
        save_json(POSTS_FILE, $posts);
    } else {
        $comments = load_json(COMMENTS_FILE);
        foreach ($comments as &$c) if ($c['id'] == $id) { $c['votes'] += $value; break; }
        save_json(COMMENTS_FILE, $comments);
    }
    return true;
}

function create_post($title, $content) {
    $posts = load_json(POSTS_FILE);
    $id = $posts ? max(array_column($posts, 'id')) + 1 : 1;
    $posts[] = [
        'id' => $id,
        'title' => $title,
        'content' => $content,
        'votes' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    save_json(POSTS_FILE, $posts);
    return $id;
}

function create_comment($post_id, $parent_id, $content) {
    $comments = load_json(COMMENTS_FILE);
    $id = $comments ? max(array_column($comments, 'id')) + 1 : 1;
    $comments[] = [
        'id' => $id,
        'post_id' => $post_id,
        'parent_id' => $parent_id,
        'content' => $content,
        'votes' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
    save_json(COMMENTS_FILE, $comments);
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
    $votes = load_json(VOTES_FILE);
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = "${type}_${id}_${ip}";
    
    if (isset($votes[$key])) {
        return (time() - $votes[$key]['time']) < 60;
    }
    
    if ($type === 'post') {
        $posts = load_json(POSTS_FILE);
        foreach ($posts as $p) if ($p['id'] == $id) {
            return (time() - strtotime($p['created_at'])) < 60;
        }
    }
    return false;
}

function get_comment_count($post_id) {
    $comments = load_json(COMMENTS_FILE);
    return count(array_filter($comments, fn($c) => $c['post_id'] == $post_id));
}
