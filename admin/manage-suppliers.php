<?php
session_start();
require_once('includes/db.php');

$conn->query("CREATE TABLE IF NOT EXISTS tbl_supplier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(150),
    phone VARCHAR(30),
    email VARCHAR(150),
    address TEXT,
    active VARCHAR(10) DEFAULT 'Yes',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin-login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $active = trim($_POST['active'] ?? 'Yes');

    if ($name !== '') {
        $stmt = $conn->prepare('INSERT INTO tbl_supplier (name, contact_person, phone, email, address, active) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssss', $name, $contact_person, $phone, $email, $address, $active);
        $stmt->execute();
    }
}

$result = $conn->query('SELECT * FROM tbl_supplier ORDER BY created_at DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Suppliers | SmartStock</title>
    <style>
        body { font-family: Arial, sans-serif; margin:0; background:#f8fafc; color:#0f172a; }
        .page { max-width: 1100px; margin: 30px auto; padding: 20px; }
        .card { background:white; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 8px 24px rgba(0,0,0,0.06); }
        form { display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; }
        input, select, textarea, button { width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1; }
        button { background:#2563eb; color:white; border:none; cursor:pointer; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:10px; border-bottom:1px solid #e2e8f0; text-align:left; }
        th { background:#f1f5f9; }
        .back { display:inline-block; margin-bottom:15px; color:#2563eb; text-decoration:none; }
    </style>
</head>
<body>
    <div class="page">
        <a class="back" href="dashboard.php">← Back to Dashboard</a>
        <h2>Manage Suppliers</h2>
        <div class="card">
            <h3>Add New Supplier</h3>
            <form method="post">
                <input type="text" name="name" placeholder="Supplier Name" required />
                <input type="text" name="contact_person" placeholder="Contact Person" />
                <input type="text" name="phone" placeholder="Phone" />
                <input type="email" name="email" placeholder="Email" />
                <textarea name="address" placeholder="Address"></textarea>
                <select name="active">
                    <option value="Yes">Active</option>
                    <option value="No">Inactive</option>
                </select>
                <button type="submit">Save Supplier</button>
            </form>
        </div>

        <div class="card">
            <h3>Supplier List</h3>
            <table>
                <thead>
                    <tr><th>Name</th><th>Contact</th><th>Phone</th><th>Email</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['contact_person'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['phone'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['email'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['active']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5">No suppliers found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

