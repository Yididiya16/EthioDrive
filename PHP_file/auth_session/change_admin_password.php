<?php
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'];
    if (empty($new_password)) {
        echo "Password cannot be empty.";
        exit;
    }

    $hashed = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = 'admin@ethiodrive.com'");
    $stmt->bind_param("s", $hashed);
    if ($stmt->execute()) {
        echo "Admin password updated successfully.";
    } else {
        echo "Error updating password: " . $conn->error;
    }
    $stmt->close();
    $conn->close();
} else {
?>
<form method="POST">
    <label>New Password:</label>
    <input type="password" name="new_password" required>
    <button type="submit">Update Password</button>
</form>
<?php
}
?>