<?php
require_once "config/db.php";

// Fetch vehicles
$result = $conn->query("SELECT * FROM vehicles WHERE status = 'available' ORDER BY id DESC");
$vehicles = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicles | EthioDrive</title>
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
                <a href="index.html">EthioDrive</a>
            </div>
            <div class="nav-links">
                <a href="index.html">Home</a>
                <a href="vehicles.php" class="active">Vehicles</a>
                <a href="rent.php">Rent</a>
                <a href="buy.php">Buy</a>
                <a href="about.html">About Us</a>
                <a href="auth/login.php">Login</a>
                <a href="auth/register.php">Register</a>
                <button class="theme-toggle" id="themeToggle">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </nav>
    </div>
</header>

<!-- Hero -->
<section class="hero">
    <div class="container">
        <h1>Our Vehicle Collection</h1>
        <p>Reliable vehicles ready for Ethiopian roads</p>
    </div>
</section>

<!-- Vehicles -->
<section class="container mt-3">
    <h2>Available Vehicles</h2>

    <div class="grid grid-4 mt-2">
        <?php foreach ($vehicles as $v): ?>
            <div class="card vehicle-card">
                <div class="vehicle-img-container">
                    <img src="uploads/<?php echo htmlspecialchars($v['image'] ?: 'default.jpg'); ?>" class="vehicle-img" alt="<?php echo htmlspecialchars($v['title']); ?>">
                    <span class="vehicle-badge"><?php echo strtoupper($v['price_type']); ?></span>
                </div>
                <h3><?php echo htmlspecialchars($v['title']); ?></h3>
                <p><?php echo htmlspecialchars($v['brand'] . ' ' . $v['model']); ?> • <?php echo $v['year']; ?></p>

                <div class="flex-between mt-1">
                    <div class="vehicle-price"><?php echo number_format($v['price']); ?> ETB<?php echo $v['price_type'] === 'rent' ? '/day' : ''; ?></div>
                    <span class="status-badge status-active">Available</span>
                </div>

                <div class="flex gap-1 mt-2">
                    <button class="btn btn-sm btn-outline" onclick="viewVehicle(<?php echo $v['id']; ?>)">
                        <i class="fas fa-eye"></i> Details
                    </button>
                    <?php if ($v['price_type'] === 'rent'): ?>
                        <a href="rent.php?vehicle_id=<?php echo $v['id']; ?>" class="btn btn-sm">
                            <i class="fas fa-car"></i> Rent
                        </a>
                    <?php else: ?>
                        <a href="buy.php?vehicle_id=<?php echo $v['id']; ?>" class="btn btn-sm">
                            <i class="fas fa-shopping-cart"></i> Buy
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Modal -->
<div class="modal" id="vehicleModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle"></h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <img id="modalImage" class="vehicle-img">
            <p id="modalDesc" class="mt-1"></p>
            <div id="modalPrice" class="vehicle-price"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal()">Close</button>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="footer mt-3">
    <div class="container">
        <div class="footer-bottom">
            <p>&copy; 2025 EthioDrive. All rights reserved.</p>
        </div>
    </div>
</footer>

<script>
function viewVehicle(id) {
    // Fetch vehicle details via AJAX or use data
    // For now, placeholder
    alert('Vehicle details for ID: ' + id);
}

function closeModal() {
    document.getElementById('vehicleModal').classList.remove('show');
}
</script>

</body>
</html>