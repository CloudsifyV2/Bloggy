<?php
session_start();
require_once __DIR__ . '/config/MySQL.php';
include 'components/navbar.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to create a post.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $author_id = $_SESSION['user_id'];
    $title = trim($_POST['title'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $tags = trim($_POST['tags'] ?? '');

    if (empty($title) || empty($content)) {
        $error = "Title and content are required.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO posts (author_id, title, excerpt, content, image_url, tags, date_posted)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("isssss", $author_id, $title, $excerpt, $content, $image_url, $tags);

        if ($stmt->execute()) {
            header("Location: hub.php");
            exit;
        } else {
            $error = "Error creating post: " . $stmt->error;
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="styles/content.css">
  <title>Create a New Post</title>
</head>
<body>
  <div class="hero">
    <img id="heroImage" src="" alt="Post Preview">
    <div class="hero-overlay"></div>
  </div>

  <div class="container">
    <article class="article">
      <div class="article-content">
        <h1>Create a New Post</h1>

        <?php if (!empty($error)): ?>
          <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" class="create-post-form">
          <label for="title">Title:</label>
          <input type="text" id="title" name="title" required placeholder="Enter post title">

          <label for="excerpt">Excerpt:</label>
          <textarea id="excerpt" name="excerpt" rows="2" placeholder="Short summary..."></textarea>

          <label for="content">Content:</label>
          <textarea id="content" name="content" rows="10" required placeholder="Write your post content here..."></textarea>

          <label for="image_url">Image URL:</label>
          <input type="url" id="image_url" name="image_url" placeholder="https://example.com/image.jpg">

          <label for="tags">Tags (comma-separated):</label>
          <input type="text" id="tags" name="tags" placeholder="e.g. php,webdev,blog">

          <button type="submit" class="btn">Publish Post</button>
        </form>
      </div>
    </article>
  </div>

  <script>
    // 👇 Live hero image preview
    document.addEventListener('DOMContentLoaded', () => {
      const imageInput = document.getElementById('image_url');
      const heroImage = document.getElementById('heroImage');

      imageInput.addEventListener('input', () => {
        const url = imageInput.value.trim();
        if (url.match(/^https?:\/\/.+\.(jpg|jpeg|png|gif|webp)$/i)) {
          heroImage.src = url;
        } else if (url === '') {
          heroImage.src = '';
        }
      });
    });

    async function isImageUrl(url) {
      try {
        const response = await fetch(url, { method: "HEAD" });
        const contentType = response.headers.get("content-type");
        return contentType && contentType.startsWith("image/");
      } catch (err) {
        return false;
      }
    }
  </script>
    <?php include 'components/footer.php'; ?>

</body>
</html>
