<?php
session_start();
include("admin/includes/db.php");
if (!isset($_SESSION['customer_logged_in'])) {
    header("Location: customer-login.php");
    exit;
}

$message = "";
$isError = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_SESSION['cart'])) {
    $customer_id = (int)$_SESSION['customer_id'];
    $conn->begin_transaction();

    try {
        foreach ($_SESSION['cart'] as $item) {
            $product_id = (int)$item['id'];
            $quantity = (int)$item['quantity'];
            $unit_price = (float)$item['price'];
            $total = $unit_price * $quantity;

            $stockStmt = $conn->prepare("SELECT stock_quantity FROM tbl_product WHERE product_id = ? AND active = 'Yes' LIMIT 1");
            $stockStmt->bind_param("i", $product_id);
            $stockStmt->execute();
            $stockResult = $stockStmt->get_result();
            $stockRow = $stockResult->fetch_assoc();
            $stockStmt->close();

            if (!$stockRow || (int)$stockRow['stock_quantity'] < $quantity) {
                throw new Exception("Insufficient stock for one or more products.");
            }

            $stmt = $conn->prepare("INSERT INTO tbl_order (product_id, customer_id, quantity, unit_price, total, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
            $stmt->bind_param("iiidd", $product_id, $customer_id, $quantity, $unit_price, $total);
            $stmt->execute();
            $stmt->close();

            $updateStock = $conn->prepare("UPDATE tbl_product SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
            $updateStock->bind_param("ii", $quantity, $product_id);
            $updateStock->execute();
            $updateStock->close();
        }
        $conn->commit();
        $_SESSION['cart'] = [];
        $message = "🎉 Your order has been placed successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $message = "❌ Oops! Something went wrong. Please try again.";
        $isError = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Checkout | SmartStock</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');

  /* Reset & base */
  *, *::before, *::after {
      box-sizing: border-box;
  }
  body, html {
      margin: 0; padding: 0;
      height: 100%;
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #667eea, #764ba2, #6a11cb);
      background-size: 600% 600%;
      animation: gradientShift 25s ease infinite;
      color: #f5f7fa;
  }
  @keyframes gradientShift {
      0% {background-position:0% 50%;}
      50% {background-position:100% 50%;}
      100% {background-position:0% 50%;}
  }

  /* Navbar fixed on top */
  nav {
      position: fixed;
      top: 0; left: 0; right: 0;
      height: 60px;
      background: rgba(20, 20, 30, 0.85);
      backdrop-filter: blur(10px);
      box-shadow: 0 2px 8px rgba(0,0,0,0.7);
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 25px;
      z-index: 10000;
      color: #fff;
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
      gap: 25px;
      margin: 0;
      padding: 0;
  }
  nav ul li a {
      color: #ffca28;
      text-decoration: none;
      transition: color 0.3s ease;
  }
  nav ul li a:hover, nav ul li a:focus {
      color: #fff;
      outline: none;
  }

  /* Content wrapper with padding to avoid navbar overlap */
  main {
      padding-top: 80px; /* slightly more than navbar height */
      display: flex;
      justify-content: center;
      align-items: flex-start;
      min-height: 100vh;
  }

  /* Card Container */
  .card {
      background: rgba(255, 255, 255, 0.12);
      backdrop-filter: blur(14px);
      border-radius: 20px;
      max-width: 480px;
      width: 100%;
      padding: 40px 35px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.3);
      animation: fadeUp 0.8s ease forwards;
      text-align: center;
      position: relative;
      color: #f5f7fa;
  }
  @keyframes fadeUp {
      from {
          opacity: 0;
          transform: translateY(30px);
      } to {
          opacity: 1;
          transform: translateY(0);
      }
  }
  h2 {
      font-weight: 700;
      font-size: 2.8rem;
      margin-bottom: 25px;
      letter-spacing: 1px;
      color: #fff;
      text-shadow: 0 0 8px rgba(255,255,255,0.75);
  }
  .user-info p {
      margin-bottom: 14px;
      font-weight: 500;
      font-size: 1.1rem;
      color: #dcdfe4;
      letter-spacing: 0.3px;
  }
  .user-info strong {
      color: #fff;
  }
  .logout-link {
      display: inline-block;
      margin-top: 12px;
      font-weight: 600;
      color: #ffca28;
      text-decoration: none;
      font-size: 1rem;
      transition: color 0.3s ease;
  }
  .logout-link:hover,
  .logout-link:focus {
      color: #ffe54c;
      outline: none;
  }
  .message {
      margin: 25px 0;
      font-weight: 600;
      font-size: 1.2rem;
      padding: 15px 20px;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(255, 255, 255, 0.25);
      user-select: none;
      animation: pulseGlow 2.5s ease infinite;
  }
  .message.success {
      background: #28a745a8;
      color: #dff0d8;
      text-shadow: 0 0 6px #28a745;
  }
  .message.error {
      background: #dc3545b3;
      color: #f8d7da;
      text-shadow: 0 0 6px #dc3545;
  }
  @keyframes pulseGlow {
      0%, 100% {
          box-shadow: 0 0 10px rgba(255,255,255,0.25);
      }
      50% {
          box-shadow: 0 0 20px rgba(255,255,255,0.6);
      }
  }
  form p {
      font-size: 1.25rem;
      margin-bottom: 30px;
      color: #eee;
      letter-spacing: 0.5px;
  }
  button {
      position: relative;
      overflow: hidden;
      background: #ffca28;
      color: #333;
      border: none;
      font-weight: 700;
      font-size: 1.3rem;
      padding: 15px 0;
      width: 100%;
      border-radius: 50px;
      cursor: pointer;
      box-shadow: 0 8px 20px rgba(255, 202, 40, 0.6);
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
  }
  button:hover,
  button:focus {
      background-color: #ffd54f;
      box-shadow: 0 12px 30px rgba(255, 214, 79, 0.8);
      outline: none;
  }
  button:after {
      content: "";
      position: absolute;
      background: rgba(255, 255, 255, 0.4);
      border-radius: 50%;
      transform: scale(0);
      opacity: 0;
      pointer-events: none;
      animation: ripple 0.6s linear;
  }
  button:focus:after {
      animation-name: ripple;
      animation-duration: 0.6s;
      opacity: 1;
      transform: scale(2.5);
      top: 50%;
      left: 50%;
      width: 100%;
      height: 100%;
      margin-left: -50%;
      margin-top: -50%;
  }
  @keyframes ripple {
      to {
          opacity: 0;
          transform: scale(2.5);
      }
  }
  .empty-cart {
      font-size: 1.2rem;
      color: #eee;
      margin-top: 30px;
  }
  .empty-cart a {
      color: #ffca28;
      font-weight: 600;
      text-decoration: none;
      transition: color 0.3s ease;
  }
  .empty-cart a:hover,
  .empty-cart a:focus {
      color: #fff;
      text-decoration: underline;
      outline: none;
  }
  .spinner {
      border: 4px solid rgba(255,255,255,0.25);
      border-top: 4px solid #ffca28;
      border-radius: 50%;
      width: 28px;
      height: 28px;
      animation: spin 1s linear infinite;
      position: absolute;
      top: 50%;
      right: 20px;
      transform: translateY(-50%);
      display: none;
  }
  form.loading button {
      pointer-events: none;
      opacity: 0.7;
  }
  form.loading .spinner {
      display: inline-block;
  }
  @keyframes spin {
      to {
          transform: rotate(360deg);
      }
  }
  @media (max-width: 480px) {
      .card {
          padding: 30px 25px;
      }
      h2 {
          font-size: 2.2rem;
      }
      button {
          font-size: 1.1rem;
      }
      nav {
          font-size: 1rem;
          padding: 0 15px;
      }
  }
</style>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', e => {
                form.classList.add('loading');
            });
        }
    });
