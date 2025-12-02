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
  <title>Bloggy – Tag Search</title>
</head>
<body>

<main class="container">

  <!-- Tag Search -->
  <div class="filter-section">
      <h3>Find by Tag</h3>
      <input type="text" id="tagSearchInput" placeholder="Search tags..." />
  </div>

  <!-- Live Search Results -->
  <div id="searchResults" class="blog-grid hidden"></div>
  <div id="noSearchResults" class="no-results hidden">No posts found.</div>

</main>

<script>
/* ------------------------------
   Tag Search Logic
------------------------------ */

document.getElementById("tagSearchInput").addEventListener("input", function () {
    let term = this.value.trim();
    const resultsDiv = document.getElementById("searchResults");
    const noResultsDiv = document.getElementById("noSearchResults");

    // Clear old results
    resultsDiv.innerHTML = "";

    // If empty input → hide everything
    if (term.length === 0) {
        resultsDiv.classList.add("hidden");
        noResultsDiv.classList.add("hidden");
        return;
    }

    fetch("DO/do_search_tags.php?term=" + encodeURIComponent(term))
        .then(res => res.json())
        .then(data => {
            console.log("Results:", data);

            if (data.length === 0) {
                resultsDiv.classList.add("hidden");
                noResultsDiv.classList.remove("hidden");
                return;
            }

            noResultsDiv.classList.add("hidden");
            resultsDiv.classList.remove("hidden");

            data.forEach(post => {
                let slug = slugify(post.title);
                let url = `post_content.php?id=${post.id}&slug=${slug}`;
                let tags = post.tags.split(",").map(t => t.trim());

                let card = document.createElement("article");
                card.classList.add("blog-card");
                card.setAttribute("data-tags", post.tags);
                card.onclick = () => window.location = url;

                card.innerHTML = `
                    <div class="blog-card-image-container">
                        <img src="${post.image_url}" 
                             alt="${escapeHtml(post.title)}" 
                             class="blog-card-image">
                    </div>

                    <div class="blog-card-header">
                        <div class="blog-card-tags">
                           ${tags.map(t => `<span class="card-tag">${escapeHtml(capitalize(t))}</span>`).join("")}
                        </div>
                        <h2 class="blog-card-title">${escapeHtml(post.title)}</h2>
                        <p class="blog-card-author">By ${escapeHtml(post.author_name || "Unknown Author")}</p>
                    </div>

                    <div class="blog-card-content">
                        <p class="blog-card-excerpt">${escapeHtml(post.excerpt)}</p>
                        <p class="blog-card-date">${formatDate(post.date_posted)}</p>
                    </div>
                `;

                resultsDiv.appendChild(card);
            });
        });
});

/* ------------------------------
   Helper Functions
------------------------------ */

function escapeHtml(text) {
    if (!text) return "";
    const map = { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" };
    return text.replace(/[&<>"]/g, m => map[m]);
}

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
}

function slugify(text) {
    return text
        .toLowerCase()
        .replace(/[^\w]+/g, "-")
        .replace(/-+/g, "-")
        .replace(/^-|-$/g, "");
}

function formatDate(dateStr) {
    let date = new Date(dateStr);
    return date.toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric"
    });
}
</script>

<?php include 'components/footer.php'; ?>
</body>
</html>
