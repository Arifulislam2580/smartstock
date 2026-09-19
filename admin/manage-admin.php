<?php
session_start();
require_once('includes/db.php');

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin-login.php");
    exit;
}

$adminResult = $conn->query("SELECT * FROM tbl_admin");
$messagesResult = $conn->query("SELECT * FROM contact_messages ORDER BY submitted_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Admins | SmartStock</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            color: #333;
            background: linear-gradient(270deg, #e0eafc, #cfdef3, #e0eafc);
            background-size: 600% 600%;
            animation: gradientFlow 12s ease infinite;
        }

        @keyframes gradientFlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 36px;
            color: #2c3e50;
        }

        .add-admin {
            display: inline-block;
            margin-bottom: 20px;
            padding: 12px 26px;
            background: #3498db;
            color: #fff;
            border-radius: 8px;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .add-admin:hover {
            background: #2980b9;
            transform: scale(1.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 50px;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
        }

        th, td {
            padding: 16px;
            text-align: center;
            border-bottom: 1px solid #f0f0f0;
        }

        th {
            background: #2c3e50;
            color: white;
            font-size: 16px;
        }

        tr:hover {
            background-color: #f4faff;
            transition: 0.3s;
        }

        .action-links a {
            color: #3498db;
            margin: 0 6px;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .action-links a:hover {
            color: #e74c3c;
        }

        .messages-box {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            animation: slideIn 0.9s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .messages-box h3 {
            font-size: 26px;
            margin-bottom: 20px;
            color: #34495e;
        }

        .message-item {
            background: #ffffff;
            border-left: 5px solid #3498db;
            padding: 18px 24px;
            margin-bottom: 18px;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            transition: transform 0.2s ease;
        }

        .message-item:hover {
            transform: translateY(-3px);
        }

        .message-item p {
            margin: 6px 0;
            line-height: 1.5;
        }

        .message-item small {
            color: #999;
        }

        @media (max-width: 768px) {
            h2 {
                font-size: 26px;
            }

            table, th, td {
                font-size: 14px;
            }

            .add-admin {
                font-size: 14px;
                padding: 10px 20px;
            }

            .messages-box h3 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Admin Management</h2>

        <div style="display: flex; flex-wrap: wrap; gap: 12px; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <a class="add-admin" href="add-admin.php">+ Add Admin</a>
                <a class="add-admin" href="dashboard.php" style="background: #2ecc71;">← Dashboard</a>
            </div>
            <div>
                <a class="add-admin" href="admin-login.php?logout=1" style="background: #e74c3c;">Logout</a>
            </div>
        </div>

        <!-- Admins Table -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $adminResult->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td class="action-links">
                        <a href="update-admin.php?id=<?php echo $row['id']; ?>">Edit</a> | 
                        <a href="delete-admin.php?id=<?php echo $row['id']; ?>" onclick="return confirmDelete()">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Messages Box -->
        <div class="messages-box">
            <h3>📥 User Messages</h3>
            <?php if ($messagesResult->num_rows > 0): ?>
                <?php while($msg = $messagesResult->fetch_assoc()): ?>
                    <div class="message-item">
                        <p><strong>From:</strong> <?php echo htmlspecialchars($msg['name']); ?> (<?php echo htmlspecialchars($msg['email']); ?>)</p>
                        <p><strong>Message:</strong> <?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                        <small>📅 <?php echo $msg['submitted_at']; ?></small>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No messages found.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to delete this admin? This action cannot be undone.");
        }
    </script>

</body>
</html>

