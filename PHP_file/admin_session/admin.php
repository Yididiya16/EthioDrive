<?php
/**************************************************
 * ADMIN PANEL - EthioDrive
 * Single file admin system
 **************************************************/

session_start();
require_once __DIR__ . '/config/db.php';

/* ===============================
   1️⃣ SECURITY: ADMIN ONLY
================================ */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: public/login.php");
    exit;
}

/* ===============================
   2️⃣ HANDLE ACTIONS (POST / GET)
================================ */
$action = $_GET['action'] ?? null;

/* ---- VEHICLE ACTIONS ---- */
if ($action === 'add_vehicle' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $brand = trim($_POST['brand']);
    $model = trim($_POST['model']);
    $year = (int)$_POST['year'];
    $price = (float)$_POST['price'];
    $price_type = $_POST['price_type'];
    $description = trim($_POST['description']);
    
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        /*$upload_dir = __DIR__ . '/uploads/';*/
        $upload_dir = __DIR__ . '/../uploads/';

        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $filename = uniqid() . '_' . basename($_FILES['image']['name']);
        $target = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $image = $filename;
        }
    }
    
    $stmt = $conn->prepare("
        INSERT INTO vehicles (title, brand, model, year, price, price_type, description, image, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'available')
    ");
    $stmt->bind_param("sssidsss", $title, $brand, $model, $year, $price, $price_type, $description, $image);
    $stmt->execute();
    header("Location: admin.php?added=vehicle");
    exit;
}

if ($action === 'delete_vehicle') {
    $id = (int)$_GET['id'];
    $conn->query("DELETE FROM vehicles WHERE id=$id");
}

if ($action === 'toggle_vehicle') {
    $id = (int)$_GET['id'];
    $conn->query("
        UPDATE vehicles
        SET status = CASE 
            WHEN status='available' THEN 'unavailable' 
            WHEN status='unavailable' THEN 'available' 
            ELSE status 
        END
        WHERE id=$id
    ");
}

/* ---- USER ACTIONS ---- */
if ($action === 'add_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($full_name) || empty($email) || empty($password)) {
        die("All fields required");
    }
    
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $full_name, $email, $hashed);
    $stmt->execute();
    header("Location: admin.php?added=user");
    exit;
}

/* ---- BOOKING ACTIONS ---- */
if ($action === 'approve_booking') {
    $id = (int)$_GET['id'];
    $conn->query("UPDATE bookings SET status='approved' WHERE id=$id");
    // Update vehicle status to rented
    $booking = $conn->query("SELECT vehicle_id FROM bookings WHERE id=$id")->fetch_assoc();
    if ($booking) {
        $conn->query("UPDATE vehicles SET status='rented' WHERE id={$booking['vehicle_id']}");
    }
}

if ($action === 'reject_booking') {
    $id = (int)$_GET['id'];
    $conn->query("UPDATE bookings SET status='rejected' WHERE id=$id");
}

if ($action === 'mark_read') {
    $id = (int)$_GET['id'];
    $conn->query("UPDATE notifications SET is_read=1 WHERE id=$id");
}

/* ===============================
   3️⃣ FETCH DATA FOR DASHBOARD
================================ */
$stats = [
    'users'    => $conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'],
    'vehicles' => $conn->query("SELECT COUNT(*) c FROM vehicles")->fetch_assoc()['c'],
    'bookings' => $conn->query("SELECT COUNT(*) c FROM bookings")->fetch_assoc()['c'],
];

$vehicles = $conn->query("SELECT * FROM vehicles ORDER BY id DESC");
$notifications = $conn->query("SELECT * FROM notifications WHERE user_id IS NULL ORDER BY created_at DESC");
$bookings = $conn->query("
    SELECT b.*, u.full_name, v.title
    FROM bookings b
    JOIN users u ON b.user_id=u.id
    JOIN vehicles v ON b.vehicle_id=v.id
    ORDER BY b.id DESC
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | EthioDrive</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<h1>Admin Dashboard</h1>
<p>Welcome Admin 👑</p>

<hr>

<!-- ===============================
     4️⃣ DASHBOARD STATS
================================= -->
<div class="grid grid-4">
    <div class="card">Users: <?= $stats['users'] ?></div>
    <div class="card">Vehicles: <?= $stats['vehicles'] ?></div>
    <div class="card">Bookings: <?= $stats['bookings'] ?></div>
    <div class="card">Rentals: <?= $stats['rentals'] ?></div>
</div>

<hr>

<!-- ===============================
     NOTIFICATIONS PANEL
================================= -->
<h2>Notifications</h2>
<?php if ($notifications->num_rows > 0): ?>
    <ul>
        <?php while ($n = $notifications->fetch_assoc()): ?>
            <li>
                <strong><?= htmlspecialchars($n['message']) ?></strong>
                <small>(<?= $n['created_at'] ?>)</small>
                <?php if (!$n['is_read']): ?>
                    <a href="?action=mark_read&id=<?= $n['id'] ?>">Mark as Read</a>
                <?php endif; ?>
            </li>
        <?php endwhile; ?>
    </ul>
<?php else: ?>
    <p>No new notifications.</p>
<?php endif; ?>

<hr>
<h2>Add Vehicle</h2>
<form method="POST" action="?action=add_vehicle">
    <input type="text" name="title" placeholder="Vehicle title" required>
    <select name="type">
        <option value="rent">Rent</option>
        <option value="buy">Buy</option>
    </select>
    <input type="number" name="price" step="0.01" placeholder="Price" required>
    <button type="submit">Add Vehicle</button>
</form>

<hr>

<!-- ===============================
     6️⃣ VEHICLE LIST (CRUD)
================================= -->
<h2>Vehicles</h2>
<table border="1" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>Title</th>
        <th>Type</th>
        <th>Price</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
    <?php while ($v = $vehicles->fetch_assoc()): ?>
    <tr>
        <td><?= $v['id'] ?></td>
        <td><?= htmlspecialchars($v['title']) ?></td>
        <td><?= $v['type'] ?></td>
        <td><?= $v['price'] ?></td>
        <td><?= $v['status'] ?></td>
        <td>
            <a href="?action=toggle_vehicle&id=<?= $v['id'] ?>">Toggle</a> |
            <a href="?action=delete_vehicle&id=<?= $v['id'] ?>"
               onclick="return confirm('Delete vehicle?')">Delete</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<hr>

<!-- ===============================
     7️⃣ BOOKINGS MANAGEMENT
================================= -->
<h2>Bookings</h2>
<table border="1" cellpadding="5">
    <tr>
        <th>User</th>
        <th>Vehicle</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php while ($b = $bookings->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($b['name']) ?></td>
        <td><?= htmlspecialchars($b['title']) ?></td>
        <td><?= $b['status'] ?></td>
        <td>
            <?php if ($b['status'] === 'pending'): ?>
                <a href="?action=approve_booking&id=<?= $b['id'] ?>">Approve</a> |
                <a href="?action=reject_booking&id=<?= $b['id'] ?>">Reject</a>
            <?php else: ?>
                ---
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<hr>

<a href="auth/logout.php">Logout</a>

</body>
</html>
