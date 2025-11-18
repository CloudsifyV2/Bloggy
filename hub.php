<?php
session_start();
require_once __DIR__ . '/config/MySQL.php';
include 'components/navbar.php';

// Function to create a URL-friendly slug
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="styles/hub.css">
  <title>Bloggy</title>
</head>
<body>
<main class="container">

  <!-- Tag Filter -->
  <div class="filter-section">
    <h3>Filter by Tag</h3>
    <div class="tag-filters" id="tagFilters">
      <span class="tag-badge active" data-tag="">All Posts</span>
      <span class="tag-badge inactive" data-tag="Technology">Technology</span>
      <span class="tag-badge inactive" data-tag="Web Development">Web Development</span>
      <span class="tag-badge inactive" data-tag="Design">Design</span>
      <span class="tag-badge inactive" data-tag="UX">UX</span>
      <span class="tag-badge inactive" data-tag="Travel">Travel</span>
      <span class="tag-badge inactive" data-tag="Lifestyle">Lifestyle</span>
      <span class="tag-badge inactive" data-tag="Food">Food</span>
      <span class="tag-badge inactive" data-tag="Fitness">Fitness</span>
      <span class="tag-badge inactive" data-tag="Business">Business</span>
    </div>
  </div>

  <!-- Blog Posts Grid -->
  <div class="blog-grid" id="blogGrid">
    <?php
    $sql = "
      SELECT posts.*, users.username AS author_name 
      FROM posts
      LEFT JOIN users ON posts.author_id = users.id
      ORDER BY date_posted DESC
    ";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($post = $result->fetch_assoc()) {
            $tagsArray = explode(',', $post['tags']);
            $slug = slugify($post['title']);
            $url = "post_content.php?id={$post['id']}&slug={$slug}";
            $author = $post['author_name'] ?? 'Unknown Author';
            ?>
            <article class="blog-card" data-tags="<?php echo htmlspecialchars($post['tags']); ?>" onclick="window.location='<?php echo $url; ?>'">
              <div class="blog-card-image-container">
                <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="blog-card-image">
              </div>
              <div class="blog-card-header">
                <div class="blog-card-tags">
                  <?php foreach ($tagsArray as $t): ?>
                    <span class="card-tag"><?php echo trim(htmlspecialchars($t)); ?></span>
                  <?php endforeach; ?>
                </div>
                <h2 class="blog-card-title"><?php echo htmlspecialchars($post['title']); ?></h2>
                <p class="blog-card-author">By <?php echo htmlspecialchars($author); ?></p>
              </div>
              <div class="blog-card-content">
                <p class="blog-card-excerpt"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                <p class="blog-card-date"><?php echo date('F j, Y', strtotime($post['date_posted'])); ?></p>
              </div>
            </article>
            <?php
        }
    } else {
        $_SESSION['error'] = "No posts found.";
    }

    // Get all unique, capitalized tags for logging
    $tagsQuery = "SELECT tags FROM posts";
    $tagsResult = $conn->query($tagsQuery);

    $allTags = [];
    if ($tagsResult && $tagsResult->num_rows > 0) {
        while ($row = $tagsResult->fetch_assoc()) {
            $tags = explode(',', $row['tags']);
            foreach ($tags as $tag) {
                $trimmed = trim($tag);
                if (!empty($trimmed)) {
                    $capitalized = ucfirst(strtolower($trimmed));
                    $allTags[] = $capitalized;
                }
            }
        }
    }
    $uniqueTags = array_unique($allTags);

    $conn->close();
    ?>
  </div>

  <div class="no-results hidden" id="noResults">

  </div>

</main>

<script>
  // Tag filtering functionality
  const tagFilters = document.querySelectorAll('.tag-badge');
  const blogCards = document.querySelectorAll('.blog-card');
  const blogGrid = document.getElementById('blogGrid');
  const noResults = document.getElementById('noResults');

  tagFilters.forEach(badge => {
    badge.addEventListener('click', () => {
      const selectedTag = badge.getAttribute('data-tag');

      // Update active state
      tagFilters.forEach(b => {
        b.classList.remove('active');
        b.classList.add('inactive');
      });
      badge.classList.add('active');
      badge.classList.remove('inactive');

      // Filter blog cards
      let visibleCount = 0;
      blogCards.forEach(card => {
        const cardTags = card.getAttribute('data-tags').split(',');
        if (selectedTag === '' || cardTags.includes(selectedTag)) {
          card.classList.remove('hidden');
          visibleCount++;
        } else {
          card.classList.add('hidden');
        }
      });

      // Show/hide no results message
      if (visibleCount === 0) {
        blogGrid.classList.add('hidden');
        noResults.classList.remove('hidden');
      } else {
        blogGrid.classList.remove('hidden');
        noResults.classList.add('hidden');
      }
    });
  });

  const tagsFromDatabase = <?php echo json_encode(array_values($uniqueTags)); ?>;
  console.log("Tags from database (capitalized):", tagsFromDatabase);
</script>
</body>
</html>
