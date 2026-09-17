<?php
session_start();
require_once __DIR__ . '/config/db.php';

/* 🔐 Protect page */
if (!isset($_SESSION['user_id'])) {
    header("Location: public/login.php");
    exit;
}

/* Handle POST updates */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $full_name = trim($_POST['full_name']);
        $phone = trim($_POST['phone']);
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("ssi", $full_name, $phone, $user_id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['user_name'] = $full_name; // Update session
        header("Location: profile.php?updated=profile");
        exit;
    } elseif (isset($_POST['change_password'])) {
        $current = $_POST['current_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];
        
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!password_verify($current, $row['password'])) {
            header("Location: profile.php?error=password");
            exit;
        }
        if ($new !== $confirm || strlen($new) < 6) {
            header("Location: profile.php?error=password");
            exit;
        }
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed, $user_id);
        $stmt->execute();
        header("Location: profile.php?updated=password");
        exit;
    }
}

/* ✅ Fetch user data */
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT full_name, email, phone, role, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    session_destroy();
    header("Location: public/login.php");
    exit;
}

$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <title>My Profile | EthioDrive</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Styles -->
   <!-- <link rel="stylesheet" href="css/style.css"> -->

   <link rel="stylesheet" href="../style.css">
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
                <a href="profile.php" class="active">Profile</a>
                <a href="auth/logout.php" class="btn btn-outline">Logout</a>
            </div>
        </nav>
    </div>
</header>

<!-- Profile Section -->
<section class="container mt-3">
    <div class="card profile-card text-center">

        <div class="profile-avatar">
            <i class="fas fa-user"></i>
        </div>

        <h2 class="mt-2"><?php echo htmlspecialchars($user['full_name']); ?></h2>
        <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
        <p class="text-muted"><?php echo htmlspecialchars($user['phone']); ?></p>

        <div class="profile-info mt-3">
            <p>
                <strong>Role:</strong>
                <span class="badge">
                    <?php echo ucfirst($user['role']); ?>
                </span>
            </p>

            <p>
                <strong>Member Since:</strong>
                <?php echo date("F d, Y", strtotime($user['created_at'])); ?>
            </p>
        </div>

        <div class="profile-actions mt-3">
            <button onclick="toggleEdit()" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Profile
            </button>
            <a href="dashboard.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

    </div>
</section>

<!-- Edit Profile Section -->
<section class="container mt-3" id="editSection" style="display:none;">
    <div class="card">
        <h2>Edit Profile</h2>
        <form method="POST">
            <input type="hidden" name="update_profile" value="1">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
            </div>
            <button type="submit" class="btn">Update Profile</button>
        </form>
    </div>

    <div class="card mt-2">
        <h2>Change Password</h2>
        <form method="POST">
            <input type="hidden" name="change_password" value="1">
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required>
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" required minlength="6">
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn">Change Password</button>
        </form>
    </div>
</section>

<script>
function toggleEdit() {
    const section = document.getElementById('editSection');
    section.style.display = section.style.display === 'none' ? 'block' : 'none';
}
</script>
<footer class="footer mt-3">
    <div class="container text-center">
        <p>&copy; <?php echo date("Y"); ?> EthioDrive. All rights reserved.</p>
    </div>
</footer>

</body>
</html>
