<?php
// ===============================
// Database Configuration
// ===============================

$host = "localhost";
$user = "root";
$pass = "";
$db   = "nawa54";

// ===============================
// Create Connection
// ===============================

$conn = mysqli_connect($host, $user, $pass, $db);

// ===============================
// Check Connection
// ===============================

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Optional: Set charset (VERY GOOD PRACTICE)
mysqli_set_charset($conn, "utf8mb4");
