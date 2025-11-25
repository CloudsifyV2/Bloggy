<?php
include 'components/navbar.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bloggy - <?php echo htmlspecialchars($_SESSION['username'] ?? 'Profile'); ?></title>
</head>
<body>
    
    <div class="profile">
        <?php
        $userInitial = isset($_SESSION['username']) ? strtoupper($_SESSION['username'][0]) : 'G';
        ?>
        <div class="user-avatar" id="userAvatar"><?= htmlspecialchars($userInitial) ?></div>
        <h1><?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?></h1>
        <p>Member since: <?php echo htmlspecialchars($_SESSION['member_since'] ?? 'N/A'); ?></p>

        <h1> Your Posts</h1>
        <div class="user-posts">
            <?php
            require_once __DIR__ . '/config/MySQL.php';

            if (isset($_SESSION['user_id'])) {
                $userId = $conn->real_escape_string($_SESSION['user_id']);
                $sql = "SELECT * FROM posts WHERE author_id = '$userId' ORDER BY date_posted DESC";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while ($post = $result->fetch_assoc()) {
                        echo '<div class="post-item">';
                        echo '<h3>' . htmlspecialchars($post['title']) . '</h3>';
                        echo '<p>' . htmlspecialchars(substr($post['content'], 0, 100)) . '...</p>';
                        echo '<a href="post_content.php?id=' . $post['id'] . '">Read More</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No posts found.</p>';
                }
            } else {
                echo '<p>Please log in to view your posts.</p>';
            }
            ?>
    </div>
</body>
</html>