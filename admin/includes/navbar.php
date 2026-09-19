<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Montserrat:wght@500;700&display=swap" rel="stylesheet">

<style>
    nav.custom-nav {
        background: linear-gradient(270deg, #ff4e50, #f9d423, #ffffff, #f9d423, #ff4e50);
        background-size: 1000% 1000%;
        animation: gradientMove 20s ease infinite;
        padding: 20px 10%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 1000;
        min-height: 80px;
        backdrop-filter: blur(6px);
        user-select: none;
    }

    @keyframes gradientMove {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    .custom-nav .brand {
        font-family: 'Pacifico', cursive;
        font-size: 30px;
        color: #ff0000;
        letter-spacing: 1.5px;
        animation: fadeInDown 1s ease forwards;
        user-select: none;
        text-shadow: 2px 2px 5px rgba(255, 0, 0, 0.4);
        cursor: pointer;
    }

    .custom-nav .nav-links {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }

    .custom-nav a,
    .custom-nav span.user-greeting {
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 30px;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        background: rgba(255, 255, 255, 0.9);
        color: #ff4e50;
        transform: scale(1);
    }

    .custom-nav a:hover,
    .custom-nav a:focus {
        background: #f9d423;
        color: #333;
        transform: scale(1.08);
        box-shadow: 0 5px 10px rgba(0,0,0,0.15);
        outline: none;
    }

    .custom-nav span.user-greeting {
        background: rgba(255, 255, 255, 0.95);
        color: #1f2937;
        animation: fadeIn 1s ease forwards;
        cursor: default;
        user-select: text;
        box-shadow: 0 3px 6px rgba(0,0,0,0.08);
    }

    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @media (max-width: 768px) {
        nav.custom-nav {
            flex-direction: column;
            align-items: flex-start;
            padding: 15px 5%;
            min-height: 100px;
        }

        .custom-nav .brand {
            font-size: 26px;
            margin-bottom: 10px;
        }

        .custom-nav .nav-links {
            flex-direction: column;
            align-items: flex-start;
            width: 100%;
        }

        .custom-nav a,
        .custom-nav span.user-greeting {
            width: 100%;
            padding: 10px 16px;
            margin-left: 0;
        }
    }
</style>

<nav class="custom-nav" role="navigation" aria-label="Main Navigation">
    <div class="brand" onclick="window.location.href='index.php';">📦 SmartStock</div>

    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="menu.php">Menu</a>
        <a href="cart.php">Cart</a>
        <a href="my-orders.php">My Orders</a>
        <a href="chatbot.php">AI Assistant</a>
        <a href="contact-support.php">Support</a>

        <?php if (isset($_SESSION['customer_logged_in']) && $_SESSION['customer_logged_in']): ?>
            <span class="user-greeting">Hello, <?php echo htmlspecialchars($_SESSION['customer_name']); ?>!</span>
            <a href="customer-logout.php">Logout</a>
        <?php else: ?>
            <a href="customer-login.php">Login</a>
            <a href="customer-register.php">Register</a>
            <a href="admin/admin-login.php">Admin</a>
        <?php endif; ?>
    </div>
</nav>
