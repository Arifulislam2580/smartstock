<?php
session_start();
require_once('includes/db.php');

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin-login.php");
    exit;
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: admin-login.php");
    exit;
}

$totalAdmins = (int) ($conn->query("SELECT COUNT(*) as c FROM tbl_admin")->fetch_assoc()['c'] ?? 0);
$totalProducts = (int) ($conn->query("SELECT COUNT(*) as c FROM tbl_product")->fetch_assoc()['c'] ?? 0);
$totalOrders = (int) ($conn->query("SELECT COUNT(*) as c FROM tbl_order")->fetch_assoc()['c'] ?? 0);
$totalCustomers = (int) ($conn->query("SELECT COUNT(*) as c FROM customer_registration")->fetch_assoc()['c'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin Dashboard | SmartStock</title>
<link rel="stylesheet" href="../assets/css/admin.css" />
<style>
    /* Reset and base */
    body {
        font-family: 'Poppins', sans-serif;
        background: #fff8f0;
        margin: 0;
        padding: 30px 20px;
        color: #3e3e3e;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
    h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #b22222; /* Firebrick Red */
        font-weight: 700;
        letter-spacing: 1px;
    }

    /* Logout button */
    a.logout-btn {
        display: block;
        max-width: 140px;
        margin: 0 auto 40px auto;
        background: linear-gradient(45deg, #e63946, #f1faee);
        color: #fff;
        font-weight: 600;
        padding: 12px 25px;
        text-align: center;
        border-radius: 40px;
        box-shadow: 0 8px 15px rgba(230, 57, 70, 0.4);
        text-decoration: none;
        transition: all 0.4s ease;
        letter-spacing: 0.7px;
    }
    a.logout-btn:hover {
        background: linear-gradient(45deg, #f1faee, #e63946);
        color: #fff;
        box-shadow: 0 15px 30px rgba(230, 57, 70, 0.7);
        transform: translateY(-4px) scale(1.05);
    }

    /* Container for cards */
    .container {
        max-width: 960px;
        margin: 0 auto;
        display: flex;
        gap: 30px;
        flex-wrap: wrap;
        justify-content: center;
    }

    /* Card base styles */
    .card {
        border-radius: 18px;
        width: 320px;
        padding: 30px 25px;
        color: white;
        box-shadow: 0 15px 40px rgba(255, 183, 3, 0.4);
        cursor: default;
        transition: transform 0.4s ease, box-shadow 0.4s ease;
        position: relative;
        overflow: hidden;
    }
    .card:hover {
        transform: translateY(-15px) scale(1.07);
        box-shadow: 0 25px 60px rgba(255, 183, 3, 0.7);
        z-index: 5;
    }

    /* Gradient backgrounds */
    .stats {
        background: linear-gradient(135deg, #b22222 0%, #ffcc00 100%);
        box-shadow: 0 15px 50px rgba(178, 34, 34, 0.7);
    }

    .nav-links {
        background: linear-gradient(135deg, #ffcc00 0%, #b22222 100%);
        box-shadow: 0 15px 50px rgba(255, 204, 0, 0.7);
    }

    /* Heading inside cards */
    .card h3 {
        margin-top: 0;
        margin-bottom: 20px;
        font-weight: 800;
        font-size: 28px;
        letter-spacing: 1.1px;
        text-shadow: 1px 1px 4px rgba(0,0,0,0.2);
    }

    /* Stats list */
    .stats ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .stats li {
        font-size: 22px;
        font-weight: 700;
        padding: 15px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.25);
        display: flex;
        justify-content: space-between;
        letter-spacing: 0.5px;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.15);
    }
    .stats li:last-child {
        border-bottom: none;
    }
    .stats li span {
        font-weight: 900;
        font-size: 24px;
        color: #fffbe6;
        text-shadow: 2px 2px 5px rgba(0,0,0,0.3);
    }

    /* Quick action links */
    .nav-links a {
        display: block;
        background: rgba(255, 255, 255, 0.85);
        color: #b22222;
        font-weight: 700;
        text-align: center;
        padding: 14px 0;
        margin: 14px 0;
        border-radius: 12px;
        text-decoration: none;
        box-shadow: 0 8px 20px rgba(178, 34, 34, 0.25);
        transition: background 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
        letter-spacing: 0.6px;
    }
    .nav-links a:hover {
        background: #b22222;
        color: #fffbe6;
        box-shadow: 0 15px 35px rgba(178, 34, 34, 0.7);
        transform: scale(1.07);
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    }

    /* Responsive */
    @media (max-width: 700px) {
        .container {
            flex-direction: column;
            align-items: center;
        }
        .card {
            width: 90%;
        }
    }
</style>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</h2>
    <a href="admin-login.php?logout=1" class="logout-btn">Logout</a>

    <div class="container">
        <div class="card stats">
            <h3>Stats</h3>
            <ul>
                <li>Admins: <span><?php echo $totalAdmins; ?></span></li>
                <li>Products: <span><?php echo $totalProducts; ?></span></li>
                <li>Orders: <span><?php echo $totalOrders; ?></span></li>
                <li>Customers: <span><?php echo $totalCustomers; ?></span></li>
            </ul>
        </div>

        <div class="card nav-links">
            <h3>Quick Actions</h3>
            <a href="manage-products.php">📦 Manage Products</a>
            <a href="manage-categories.php">🏷️ Manage Categories</a>
            <a href="manage-admin.php">👤 Manage Admins</a>
            <a href="manage-order.php">📋 Manage Orders</a>
            <a href="../reports.php">📊 View Reports</a>
            <a href="manage-suppliers.php">🏭 Manage Suppliers</a>
            <a href="../chatbot.php">🤖 AI Assistant</a>
            <a href="../contact-support.php">💬 Customer Support</a>
            <a href="add-product.php">➕ Add New Product</a>
        </div>
    </div>
</body>
</html>

