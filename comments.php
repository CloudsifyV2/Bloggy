<?php
session_start();

require_once __DIR__ . '/config/MySQL.php';

$post_id = $_GET['post_id'] ?? null;

if (!$post_id || !is_numeric($post_id)) {
    die("Invalid post ID.");
}

/* Handle comment submission */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_SESSION['username'])) {
        echo "<p>You must be logged in to comment.</p>";
        exit;
    }

    $comment = trim($_POST['comment']);
    if (empty($comment)) {
        die("Comment cannot be empty.");
    }

    $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment_text) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $post_id, $_SESSION['user_id'], $comment);
    $stmt->execute();
    $stmt->close();

    echo "success";
    exit;
}

/* Fetch comments */
$stmt = $conn->prepare("
    SELECT comments.*, users.username 
    FROM comments
    JOIN users ON comments.user_id = users.id
    WHERE post_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];

while ($row = $result->fetch_assoc()) {
    $comments[] = $row;
}

echo json_encode($comments);
$stmt->close();
