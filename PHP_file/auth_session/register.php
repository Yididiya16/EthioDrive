<?php

session_start();

require_once "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../../register.html");
    exit;
}

/* ===============================
   GET FORM DATA
================================ */

$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

/* ===============================
   VALIDATION
================================ */

if (
    empty($full_name) ||
    empty($email) ||
    empty($password) ||
    empty($confirm)
) {
    header("Location: ../../register.html?error=empty");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../../register.html?error=email");
    exit;
}

if ($password !== $confirm) {
    header("Location: ../../register.html?error=password");
    exit;
}

if (strlen($password) < 6) {
    header("Location: ../../register.html?error=short");
    exit;
}

/* ===============================
   CHECK EXISTING EMAIL
================================ */

$stmt = $conn->prepare(
    "SELECT id FROM users WHERE email = ? LIMIT 1"
);

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {

    $stmt->close();
    $conn->close();

    header("Location: ../../register.html?error=exists");
    exit;
}

$stmt->close();

/* ===============================
   HASH PASSWORD
================================ */

$hashed_password = password_hash(
    $password,
    PASSWORD_DEFAULT
);

/* ===============================
   INSERT USER
================================ */

$stmt = $conn->prepare(
    "INSERT INTO users
    (full_name, email, phone, password, role)
    VALUES (?, ?, ?, ?, 'user')"
);

$stmt->bind_param(
    "ssss",
    $full_name,
    $email,
    $phone,
    $hashed_password
);

/* ===============================
   SUCCESS / FAILURE
================================ */

if ($stmt->execute()) {

    $stmt->close();
    $conn->close();

    header("Location: ../../register.html?success=1");
    exit;

} else {

    $stmt->close();
    $conn->close();

    header("Location: ../../register.html?error=server");
    exit;
}