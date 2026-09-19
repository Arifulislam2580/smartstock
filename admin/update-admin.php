<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin-login.php");
    exit;
}

require_once('includes/db.php');
//include("includes/navbar.php");

$id = intval($_GET['id'] ?? 0);
if (!$id) { die("Admin not found"); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    $sql = "UPDATE tbl_admin SET full_name=?, username=?, email=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $full_name, $username, $email, $id);

    if ($stmt->execute()) {
        header("Location: manage-admin.php");
        exit;
    } else {
        echo "Error: " . htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8');
    }
}

$stmt = $conn->prepare("SELECT * FROM tbl_admin WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

if (!$admin) {
    die("Admin not found.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Update Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <h2>Update Admin</h2>
    <p><a href="manage-admin.php">← Back to Admin List</a></p>
    <form method="post">
        <label>Full Name:</label>
        <input type="text" name="full_name" value="<?php echo htmlspecialchars($admin['full_name'], ENT_QUOTES, 'UTF-8'); ?>" required><br>
        <label>Username:</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($admin['username'], ENT_QUOTES, 'UTF-8'); ?>" required><br>
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($admin['email'], ENT_QUOTES, 'UTF-8'); ?>" required><br>
        <button type="submit">Update Admin</button>
    </form>
</body>
</html>

