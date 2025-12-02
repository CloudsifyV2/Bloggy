<?php
require_once __DIR__ . '/../config/MySQL.php'; // this creates $conn (mysqli)

header('Content-Type: application/json');

$term = $_GET['term'] ?? '';
$term = "%$term%";

// Prepare statement
$stmt = $conn->prepare("SELECT * FROM posts WHERE tags LIKE ? ORDER BY date_posted");
$stmt->bind_param("s", $term);
$stmt->execute();

// Get results
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

// Return JSON
echo json_encode($data);
exit;
