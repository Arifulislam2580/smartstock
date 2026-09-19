<?php
session_start();
include('admin/includes/db.php');

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin/admin-login.php');
    exit;
}

$productsCount = (int)($conn->query("SELECT COUNT(*) AS c FROM tbl_product")->fetch_assoc()['c'] ?? 0);
$ordersCount = (int)($conn->query("SELECT COUNT(*) AS c FROM tbl_order")->fetch_assoc()['c'] ?? 0);
$customersCount = (int)($conn->query("SELECT COUNT(*) AS c FROM customer_registration")->fetch_assoc()['c'] ?? 0);
$lowStock = $conn->query("SELECT title, stock_quantity FROM tbl_product WHERE active='Yes' AND stock_quantity < 10 ORDER BY stock_quantity ASC LIMIT 10");
$revenue = $conn->query("SELECT COALESCE(SUM(total), 0) AS total FROM tbl_order");
$revenueValue = (float)($revenue->fetch_assoc()['total'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reports | SmartStock</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f8fafc; color: #0f172a; }
        .page { max-width: 1000px; margin: 40px auto; padding: 20px; }
        .card { background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 8px 22px rgba(0,0,0,0.06); }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; }
        .stat { background: linear-gradient(135deg, #2563eb, #0f766e); color: white; padding: 18px; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 10px; border-bottom: 1px solid #e2e8f0; }
        th { background: #f1f5f9; }
        .back { display: inline-block; margin-bottom: 16px; color: #2563eb; text-decoration: none; }
    </style>
</head>
<body>
    <div class="page">
        <a class="back" href="admin/dashboard.php">← Back to Dashboard</a>
        <h2>SmartStock Reports</h2>
        <div class="stats">
            <div class="stat"><strong>Products</strong><br /><?php echo $productsCount; ?></div>
            <div class="stat"><strong>Orders</strong><br /><?php echo $ordersCount; ?></div>
            <div class="stat"><strong>Customers</strong><br /><?php echo $customersCount; ?></div>
            <div class="stat"><strong>Revenue</strong><br />৳<?php echo number_format($revenueValue, 2); ?></div>
        </div>

        <div class="card">
            <h3>Low Stock Alert</h3>
            <table>
                <thead>
                    <tr><th>Product</th><th>Stock</th></tr>
                </thead>
                <tbody>
                    <?php if ($lowStock && $lowStock->num_rows > 0): ?>
                        <?php while ($row = $lowStock->fetch_assoc()): ?>
                            <tr><td><?php echo htmlspecialchars($row['title']); ?></td><td><?php echo (int)$row['stock_quantity']; ?></td></tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="2">No low-stock products right now.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
