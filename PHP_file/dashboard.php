<?php
session_start();
require_once "config/db.php";

// 🔐 Block guests
if (!isset($_SESSION['user_id'])) {
    header("Location: public/login.php");
    exit;
}

$user_id  = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// 📊 Fetch stats

// Active rentals
$stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM bookings 
    WHERE user_id = ? AND status = 'approved'
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($activeRentals);
$stmt->fetch();
$stmt->close();

// Total bookings
$stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM bookings 
    WHERE user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($totalBookings);
$stmt->fetch();
$stmt->close();

// Purchases
$stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM purchases 
    WHERE user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($purchases);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | EthioDrive</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<header class="header">
    <div class="container">
        <nav class="navbar">
            <div class="logo">
                <i class="fas fa-car"></i>
                <span>EthioDrive</span>
            </div>
            <div class="nav-links">
                <a href="public/index.html">Home</a>
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="profile.php">Profile</a>
                <a href="auth/logout.php">Logout</a>
            </div>
        </nav>
    </div>
</header>

<section class="container mt-3">
    <h1>Welcome, <?= htmlspecialchars($user_name) ?> 👋</h1>
    <p>Your account overview</p>

    <div class="stats-grid mt-2">
        <div class="card stat-card">
            <i class="fas fa-car"></i>
            <div class="stat-number"><?= $activeRentals ?></div>
            <div class="stat-label">Active Rentals</div>
        </div>

        <div class="card stat-card">
            <i class="fas fa-calendar"></i>
            <div class="stat-number"><?= $totalBookings ?></div>
            <div class="stat-label">Bookings</div>
        </div>

        <div class="card stat-card">
            <i class="fas fa-shopping-cart"></i>
            <div class="stat-number"><?= $purchases ?></div>
            <div class="stat-label">Purchases</div>
        </div>
    </div>
</section>

<footer class="footer mt-3">
    <div class="container">
        <p>&copy; <?= date("Y") ?> EthioDrive</p>
    </div>
</footer>

</body>
</html>
