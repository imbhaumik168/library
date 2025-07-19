<?php include 'db.php'; ?>
<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
$category = $conn->query("SELECT * FROM categories WHERE id = $id")->fetch_assoc();

$error = '';
$success = '';

if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $image = $_FILES['cover_image'] ?? null;
    $remove_image = isset($_POST['remove_image']);
    
    if ($name !== "") {
        // Handle image upload/removal
        $imagePath = $category['cover_image'];
        
        if ($remove_image && $imagePath) {
            // Remove existing image
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            $imagePath = '';
        } elseif ($image && $image['error'] === UPLOAD_ERR_OK) {
            // Upload new image
            $uploadDir = 'uploads/categories/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $fileType = mime_content_type($image['tmp_name']);
            
            if (!in_array($fileType, $allowedTypes)) {
                $error = "Only JPG, PNG, and WEBP images are allowed.";
            } else {
                // Delete old image if exists
                if ($imagePath && file_exists($imagePath)) {
                    unlink($imagePath);
                }
                
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
            $stmt = $conn->prepare("UPDATE categories SET name = ?, cover_image = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $imagePath, $id);
            
            if ($stmt->execute())) {
                $_SESSION['success_message'] = "Category updated successfully!";
                header("Location: add_category.php");
                exit;
            } else {
                $error = "Error updating category: " . $conn->error;
            }
            $stmt->close();
        }
    } else {
        $error = "Category name cannot be empty.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Category</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/style.css">
  <style>
    .category-img-preview {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 4px;
        margin-bottom: 10px;
    }
    .empty-img-preview {
        width: 150px;
        height: 150px;
        background: #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        margin-bottom: 10px;
    }
    [data-bs-theme="dark"] .empty-img-preview {
        background: #2d3436;
    }
    .image-controls {
        margin-bottom: 20px;
    }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container my-5 animate-fade">
  <div class="card shadow">
    <div class="card-header bg-white">
      <h3 class="mb-0 d-flex align-items-center">
        <i class="bi bi-pencil me-2"></i> Edit Category
      </h3>
    </div>
    <div class="card-body">
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
      <?php endif; ?>
      
      <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
          <label class="form-label">Category Name *</label>
          <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($category['name']) ?>" required>
        </div>
        
        <div class="mb-3 image-controls">
          <label class="form-label">Cover Image</label>
          
          <?php if (!empty($category['cover_image'])): ?>
            <div>
              <img src="<?= htmlspecialchars($category['cover_image']) ?>" class="category-img-preview" id="imagePreview">
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="remove_image" id="removeImage">
                <label class="form-check-label" for="removeImage">
                  Remove current image
                </label>
              </div>
            </div>
          <?php else: ?>
            <div class="empty-img-preview" id="imagePreview">
              <i class="bi bi-image text-muted"></i>
            </div>
          <?php endif; ?>
          
          <input type="file" name="cover_image" class="form-control mt-2" id="imageUpload" accept="image/*">
          <small class="text-muted">Leave blank to keep current image</small>
        </div>
        
        <div class="d-flex justify-content-between">
          <a href="add_category.php" class="btn btn-outline-secondary">
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

<script>
// Preview image before upload
document.getElementById('imageUpload').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            let preview = document.getElementById('imagePreview');
            if (preview.classList.contains('empty-img-preview')) {
                // Replace placeholder with actual image
                preview.outerHTML = `<img src="${e.target.result}" class="category-img-preview" id="imagePreview">`;
            } else {
                // Update existing image
                preview.src = e.target.result;
            }
            // Uncheck remove checkbox if new image selected
            const removeCheckbox = document.getElementById('removeImage');
            if (removeCheckbox) removeCheckbox.checked = false;
        }
        reader.readAsDataURL(file);
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>