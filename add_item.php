<?php include 'db.php'; ?>
<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// On form submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title']);
    $pages = intval($_POST['pages']);
    $desc = substr(trim($_POST['description']), 0, 255);
    $category_id = intval($_POST['category']);

    // Insert book
    $stmt = $conn->prepare("INSERT INTO books (title, pages, description, category_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sisi", $title, $pages, $desc, $category_id);
    if ($stmt->execute()) {
        $book_id = $stmt->insert_id;
        $stmt->close();

        // Image upload
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $files = $_FILES['images'];
        $count = min(3, count($files['name']));
        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $tmp = $files['tmp_name'][$i];
                $type = mime_content_type($tmp);
                if (!in_array($type, $allowed)) continue;

                $newname = uniqid() . "_" . basename($files['name'][$i]);
                if (move_uploaded_file($tmp, $upload_dir . $newname)) {
                    $img_stmt = $conn->prepare("INSERT INTO book_images (book_id, filename) VALUES (?, ?)");
                    $img_stmt->bind_param("is", $book_id, $newname);
                    $img_stmt->execute();
                    $img_stmt->close();
                }
            }
        }

        $_SESSION['success_message'] = "Book added successfully!";
        header("Location: index.php");
        exit;
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}

// Load categories
$cats = $conn->query("SELECT * FROM categories ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Book</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet" />
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container my-5">
  <div class="card shadow">
    <div class="card-header bg-white">
      <h4 class="mb-0"><i class="bi bi-book"></i> Add New Book</h4>
    </div>
    <div class="card-body">
      <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Book Title *</label>
            <input type="text" name="title" class="form-control" required />
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Pages *</label>
            <input type="number" name="pages" class="form-control" required />
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Category *</label>
            <select name="category" class="form-select" required>
              <option value="">-- Select Category --</option>
              <?php while ($row = $cats->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-12 mb-3">
            <label class="form-label">Description *</label>
            <textarea name="description" class="form-control" maxlength="255" rows="3" required></textarea>
          </div>
          <div class="col-md-12 mb-4">
            <label class="form-label">Upload Images (Max 3)</label>
            <input type="file" name="images[]" class="form-control" accept="image/*" multiple required />
            <small class="text-muted">Allowed: JPG, PNG, WEBP</small>
          </div>
        </div>

        <div class="d-flex justify-content-between">
          <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Cancel</a>
          <button name="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Book</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  (() => {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
      form.addEventListener('submit', e => {
        if (!form.checkValidity()) {
          e.preventDefault();
          e.stopPropagation();
        }
        form.classList.add('was-validated');
      });
    });
  })();
</script>
</body>
</html>
