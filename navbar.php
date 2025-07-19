<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? $pageTitle : ' Admin' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/style.css">
  <style>
    /* Navbar-specific styles (unchanged in dark mode) */
    .navbar {
      background: linear-gradient(135deg, #6c5ce7, #5a4bd1);
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    .navbar-brand, .nav-link {
      color: white !important;
      transition: all 0.3s ease;
    }
    
    .nav-link:hover {
      opacity: 0.8;
    }
    
    /* Dark mode toggle styles */
    #darkModeToggle {
      cursor: pointer;
      padding: 0.5rem;
      border-radius: 50%;
      transition: all 0.3s ease;
    }
    
    #darkModeToggle:hover {
      background-color: rgba(255, 255, 255, 0.1);
    }
    
    #darkModeIcon {
      font-size: 1.2rem;
      vertical-align: middle;
      transition: transform 0.3s ease;
    }
    
    .dark-mode #darkModeIcon {
      transform: rotate(25deg);
    }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <i class="bi bi-house-fill me-2"></i>
      <span>libray Admin</span>
    </a>
    
    <!-- Dark Mode Toggle -->
    <div id="darkModeToggle" class="d-flex align-items-center me-3">
      <i class="bi bi-moon-fill text-white" id="darkModeIcon"></i>
    </div>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>" href="index.php">
            <i class="bi bi-grid me-1"></i> All Items
          </a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= in_array(basename($_SERVER['PHP_SELF']), ['add_item.php', 'edit_item.php']) ? 'active' : '' ?>" href="#" id="itemsDropdown" role="button" data-bs-toggle="dropdown">
            <i class="bi bi-box-seam me-1"></i> Items
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="add_item.php"><i class="bi bi-plus-circle me-2"></i>Add New</a></li>
          </ul>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= in_array(basename($_SERVER['PHP_SELF']), ['add_category.php', 'edit_category.php']) ? 'active' : '' ?>" href="#" id="categoriesDropdown" role="button" data-bs-toggle="dropdown">
            <i class="bi bi-tags me-1"></i> Categories
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="add_category.php"><i class="bi bi-plus-circle me-2"></i>Add New</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="logout.php">
            <i class="bi bi-box-arrow-right me-1"></i> Logout
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Dark Mode Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const darkModeToggle = document.getElementById('darkModeToggle');
  const darkModeIcon = document.getElementById('darkModeIcon');
  
  // Initialize from localStorage
  if (localStorage.getItem('darkMode') === 'enabled') {
    document.body.classList.add('dark-mode');
    darkModeIcon.classList.remove('bi-moon-fill');
    darkModeIcon.classList.add('bi-sun-fill');
  }
  
  // Toggle dark mode
  darkModeToggle.addEventListener('click', function() {
    const isDarkMode = document.body.classList.toggle('dark-mode');
    localStorage.setItem('darkMode', isDarkMode ? 'enabled' : 'disabled');
    
    // Change icon
    if (isDarkMode) {
      darkModeIcon.classList.remove('bi-moon-fill');
      darkModeIcon.classList.add('bi-sun-fill');
    } else {
      darkModeIcon.classList.remove('bi-sun-fill');
      darkModeIcon.classList.add('bi-moon-fill');
    }
  });
});
</script>