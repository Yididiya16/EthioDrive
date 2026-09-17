<?php
session_start();
require_once "config/db.php";

/* 🔐 CHECK LOGIN */
if (!isset($_SESSION['user_id'])) {
    header("Location: public/login.php");
    exit;
}

/* 🛑 ONLY ACCEPT POST */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: public/booking.html");
    exit;
}

/* 📥 COLLECT DATA */
$user_id        = $_SESSION['user_id'];
$vehicle_id     = $_POST['vehicle_id'] ?? null;
$booking_type   = $_POST['booking_type'] ?? null;
$start_date     = $_POST['start_date'] ?? null;
$end_date       = $_POST['end_date'] ?? null;

/* ❌ BASIC VALIDATION */
if (
    empty($vehicle_id) ||
    empty($booking_type) ||
    empty($start_date) ||
    empty($end_date)
) {
    die("❌ Required fields missing");
}

/* 🚗 LICENSE REQUIRED FOR TEST DRIVE */
if ($booking_type === "test_drive") {
    // Note: License validation removed as not stored in DB
}

/* 🧠 INSERT INTO DATABASE */
$sql = "INSERT INTO bookings 
        (user_id, vehicle_id, booking_type, start_date, end_date) 
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "iisss",
    $user_id,
    $vehicle_id,
    $booking_type,
    $start_date,
    $end_date
);

/* 🚀 EXECUTE */
if ($stmt->execute()) {
    header("Location: dashboard.php?booking=success");
    exit;
} else {
    die("❌ Booking failed: " . $stmt->error);
}
