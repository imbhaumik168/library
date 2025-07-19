<?php
session_start();
require_once 'db.php'; // adjust path to your DB file

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $identifier = trim($_POST["identifier"]); // email or username
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($admin = $result->fetch_assoc()) {
        // Plain text comparison (⚠️ insecure)
        if ($password === $admin["password"]) {
            $_SESSION["admin"] = true;
            $_SESSION["admin_id"] = $admin["id"];
            $_SESSION["admin_username"] = $admin["username"];
            header("Location: index.php");
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login | Furniture Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <style>
    :root {
      --primary-color: #6c5ce7;
      --primary-dark: #5a4bd1;
      --secondary-color: #a29bfe;
      --light-bg: #f8f9fa;
      --dark-bg: #1a1a2e;
      --light-text: #212529;
      --dark-text: #e9ecef;
      --light-card: #ffffff;
      --dark-card: #16213e;
    }

    body {
      background-color: var(--light-bg);
      color: var(--light-text);
      transition: all 0.3s ease;
    }

    body.dark-mode {
      background-color: var(--dark-bg);
      color: var(--dark-text);
    }

    .login-container {
      max-width: 400px;
      margin: 100px auto;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      background-color: var(--light-card);
      transition: all 0.3s ease;
    }

    body.dark-mode .login-container {
      background-color: var(--dark-card);
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }

    .btn-primary {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
    }

    .btn-primary:hover {
      background-color: var(--primary-dark);
      border-color: var(--primary-dark);
    }

    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.25rem rgba(108, 92, 231, 0.25);
    }

    .error-message {
      color: #dc3545;
      font-weight: 500;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="login-container">
      <div class="text-center mb-4">
        <i class="bi bi-shield-lock" style="font-size: 3rem; color: var(--primary-color);"></i>
        <h2 class="mt-3">Admin Login</h2>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger text-center error-message">
          <?= $error ?>
        </div>
            <?php endif; ?>
      <form method="post">
        <div class="mb-3">
          <label for="identifier" class="form-label">Username or Email</label>
          <input type="text" class="form-control" id="identifier" name="identifier" placeholder="Enter username or email" required>
        </div>
        <div class="mb-4">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
        </div>
        <div class="d-grid">
          <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-box-arrow-in-right me-2"></i> Login
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Dark Mode Script (matches navbar functionality) -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Check for existing dark mode preference
      if (localStorage.getItem('darkMode') === 'enabled') {
        document.body.classList.add('dark-mode');
      }
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>