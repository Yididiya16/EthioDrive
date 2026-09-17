<?php
session_start();
require_once "config/db.php";

// 🔐 Check login
if (!isset($_SESSION['user_id'])) {
   /* header("Location: public/login.php");  */
   header("Location: ../login.html");
    exit;
}

// 🛑 Only POST allowed
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
   /* header("Location: public/rent.html"); */
    header("Location: ../rent.html");

    exit;
}

// 🧼 Collect & sanitize input
$user_id    = $_SESSION['user_id'];
$vehicle_id = intval($_POST['vehicle_id']);
$start_date = $_POST['start_date'];
$end_date   = $_POST['end_date'];

// Basic validation
if (empty($vehicle_id) || empty($start_date) || empty($end_date)) {
    die("All fields are required.");
}

if ($start_date > $end_date) {
    die("Invalid rental date range.");
}

// 💰 Get vehicle price per day
$stmt = $conn->prepare("SELECT price FROM vehicles WHERE id = ? AND price_type = 'rent'");
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Vehicle not found or not for rent.");
}

$vehicle = $result->fetch_assoc();
$price_per_day = $vehicle['price'];

// 📅 Calculate rental days
$days = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24) + 1;
$total_price = $days * $price_per_day;

// 📝 Insert rental record
$stmt = $conn->prepare("
    INSERT INTO bookings (user_id, vehicle_id, start_date, end_date, total_price, status)
    VALUES (?, ?, ?, ?, ?, 'pending')
");
$stmt->bind_param(
    "iissd",
    $user_id,
    $vehicle_id,
    $start_date,
    $end_date,
    $total_price
);

if ($stmt->execute()) {
    // Insert notification for admin
    $user_name = $_SESSION['user_name'] ?? 'User';
    $vehicle_title = $conn->query("SELECT title FROM vehicles WHERE id=$vehicle_id")->fetch_assoc()['title'];
    $message = "New rental request from $user_name for $vehicle_title.";
    $conn->query("INSERT INTO notifications (message) VALUES ('$message')");
    
    $_SESSION['message'] = "Booking submitted successfully. Please wait for admin approval.";
    header("Location: my-bookings.php");
    exit;
} else {
    echo "Rental failed. Please try again.";
}