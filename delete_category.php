<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';

$id = intval($_GET['id'] ?? 0);

// First check if category has books
$has_books = $conn->query("SELECT COUNT(*) as count FROM books WHERE category_id = $id")->fetch_assoc()['count'];

if ($has_books > 0) {
    $_SESSION['error_message'] = "Cannot delete category with existing books!";
    header("Location: add_category.php");
    exit;
}

// Delete the category
$conn->query("DELETE FROM categories WHERE id = $id");

$_SESSION['success_message'] = "Category deleted successfully!";
header("Location: add_category.php");
exit;
?>
