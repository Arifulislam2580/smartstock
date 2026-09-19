<?php
session_start();
require_once('includes/db.php');

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin-login.php");
    exit;
}

// Handle status update
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = trim($_POST['status']);
    $allowedStatuses = ['Pending', 'On the way', 'Delivered', 'Cancelled'];
    if (!in_array($status, $allowedStatuses, true)) {
        $status = 'Pending';
    }
    $stmt = $conn->prepare("UPDATE tbl_order SET status=? WHERE order_id=?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();
}

// Fetch all orders
$sql = "SELECT o.order_id, c.customer_name, c.customer_email, p.title, o.quantity, o.total, o.status, o.order_date
        FROM tbl_order o
        JOIN customer_registration c ON o.customer_id = c.customer_id
        JOIN tbl_product p ON o.product_id = p.product_id
        ORDER BY o.order_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders | SmartStock Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        /* ==== Reset and Base ==== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(-45deg, #f8f9fa, #e3f2fd, #e1f5fe, #fff3e0);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            color: #333;
            padding-bottom: 40px;
        }

        @keyframes gradientBG {
            0% {background-position: 0% 50%;}
            50% {background-position: 100% 50%;}
            100% {background-position: 0% 50%;}
        }

        h2 {
            text-align: center;
            margin: 40px 0 10px;
            font-size: 32px;
            color: #2c3e50;
            animation: fadeInDown 1s ease;
        }

        p a {
            text-align: center;
            display: block;
            color: #e74c3c;
            text-decoration: none;
            margin: 15px auto;
            font-weight: bold;
            width: fit-content;
            transition: color 0.3s ease;
        }

        p a:hover {
            color: #d35400;
        }

        table {
            width: 95%;
            margin: 20px auto;
            border-collapse: collapse;
            background: rgba(255,255,255,0.95);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
            border-radius: 12px;
            overflow: hidden;
            animation: fadeInUp 1.2s ease;
        }

        th, td {
            padding: 14px 12px;
            text-align: center;
        }

        th {
            background: linear-gradient(90deg, #2196f3, #00bcd4);
            color: #fff;
            font-size: 15px;
            letter-spacing: 0.4px;
        }

        td {
            font-size: 14px;
            color: #444;
            transition: background-color 0.3s ease;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #e3f2fd;
        }

        select {
            padding: 6px 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            background: #fff;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        select:focus {
            outline: none;
            border-color: #2196f3;
        }

        button {
            padding: 6px 12px;
            background: linear-gradient(45deg, #ff7043, #ffa726);
            border: none;
            border-radius: 6px;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s ease, background 0.3s ease;
        }

        button:hover {
            transform: scale(1.05);
            background: linear-gradient(45deg, #e64a19, #fb8c00);
        }

        a {
            color: #2196f3;
            text-decoration: none;
            font-weight: 500;
            margin-left: 10px;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #1565c0;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            table, th, td {
                font-size: 12px;
            }

            button, select {
                font-size: 12px;
                padding: 5px 8px;
            }

            h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

    <h2>Manage Orders</h2>
    <p><a href="dashboard.php">← Back to Dashboard</a></p>

    <table>
        <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Email</th>
            <th>Product</th>
            <th>Qty</th>
            <th>Total</th>
            <th>Status</th>
            <th>Date</th>
            <th>Action</th>
        </tr>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['order_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['customer_email']); ?></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td>$<?php echo number_format($row['total'], 2); ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td><?php echo date('Y-m-d', strtotime($row['order_date'])); ?></td>
                    <td>
                        <form method="POST" style="margin:0; display:inline;">
                            <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                            <select name="status">
                                <option value="Pending" <?php if($row['status']=="Pending") echo "selected"; ?>>Pending</option>
                                
                                <option value="On the way" <?php if($row['status']=="On the way") echo "selected"; ?>>On the way</option>
                                <option value="Delivered" <?php if($row['status']=="Delivered") echo "selected"; ?>>Delivered</option>
                                <option value="Cancelled" <?php if($row['status']=="Cancelled") echo "selected"; ?>>Cancelled</option>
                            </select>
                            <button type="submit" name="update_status">Update</button>
                        </form>
                        <a href="view-order.php?id=<?php echo $row['order_id']; ?>">View</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="9">No orders found</td></tr>
        <?php endif; ?>
    </table>

</body>
</html>