</script>
</head>
<body>

<nav aria-label="Main Navigation">
  <div class="logo">📦 SmartStock</div>
  <ul>
    <li><a href="menu.php" tabindex="0">Menu</a></li>
    <li><a href="cart.php" tabindex="0">Cart</a></li>
    <li><a href="my-orders.php" tabindex="0">Orders</a></li>
    <li><a href="customer-logout.php" tabindex="0">Logout</a></li>
  </ul>
</nav>

<main>
  <section class="card" role="main" aria-live="polite" aria-label="Checkout section">

    <h2>Checkout</h2>

    <div class="user-info" aria-label="Customer information">
        <p><strong>Logged in as:</strong> <?php echo htmlspecialchars($_SESSION['customer_name']); ?> (<a href="mailto:<?php echo htmlspecialchars($_SESSION['customer_email']); ?>" style="color:#ffca28; text-decoration:none;"><?php echo htmlspecialchars($_SESSION['customer_email']); ?></a>)</p>
        <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($_SESSION['customer_address'])); ?></p>
    </div>

    <?php if ($message): ?>
        <div class="message <?php echo $isError ? 'error' : 'success'; ?>" role="alert" tabindex="0">
            <?php echo $message; ?>
        </div>
    <?php elseif (!empty($_SESSION['cart'])): ?>
        <form method="POST" novalidate aria-label="Order confirmation form">
            <p>Confirm your order by clicking the button below.</p>
            <button type="submit" aria-live="polite" aria-busy="false" aria-label="Place order">Place Order</button>
            <span class="spinner" aria-hidden="true"></span>
        </form>
    <?php else: ?>
        <div class="empty-cart" role="alert" tabindex="0">
            Your cart is empty. <a href="menu.php">Go back to menu</a>
        </div>
    <?php endif; ?>

  </section>
</main>

<?php include 'chat-widget.php'; ?>
</body>
</html>
