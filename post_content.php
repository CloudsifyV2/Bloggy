<?php
session_start();
require_once __DIR__ . '/config/MySQL.php';
include 'components/navbar.php';

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
  echo "Invalid post ID.";
  exit;
}

// Fetch the post with the author info
$stmt = $conn->prepare("
    SELECT posts.*, users.username AS author_name 
    FROM posts 
    JOIN users ON posts.author_id = users.id
    WHERE posts.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  echo "Post not found.";
  exit;
}

$post = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="styles/content.css">
  <title><?php echo htmlspecialchars($post['title']); ?></title>
</head>

<body>
  <div class="hero">
    <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
    <div class="hero-overlay"></div>
  </div>

  <div class="container">
    <article class="article">
      <div class="article-content">
        <h1><?php echo htmlspecialchars($post['title']); ?></h1>
        <div class="article-meta">
          <span class="author">By <?php echo htmlspecialchars($post['author_name']); ?></span>
        </div>

        <div class="tags">
          <?php
          $tagsArray = explode(',', $post['tags']);
          foreach ($tagsArray as $tag) {
            echo '<span class="tag">' . htmlspecialchars(trim($tag)) . '</span>';
          }
          ?>
        </div>

        <div class="article-meta">
          <span class="date">Published on <?php echo date('F j, Y', strtotime($post['date_posted'])); ?></span>
        </div>

        <div class="separator"></div>

        <div class="article-body">
          <?php echo nl2br(htmlspecialchars($post['content'])); ?>
        </div>
      </div>
    </article>

    <article class="comments-section">
      <div class="article-content">
        <h1>Comments</h1>
        <div id="comments-container">
          <!-- Comments will be loaded here via PHP -->

        </div>
      </div>

      <div class="comment-form">
        <textarea id="comment-text" placeholder="Write your comment..."></textarea><br>
        <button onclick="submitComment()">Submit</button>
      </div>
    </article>
  </div>

  <script>
    function loadComments() {
      fetch("comments.php?post_id=<?php echo $id; ?>")
        .then(res => res.json())
        .then(data => {
          let container = document.getElementById("comments-container");
          container.innerHTML = "";

          if (data.length === 0) {
            container.innerHTML = "<p>No comments yet.</p>";
            return;
          }

          data.forEach(comment => {
            container.innerHTML += `
              <div class="comment">
                <p><strong>${comment.username}</strong>
                <span class="date">${new Date(comment.created_at).toLocaleString()}</span></p>
                <p>${comment.comment_text}</p>
                <hr>
              </div>
            `;
          });
        });
    }

    function submitComment() {
      let text = document.getElementById("comment-text").value;

      if (text.trim() === "") {
        alert("Comment cannot be empty.");
        return;
      }

      fetch("comments.php?post_id=<?php echo $id; ?>", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "comment=" + encodeURIComponent(text)
      })
        .then(res => res.text())
        .then(data => {
          if (data === "success") {
            document.getElementById("comment-text").value = "";
            loadComments(); // refresh comment list
          } else {
            alert(data);
          }
        });
    }

    loadComments();
    </script>
  <?php include 'components/footer.php'; ?>
</body>

</html>