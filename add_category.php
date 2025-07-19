<?php include 'db.php'; ?>
<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $image = $_FILES['cover_image'] ?? null;
    
    if ($name !== "") {
        // Handle image upload
        $imagePath = '';
        if ($image && $image['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'categories/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $fileType = mime_content_type($image['tmp_name']);
            
            if (!in_array($fileType, $allowedTypes)) {
                $error = "Only JPG, PNG, and WEBP images are allowed.";
            } else {
                $extension = pathinfo($image['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $extension;
                $targetPath = $uploadDir . $filename;
                
                if (move_uploaded_file($image['tmp_name'], $targetPath)) {
                    $imagePath = $targetPath;
                } else {
                    $error = "Failed to upload image.";
                }
            }
        }
        
        if (!$error) {
            $stmt = $conn->prepare("INSERT INTO categories (name, cover_image) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $imagePath);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Category added successfully!";
                header("Location: add_category.php");
                exit;
            } else {
                $error = "Error adding category: " . $conn->error;
            }
            $stmt->close();
        }
    } else {
        $error = "Category name cannot be empty.";
    }
}

// Get all categories
$cats = $conn->query("SELECT * FROM categories ORDER BY name");
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Categories</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/style.css">
  <style>
    .category-img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 4px;
    }
    .empty-img {
        width: 80px;
        height: 80px;
        background: #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
    }
    [data-bs-theme="dark"] .empty-img {
        background: #2d3436;
    }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container my-5 animate-fade">
  <div class="row">
    <div class="col-md-6">
      <div class="card shadow mb-4">
        <div class="card-header bg-white">
          <h3 class="mb-0 d-flex align-items-center">
            <i class="bi bi-plus-circle me-2"></i> Add New Category
          </h3>
        </div>
        <div class="card-body">
          <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
          <?php endif; ?>
          <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success_message'] ?></div>
            <?php unset($_SESSION['success_message']); ?>
          <?php endif; ?>
          
          <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
              <label class="form-label">Category Name *</label>
              <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Cover Image</label>
              <input type="file" name="cover_image" class="form-control" accept="image/*">
              <small class="text-muted">Optional. JPG, PNG, or WEBP (max 2MB)</small>
            </div>
            <div class="d-flex justify-content-between">
              <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
              </a>
              <button name="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Add Category
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    
    <div class="col-md-6">
      <div class="card shadow">
        <div class="card-header bg-white">
          <h3 class="mb-0 d-flex align-items-center">
            <i class="bi bi-tags me-2"></i> All Categories
          </h3>
        </div>
        <div class="card-body">
          <?php if ($cats->num_rows > 0): ?>
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Cover</th>
                    <th>Name</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($row = $cats->fetch_assoc()): ?>
                  <tr>
                    <td>
                      <?php if (!empty($row['cover_image'])): ?>
                        <img src="<?= htmlspecialchars($row['cover_image']) ?>" class="category-img" alt="<?= htmlspecialchars($row['name']) ?>">
                      <?php else: ?>
                        <div class="empty-img">
                          <i class="bi bi-image text-muted"></i>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td>
                      <div class="btn-group btn-group-sm">
                        <a href="edit_category.php?id=<?= $row['id'] ?>" class="btn btn-outline-warning">
                          <i class="bi bi-pencil"></i>
                        </a>
                        <a href="delete_category.php?id=<?= $row['id'] ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this category? All related items will be deleted too!')">
                          <i class="bi bi-trash"></i>
                        </a>
                      </div>
                    </td>
                  </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="text-center py-4">
              <i class="bi bi-tag text-muted display-4"></i>
              <p class="text-muted mt-3">No categories found</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>