<?php include 'db.php'; ?>
<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
$item = $conn->query("
  SELECT b.*, c.name AS category_name 
  FROM books b 
  JOIN categories c ON b.category_id = c.id 
  WHERE b.id = $id
")->fetch_assoc();

if (!$item) {
  header("Location: index.php");
  exit;
}

$images = $conn->query("SELECT * FROM book_images WHERE book_id = $id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($item['title']) ?> | Book</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><?= htmlspecialchars($item['title']) ?></h2>
    <div class="btn-group">
      <a href="edit_book.php?id=<?= $item['id'] ?>" class="btn btn-warning">
        <i class="bi bi-pencil"></i> Edit
      </a>
      <a href="delete_book.php?id=<?= $item['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this book?')">
        <i class="bi bi-trash"></i> Delete
      </a>
    </div>
  </div>

  <!-- Carousel -->
  <?php if ($images->num_rows > 0): ?>
  <div id="bookCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
    <div class="carousel-inner rounded">
      <?php $first = true; while ($img = $images->fetch_assoc()): ?>
        <div class="carousel-item <?= $first ? 'active' : '' ?>">
          <img src="uploads/<?= $img['filename'] ?>" class="d-block w-100" style="height: 400px; object-fit: contain;" alt="Book Image">
        </div>
      <?php $first = false; endwhile; ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#bookCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#bookCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Next</span>
    </button>
  </div>
  <?php else: ?>
    <div class="card mb-4">
      <div class="card-body text-center py-5">
        <i class="bi bi-image text-muted display-4"></i>
        <p class="text-muted mt-3">No images available for this book</p>
      </div>
    </div>
  <?php endif; ?>

  <!-- Book Info -->
  <div class="row">
    <div class="col-md-6">
      <div class="card mb-4">
        <div class="card-header bg-white">
          <h5 class="mb-0">Book Information</h5>
        </div>
        <div class="card-body">
          <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span>Category</span>
              <span class="badge bg-primary"><?= htmlspecialchars($item['category_name']) ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span>Pages</span>
              <span class="badge bg-secondary"><?= htmlspecialchars($item['pages']) ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span>Added On</span>
              <span><?= date('M d, Y', strtotime($item['created_at'])) ?></span>
            </li>
          </ul>
        </div>
      </div>
    </div>
    
    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-header bg-white">
          <h5 class="mb-0">Description</h5>
        </div>
        <div class="card-body">
          <p><?= nl2br(htmlspecialchars($item['description'])) ?></p>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-4">
    <a href="index.php" class="btn btn-outline-secondary">
      <i class="bi bi-arrow-left"></i> Back to All Books
    </a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
