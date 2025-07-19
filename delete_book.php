<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';

$id = intval($_GET['id'] ?? 0);

// First delete all images associated with the book
$images = $conn->query("SELECT filename FROM book_images WHERE book_id = $id");
while ($img = $images->fetch_assoc()) {
    $filePath = 'uploads/' . $img['filename'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

// Then delete the book
$conn->query("DELETE FROM books WHERE id = $id");

// Delete associated image records from the DB
$conn->query("DELETE FROM book_images WHERE book_id = $id");

$_SESSION['success_message'] = "Book deleted successfully!";
header("Location: index.php");
exit;
?>
