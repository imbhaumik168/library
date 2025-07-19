<?php include 'db.php'; ?>
<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);

// Fetch book and its images
$book = $conn->query("SELECT * FROM books WHERE id = $id")->fetch_assoc();
$cats = $conn->query("SELECT * FROM categories");
$images = $conn->query("SELECT * FROM book_images WHERE book_id = $id");

if (isset($_POST['submit'])) {
    $title = trim($_POST['title']);
    $pages = intval($_POST['pages']);
    $desc = substr(trim($_POST['description']), 0, 255);
    $category_id = intval($_POST['category']);

    $stmt = $conn->prepare("UPDATE books SET title = ?, pages = ?, description = ?, category_id = ? WHERE id = ?");
    $stmt->bind_param("sisii", $title, $pages, $desc, $category_id, $id);

    if ($stmt->execute()) {
        $upload_dir = 'uploads/';
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];

        $existing_count = $conn->query("SELECT COUNT(*) as count FROM book_images WHERE book_id = $id")->fetch_assoc()['count'];

        $uploaded_files = $_FILES['images'];
        $num_files = count($uploaded_files['name']);

        if ($num_files + $existing_count > 5) {
            die("<div class='alert alert-danger'>Cannot have more than 5 images. You already have $existing_count.</div>");
        }

        for ($i = 0; $i < $num_files; $i++) {
            if ($uploaded_files['error'][$i] === UPLOAD_ERR_OK) {
                $tmp_name = $uploaded_files['tmp_name'][$i];
                $type = mime_content_type($tmp_name);
                if (!in_array($type, $allowed_types)) {
                    die("<div class='alert alert-danger'>Invalid image type uploaded.</div>");
                }

                $name = basename($uploaded_files['name'][$i]);
                $new_name = uniqid() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "", $name);
                move_uploaded_file($tmp_name, $upload_dir . $new_name);

                $stmt = $conn->prepare("INSERT INTO book_images (book_id, filename) VALUES (?, ?)");
                $stmt->bind_param("is", $id, $new_name);
                $stmt->execute();
                $stmt->close();
            }
        }

        $_SESSION['success_message'] = "Book updated successfully!";
        header("Location: view_book.php?id=$id");
        exit;
    } else {
        echo "<div class='alert alert-danger'>Error updating book: " . $conn->error . "</div>";
    }
}

if (isset($_GET['delete_image'])) {
    $img_id = intval($_GET['delete_image']);
    $img = $conn->query("SELECT filename FROM book_images WHERE id = $img_id")->fetch_assoc();
    if ($img) {
        unlink('uploads/' . $img['filename']);
        $conn->query("DELETE FROM book_images WHERE id = $img_id");
        header("Location: edit_book.php?id=$id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Book</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container my-5 animate-fade">
  <div class="card shadow">
    <div class="card-header bg-white">
      <h3 class="mb-0 d-flex align-items-center">
        <i class="bi bi-pencil me-2"></i> Edit Book
      </h3>
    </div>
    <div class="card-body">
      <form method="POST" enctype="multipart/form-data">
        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Title *</label>
              <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($book['title']) ?>" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Pages *</label>
              <input type="number" name="pages" class="form-control" value="<?= $book['pages'] ?>" required>
            </div>
          </div>

          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Category *</label>
              <select name="category" class="form-select" required>
                <?php while ($row = $cats->fetch_assoc()): ?>
                  <option value="<?= $row['id'] ?>" <?= ($row['id'] == $book['category_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['name']) ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Description *</label>
              <textarea name="description" class="form-control" rows="3" maxlength="255" required><?= htmlspecialchars($book['description']) ?></textarea>
            </div>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label">Current Images</label>
          <div class="d-flex flex-wrap">
            <?php while ($img = $images->fetch_assoc()): ?>
              <div class="position-relative me-3 mb-3">
                <img src="uploads/<?= $img['filename'] ?>" class="img-thumb" alt="" style="height:100px; width:auto;">
                <a href="edit_book.php?id=<?= $id ?>&delete_image=<?= $img['id'] ?>" 
                   class="position-absolute top-0 start-100 translate-middle btn btn-sm btn-danger rounded-circle"
                   onclick="return confirm('Delete this image?')">
                  <i class="bi bi-x"></i>
                </a>
              </div>
            <?php endwhile; ?>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label">Add More Images (max total 5)</label>
          <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
          <small class="text-muted">Leave empty to keep current images</small>
        </div>

        <div class="d-flex justify-content-between">
          <a href="view_book.php?id=<?= $id ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Cancel
          </a>
          <button name="submit" class="btn btn-primary">
            <i class="bi bi-save"></i> Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
