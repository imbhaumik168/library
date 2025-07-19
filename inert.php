<?php
$mysqli = new mysqli("localhost", "root", "", "dev");

$username = "dev";
$email = "devan123@gmail.com";
$password = password_hash("dev143", PASSWORD_DEFAULT); // real secure hash

$stmt = $mysqli->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $password);
$stmt->execute();

echo "Admin created.";
