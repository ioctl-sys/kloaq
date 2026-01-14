<?php
require_once 'lib.php';

$action = $_GET['action'] ?? '';

if ($action === 'vote' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_GET['type'] ?? '';
    $id = $_GET['id'] ?? 0;
    $value = $_POST['value'] ?? 0;
    
    if (in_array($type, ['post', 'comment']) && in_array($value, [1, -1])) {
        vote($type, $id, $value);
    }
    
    $sort = $_GET['sort'] ?? 'hot';
    if ($type === 'post') {
        header("Location: ?sort=$sort");
    } else {
        $post_id = $_GET['post_id'] ?? 0;
        header("Location: ?action=view&id=$post_id");
    }
    exit;
}

if ($action === 'comment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = $_GET['post_id'] ?? 0;
    $parent_id = $_GET['parent_id'] ?? null;
    $content = trim($_POST['content'] ?? '');
    
    if ($content && $post_id) {
        create_comment($post_id, $parent_id, $content);
    }
    
    header("Location: ?action=view&id=$post_id");
    exit;
}

if ($action === 'view') {
    require 'view.php';
    exit;
}

if ($action === 'submit') {
    require 'submit.php';
    exit;
}

if ($action === 'reply') {
    require 'view.php';
    exit;
}

require 'home.php';
