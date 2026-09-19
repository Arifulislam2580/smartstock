<?php
session_start();
include("admin/includes/db.php");
include("admin/includes/navbar.php");

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add to cart
if (isset($_POST['add_to_cart']) || isset($_GET['add'])) {
    $product_id = intval($_POST['product_id'] ?? $_GET['add'] ?? 0);
    $quantity = max(1, intval($_POST['quantity'] ?? 1));

    $stmt = $conn->prepare("SELECT product_id, title, price FROM tbl_product WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if ($product) {
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] === $product_id) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $_SESSION['cart'][] = [
                'id' => $product['product_id'],
                'title' => $product['title'],
                'price' => $product['price'],
                'quantity' => $quantity
            ];
        }
    }
}

// Remove item
if (isset($_GET['remove'])) {
    $remove_id = intval($_GET['remove']);
    foreach ($_SESSION['cart'] as $index => $item) {
        if ($item['id'] === $remove_id) {
            unset($_SESSION['cart'][$index]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Shopping Cart | SmartStock</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
    <style>
        /* Reset and basics */
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: linear-gradient(270deg, #0f2027, #203a43, #2c5364);
            background-size: 600% 600%;
            animation: gradientShift 30s ease infinite;
            color: #f0f0f0;
            padding: 40px 20px;
            line-height: 1.6;
        }

        h2 {
            font-weight: 600;
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 2rem;
            color: #ffd700;
            text-shadow:
                0 0 5px #ffd700,
                0 0 10px #ffd700,
                0 0 20px #ffd700,
                0 0 40px #ffc107;
            animation: fadeInDown 1.2s ease forwards;
            opacity: 0;
        }

        ul {
            list-style: none;
            max-width: 720px;
            margin: 0 auto 2rem auto;
            background: rgba(30, 41, 61, 0.85);
            border-radius: 12px;
            box-shadow:
                0 8px 32px 0 rgba(31, 38, 135, 0.37);
            padding: 1.5rem 2rem;
            animation: fadeInUp 1.2s ease forwards;
            opacity: 0;
            transition: background-color 0.3s ease;
        }

        ul li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 215, 0, 0.2);
            font-weight: 500;
            font-size: 1.1rem;
        }

        ul li:last-child {
            border-bottom: none;
        }

        ul li span.title {
            flex: 2;
            color: #fffacd;
            font-weight: 600;
        }

        ul li span.details {
            flex: 1.5;
            text-align: right;
            color: #ffeb99;
        }

        a.remove-btn {
            background: #ff6b6b;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 600;
            color: white;
            text-decoration: none;
            box-shadow:
                0 3px 8px rgba(255, 107, 107, 0.6);
            transition: background 0.3s ease, transform 0.2s ease;
            margin-left: 1rem;
            flex-shrink: 0;
        }

        a.remove-btn:hover {
            background: #ff5252;
            transform: scale(1.05);
            box-shadow:
                0 5px 15px rgba(255, 82, 82, 0.9);
        }

        p.total {
            text-align: center;
            font-size: 1.6rem;
            font-weight: 700;
            color: #ffe066;
            text-shadow:
                0 0 8px #ffe066,
                0 0 15px #ffd700;
            margin-bottom: 2rem;
            animation: fadeIn 1.8s ease forwards;
            opacity: 0;
        }

        .cart-actions {
            text-align: center;
            font-size: 1.15rem;
            font-weight: 600;
            animation: fadeIn 2s ease forwards;
            opacity: 0;
        }

        .cart-actions a {
            color: #ffd54f;
            text-decoration: none;
            margin: 0 15px;
            padding: 10px 22px;
            border: 2px solid #ffd54f;
            border-radius: 50px;
            transition: background-color 0.3s ease, color 0.3s ease;
            box-shadow:
                0 0 10px #ffd54f;
        }

        .cart-actions a:hover {
            background-color: #ffd54f;
            color: #0f2027;
            box-shadow:
                0 0 20px #fff176;
        }

        p.empty-message {
            text-align: center;
            font-size: 1.3rem;
            color: #bbb;
            margin-top: 4rem;
            animation: fadeIn 2s ease forwards;
            opacity: 0;
        }

        p.empty-message a {
            color: #90caf9;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        p.empty-message a:hover {
            color: #42a5f5;
        }

        /* Animations */
        @keyframes gradientShift {
            0% {background-position: 0% 50%;}
            50% {background-position: 100% 50%;}
            100% {background-position: 0% 50%;}
        }

        @keyframes fadeInUp {
            from {opacity: 0; transform: translateY(30px);}
            to {opacity: 1; transform: translateY(0);}
        }

        @keyframes fadeInDown {
            from {opacity: 0; transform: translateY(-30px);}
            to {opacity: 1; transform: translateY(0);}
        }

        @keyframes fadeIn {
            from {opacity: 0;}
            to {opacity: 1;}
        }

        /* Responsive */
        @media (max-width: 768px) {
            ul li {
                flex-direction: column;
                align-items: flex-start;
            }
            ul li span.details {
                text-align: left;
                margin-top: 6px;
            }
            a.remove-btn {
                margin-left: 0;
                margin-top: 8px;
            }
        }
    </style>
</head>
<body>
    <section><h1>   </h1></section>
    <h2>Your Cart</h2>

    <?php if (!empty($_SESSION['cart'])): ?>
        <ul>
            <?php
            $total = 0;
            foreach ($_SESSION['cart'] as $item):
                $subtotal = $item['price'] * $item['quantity'];
                $total += $subtotal;
            ?>
            <li>
                <span class="title"><?php echo htmlspecialchars($item['title']); ?></span>
                <span class="details">
                    <?php echo htmlspecialchars($item['quantity']); ?> × $<?php echo number_format($item['price'], 2); ?>
                    = $<?php echo number_format($subtotal, 2); ?>
                </span>
                <a class="remove-btn" href="cart.php?remove=<?php echo $item['id']; ?>" title="Remove this item">Remove</a>
            </li>
            <?php endforeach; ?>
        </ul>
        <p class="total">Total: $<?php echo number_format($total, 2); ?></p>

        <?php if (isset($_SESSION['customer_logged_in'])): ?>
            <div class="cart-actions">
                <a href="order.php">Proceed to Checkout</a>
                <a href="my-orders.php">View My Orders</a>
            </div>
        <?php else: ?>
            <p class="empty-message">You must <a href="customer-login.php">Login</a> or <a href="customer-register.php">Register</a> to checkout.</p>
        <?php endif; ?>

    <?php else: ?>
        <p class="empty-message">Your cart is empty. <a href="menu.php">Go back to menu</a></p>
    <?php endif; ?>
<?php include 'chat-widget.php'; ?>
</body>
</html>
