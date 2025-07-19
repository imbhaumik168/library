<?php include 'db.php'; ?>
<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin Dashboard - Books</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">All Books</h2>
    <a href="add_book.php" class="btn btn-dark">
      <i class="bi bi-plus-circle me-1"></i> Add Book
    </a>
  </div>

  <?php
  $cats = $conn->query("SELECT * FROM categories");
  $selectedCat = isset($_GET['category']) ? intval($_GET['category']) : 0;
  ?>

  <form method="GET" class="row g-2 mb-4">
    <div class="col-md-4">
      <select name="category" class="form-select">
        <option value="0">All Categories</option>
        <?php while ($row = $cats->fetch_assoc()) { ?>
          <option value="<?= $row['id'] ?>" <?= ($selectedCat == $row['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($row['name']) ?>
          </option>
        <?php } ?>
      </select>
    </div>
    <div class="col-md-auto">
      <button type="submit" class="btn btn-outline-dark">Filter</button>
    </div>
  </form>

  <?php
  $sql = "SELECT b.*, c.name AS category_name 
          FROM books b 
          JOIN categories c ON b.category_id = c.id";

  if ($selectedCat > 0) {
      $sql .= " WHERE b.category_id = $selectedCat";
  }

  $sql .= " ORDER BY b.created_at DESC";

  $items = $conn->query($sql);

  if ($items && $items->num_rows > 0) {
  ?>
    <div class="row row-cols-1 row-cols-md-3 g-4">
      <?php while ($row = $items->fetch_assoc()) {
        $image = $conn->query("SELECT filename FROM book_images WHERE book_id = {$row['id']} LIMIT 1")->fetch_assoc();
      ?>
      <div class="col">
        <div class="card h-100">
          <?php if ($image) { ?>
            <img src="uploads/<?= $image['filename'] ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
          <?php } else { ?>
            <div class="bg-light d-flex justify-content-center align-items-center" style="height: 200px;">
              <i class="bi bi-book text-secondary fs-1"></i>
            </div>
          <?php } ?>
          <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
            <p class="card-text"><?= htmlspecialchars($row['description']) ?></p>
            <p class="card-text"><strong>Pages:</strong> <?= $row['pages'] ?></p>
            <p class="card-text"><small class="text-muted">Category: <?= htmlspecialchars($row['category_name']) ?></small></p>
            <p class="card-text"><small class="text-muted">Added: <?= date('M d, Y', strtotime($row['created_at'])) ?></small></p>
          </div>
          <div class="card-footer d-flex justify-content-between">
            <a href="view_book.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">View</a>
            <a href="edit_book.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
          </div>
        </div>
      </div>
      <?php } ?>
    </div>
  <?php
  } else {
    echo '<div class="alert alert-info text-center">No books found.</div>';
  }
  ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
