<?php
session_start();
require_once __DIR__ . '/config/db.php';

/* 🔐 Protect page */
if (!isset($_SESSION['user_id'])) {
    header("Location: public/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* ✅ Fetch user bookings */
$sql = "
    SELECT 
        b.id,
        b.start_date,
        b.end_date,
        b.status,
        v.brand,
        v.model,
        v.price
    FROM bookings b
    JOIN vehicles v ON b.vehicle_id = v.id
    WHERE b.user_id = ?
    ORDER BY b.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <title>My Bookings | EthioDrive</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- Header -->
<header class="header">
    <div class="container">
        <nav class="navbar">
            <div class="logo">
                <i class="fas fa-car"></i>
                <a href="dashboard.php">EthioDrive</a>
            </div>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="my-bookings.php" class="active">My Bookings</a>
                <a href="profile.php">Profile</a>
                <a href="auth/logout.php" class="btn btn-outline">Logout</a>
            </div>
        </nav>
    </div>
</header>

<!-- Message -->
<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success">
        <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
    </div>
<?php endif; ?>

<!-- Bookings -->
<section class="container mt-3">
    <div class="card">
        <h2><i class="fas fa-calendar-check"></i> My Bookings</h2>

        <?php if ($result->num_rows === 0): ?>
            <p class="mt-2 text-muted">You have no bookings yet.</p>
        <?php else: ?>
            <div class="table-responsive mt-2">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Vehicle</th>
                            <th>Rental Period</th>
                            <th>Price / Day</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($row['brand'] . ' ' . $row['model']); ?>
                                </td>
                                <td>
                                    <?php echo date("M d, Y", strtotime($row['start_date'])); ?>
                                    →
                                    <?php echo date("M d, Y", strtotime($row['end_date'])); ?>
                                </td>
                                <td>
                                    <?php echo number_format($row['price']); ?> ETB
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $row['status']; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="mt-2">
            <a href="dashboard.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="footer mt-3">
    <div class="container text-center">
        <p>&copy; <?php echo date("Y"); ?> EthioDrive</p>
    </div>
</footer>

</body>
</html>
