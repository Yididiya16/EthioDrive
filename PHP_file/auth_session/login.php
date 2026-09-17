<?php
session_start();
require_once "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    /*header("Location: ../public/login.php");*/
    header("Location: ../../login.html");
    exit;
}

$email    = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

// Basic validation
if (empty($email) || empty($password)) {
   /* header("Location: ../public/login.php?error=empty"); */
   header("Location: ../../login.html");
    exit;
}

// Check user
$sql = "SELECT id, full_name, email, password, role FROM users WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    /* header("Location: ../public/login.php?error=invalid"); */
    header("Location: ../../login.html");
    exit;
}

$user = $result->fetch_assoc();

// Verify password
if (!password_verify($password, $user["password"])) {
   /* header("Location: ../public/login.php?error=invalid"); */
   header("Location: ../../login.html");
    exit;
}

// ✅ LOGIN SUCCESS
$_SESSION["user_id"]   = $user["id"];
$_SESSION["user_name"] = $user["full_name"];
$_SESSION["user_role"] = $user["role"];

// Redirect based on role
if ($user["role"] === "admin") {
    header("Location: ../admin.php");
} else {
    header("Location: ../dashboard.php");
}
exit;