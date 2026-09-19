<?php
session_start();
require_once('includes/db.php');

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin-login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: manage-order.php");
    exit;
}

$order_id = intval($_GET['id']);

$sql = "SELECT o.order_id, o.quantity, o.unit_price, o.total, o.status, o.order_date,
               c.customer_name, c.customer_email, c.phone, c.customer_address,
               p.title, p.price
        FROM tbl_order o
        JOIN customer_registration c ON o.customer_id = c.customer_id
        JOIN tbl_product p ON o.product_id = p.product_id
        WHERE o.order_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    die("Order not found!");
}
$order = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Details | SmartStock Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        /* Base Styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(-45deg, #f8f9fa, #e3f2fd, #fce4ec, #fff3e0);
            background-size: 400% 400%;
            animation: gradientMove 15s ease infinite;
            padding: 30px 15px;
            color: #2c3e50;
        }

        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 12px 24px rgba(0,0,0,0.1);
            padding: 30px;
            animation: fadeInUp 1s ease;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #1a237e;
            font-size: 28px;
        }

        .section {
            margin-bottom: 25px;
            animation: fadeIn 1.2s ease;
        }

        .section h3 {
            font-size: 20px;
            color: #007bff;
            margin-bottom: 12px;
        }

        p {
            font-size: 15px;
            margin-bottom: 6px;
            line-height: 1.6;
        }

        strong {
            color: #333;
        }

        .back-link {
            display: block;
            margin-top: 30px;
            text-align: center;
            color: #e53935;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #d32f2f;
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            h2 {
                font-size: 22px;
            }

            .section h3 {
                font-size: 18px;
            }

            p {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Order Details (ID: <?php echo $order['order_id']; ?>)</h2>

    <div class="section">
        <h3>Customer Info</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($order['customer_address']); ?></p>
    </div>

    <div class="section">
        <h3>Product Ordered</h3>
        <p><strong>Item:</strong> <?php echo htmlspecialchars($order['title']); ?></p>
        <p><strong>Price:</strong> $<?php echo number_format($order['price'], 2); ?></p>
        <p><strong>Quantity:</strong> <?php echo $order['quantity']; ?></p>
        <p><strong>Total:</strong> $<?php echo number_format($order['total'], 2); ?></p>
    </div>

    <div class="section">
        <h3>Order Status</h3>
        <p><strong>Status:</strong> <?php echo $order['status']; ?></p>
        <p><strong>Placed On:</strong> <?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></p>
    </div>

    <a href="manage-order.php" class="back-link">← Back to Orders</a>
</div>

</body>
</html>

