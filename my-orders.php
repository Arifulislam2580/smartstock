<?php
session_start();
include("admin/includes/db.php");


// Ensure customer is logged in
if (!isset($_SESSION['customer_logged_in'])) {
    header("Location: customer-login.php");
    exit;
}

$customer_id = (int)$_SESSION['customer_id']; // sanitize input

// Fetch orders for this customer
$sql = "SELECT o.order_id, p.title, o.quantity, o.unit_price, o.total, o.status, o.order_date
        FROM tbl_order o
        JOIN tbl_product p ON o.product_id = p.product_id
        WHERE o.customer_id = ?
        ORDER BY o.order_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>My Orders | SmartStock</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');

  /* Reset & base */
  *, *::before, *::after {
      box-sizing: border-box;
  }
  body, html {
      margin: 0; padding: 0;
      font-family: 'Inter', sans-serif;
      background: #1e1e2f;
      color: #e1e1e6;
      min-height: 100vh;
  }

  /* Fixed Navbar */
  nav {
      position: fixed;
      top: 0; left: 0; right: 0;
      height: 60px;
      background: rgba(30, 30, 47, 0.95);
      backdrop-filter: blur(10px);
      box-shadow: 0 2px 8px rgba(0,0,0,0.6);
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 25px;
      z-index: 10000;
      color: #ffca28;
      font-weight: 600;
      font-size: 1.25rem;
      user-select: none;
  }
  nav .logo {
      font-size: 1.4rem;
  }
  nav ul {
      list-style: none;
      display: flex;
      gap: 20px;
      margin: 0;
      padding: 0;
  }
  nav ul li a {
      color: #ffca28;
      text-decoration: none;
      transition: color 0.3s ease;
  }
  nav ul li a:hover,
  nav ul li a:focus {
      color: #fff;
      outline: none;
  }

  /* Main container */
  main {
      padding: 90px 20px 40px;
      max-width: 960px;
      margin: 0 auto;
  }
  h2 {
      text-align: center;
      font-weight: 700;
      font-size: 2.5rem;
      margin-bottom: 10px;
      color: #ffca28;
      text-shadow: 0 0 6px rgba(255,202,40,0.7);
  }
  p.welcome {
      text-align: center;
      margin: 0 0 30px;
      font-size: 1.2rem;
      color: #ccc;
  }
  p.links {
      text-align: center;
      margin-bottom: 40px;
  }
  p.links a {
      color: #ffca28;
      font-weight: 600;
      text-decoration: none;
      margin: 0 10px;
      transition: color 0.3s ease;
  }
  p.links a:hover,
  p.links a:focus {
      color: #fff;
      outline: none;
  }

  /* Table Styling */
  table {
      width: 100%;
      border-collapse: collapse;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
      background: rgba(40, 40, 60, 0.9);
      border-radius: 12px;
      overflow: hidden;
      font-size: 1rem;
      color: #eee;
      animation: fadeIn 0.8s ease forwards;
  }
  thead tr {
      background: #272742;
      font-weight: 700;
      letter-spacing: 0.05em;
  }
  th, td {
      padding: 14px 18px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      text-align: center;
  }
  tbody tr:hover {
      background: rgba(255, 202, 40, 0.15);
      cursor: default;
  }
  tbody tr:last-child td {
      border-bottom: none;
  }

  /* Status colors */
  .status-pending {
      color: #f0ad4e;
      font-weight: 600;
  }
  .status-completed {
      color: #28a745;
      font-weight: 700;
  }
  .status-canceled {
      color: #dc3545;
      font-weight: 700;
  }

  /* Responsive */
  @media (max-width: 700px) {
      main {
          padding: 100px 10px 20px;
      }
      table, thead, tbody, th, td, tr {
          display: block;
      }
      thead tr {
          display: none;
      }
      tbody tr {
          margin-bottom: 20px;
          background: rgba(40, 40, 60, 0.9);
          border-radius: 12px;
          padding: 15px;
      }
      tbody tr:hover {
          background: rgba(255, 202, 40, 0.15);
      }
      tbody td {
          text-align: right;
          padding-left: 50%;
          position: relative;
          border: none;
          border-bottom: 1px solid rgba(255,255,255,0.1);
      }
      tbody td::before {
          content: attr(data-label);
          position: absolute;
          left: 18px;
          width: 45%;
          padding-left: 15px;
          font-weight: 600;
          text-align: left;
          color: #ffca28;
          white-space: nowrap;
      }
      tbody td:last-child {
          border-bottom: none;
      }
  }

  /* FadeIn animation */
  @keyframes fadeIn {
      from {
          opacity: 0;
          transform: translateY(10px);
      }
      to {
          opacity: 1;
          transform: translateY(0);
      }
  }
</style>
</head>
<body>

<nav aria-label="Main Navigation">
  <div class="logo">📦 SmartStock</div>
  <ul>
    <li><a href="menu.php" tabindex="0">Menu</a></li>
    <li><a href="cart.php" tabindex="0">Cart</a></li>
    <li><a href="my-orders.php" tabindex="0" aria-current="page">Orders</a></li>
    <li><a href="customer-logout.php" tabindex="0">Logout</a></li>
  </ul>
</nav>

<main>
  <h2>My Orders</h2>
  <p class="welcome">Welcome, <strong><?php echo htmlspecialchars($_SESSION['customer_name']); ?></strong>!</p>
  <p class="links">
    <a href="menu.php">Back to Menu</a> | 
    <a href="customer-logout.php">Logout</a>
  </p>

  <table role="table" aria-label="Customer orders">
    <thead>
      <tr>
        <th scope="col">Order ID</th>
        <th scope="col">Food</th>
        <th scope="col">Quantity</th>
        <th scope="col">Total ($)</th>
        <th scope="col">Status</th>
        <th scope="col">Date</th>
      </tr>
    </thead>
    <tbody>
    <?php if ($result && $result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): 
        // Define status class for coloring
        $statusClass = '';
        $statusText = htmlspecialchars($row['status']);
        switch (strtolower($row['status'])) {
          case 'pending': $statusClass = 'status-pending'; break;
          case 'completed': $statusClass = 'status-completed'; break;
          case 'canceled': $statusClass = 'status-canceled'; break;
          default: $statusClass = '';
        }
      ?>
        <tr>
          <td data-label="Order ID"><?php echo $row['order_id']; ?></td>
          <td data-label="Food"><?php echo htmlspecialchars($row['title']); ?></td>
          <td data-label="Quantity"><?php echo $row['quantity']; ?></td>
          <td data-label="Total"><?php echo number_format($row['total'], 2); ?></td>
          <td data-label="Status" class="<?php echo $statusClass; ?>"><?php echo $statusText; ?></td>
          <td data-label="Date"><?php echo date("M d, Y", strtotime($row['order_date'])); ?></td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="6">No orders found.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</main>

<?php include 'chat-widget.php'; ?>
</body>
</html>
