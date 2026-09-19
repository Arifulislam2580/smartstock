<?php
session_start();
require_once('admin/includes/db.php');

// Currency constant
define('CURRENCY', '৳'); // BDT Taka
define('CURRENCY_CODE', 'BDT');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SmartStock Wholesale - Authentic Chinese Imported Products at Best Wholesale Prices in BDT">
    <title>SmartStock Wholesale | Chinese Imported Products | Laobaan Bangladesh</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #1e40af;
            --secondary: #0369a1;
            --accent: #f59e0b;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --bg-light: #f9fafb;
            --border: #e5e7eb;
            --success: #10b981;
            --danger: #ef4444;
        }

        html, body {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
            background: white;
            line-height: 1.6;
        }

        /* Header & Navigation */
        header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 5%;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        .logo-icon {
            font-size: 28px;
        }

        .logo-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .logo-main {
            font-size: 20px;
        }

        .logo-sub {
            font-size: 10px;
            opacity: 0.9;
            font-weight: 500;
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 30px;
            align-items: center;
        }

        nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.3s;
            font-size: 14px;
        }

        nav a:hover {
            opacity: 0.8;
        }

        .nav-icons {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .icon-btn {
            position: relative;
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--accent);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 50%, #0f172a 100%);
            color: white;
            padding: 80px 5%;
            text-align: center;
        }

        .hero h1 {
            font-size: 48px;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .hero p {
            font-size: 20px;
            margin-bottom: 30px;
            opacity: 0.95;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            font-size: 15px;
        }

        .btn-primary {
            background: var(--accent);
            color: white;
        }

        .btn-primary:hover {
            background: #d97706;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(245, 158, 11, 0.3);
        }

        .btn-secondary {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid white;
        }

        .btn-secondary:hover {
            background: white;
            color: var(--primary);
        }

        /* Featured Section */
        .section {
            padding: 60px 5%;
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-title {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--text-dark);
        }

        .section-subtitle {
            font-size: 16px;
            color: var(--text-light);
            margin-bottom: 40px;
        }

        /* Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 60px;
        }

        .product-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            transform: translateY(-5px);
            border-color: var(--primary);
        }

        .product-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #e0e0e0 0%, #f5f5f5 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            position: relative;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--accent);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge.stock {
            background: var(--success);
            top: auto;
            bottom: 10px;
        }

        .badge.low-stock {
            background: var(--danger);
        }

        .product-info {
            padding: 15px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .product-name {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-dark);
            line-height: 1.4;
        }

        .product-description {
            font-size: 12px;
            color: var(--text-light);
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .product-price {
            margin-top: auto;
            padding-top: 10px;
            border-top: 1px solid var(--border);
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .price-label {
            font-size: 12px;
            color: var(--text-light);
        }

        .price {
            font-size: 16px;
            font-weight: 700;
            color: var(--primary);
        }

        .original-price {
            text-decoration: line-through;
            color: var(--text-light);
            font-size: 13px;
        }

        .product-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .product-actions button {
            flex: 1;
            padding: 8px;
            border: 1px solid var(--border);
            background: white;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 12px;
            transition: all 0.3s;
        }

        .btn-add-cart {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .btn-add-cart:hover {
            background: var(--secondary);
        }

        /* Features Section */
        .features {
            background: var(--bg-light);
            padding: 60px 5%;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .feature-card {
            text-align: center;
            padding: 30px;
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border);
        }

        .feature-icon {
            font-size: 40px;
            margin-bottom: 15px;
            color: var(--primary);
        }

        .feature-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .feature-text {
            color: var(--text-light);
            font-size: 14px;
        }

        /* Footer */
        footer {
            background: var(--text-dark);
            color: white;
            padding: 50px 5%;
            margin-top: 80px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            max-width: 1400px;
            margin: 0 auto;
            margin-bottom: 40px;
        }

        .footer-section h3 {
            margin-bottom: 15px;
            font-size: 16px;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 13px;
            display: block;
            margin-bottom: 8px;
            transition: color 0.3s;
        }

        .footer-section a:hover {
            color: var(--accent);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 30px;
            text-align: center;
            font-size: 13px;
            color: rgba(255,255,255,0.6);
        }

        .company-info {
            display: flex;
            gap: 15px;
            align-items: center;
            margin-bottom: 15px;
        }

        .contact-info {
            font-size: 13px;
            line-height: 1.8;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
            }

            nav ul {
                flex-direction: column;
                gap: 15px;
            }

            .hero h1 {
                font-size: 32px;
            }

            .hero p {
                font-size: 16px;
            }

            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
                gap: 15px;
            }

            .section-title {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>

<!-- Header -->
<header>
    <div class="navbar">
        <div class="logo" onclick="location.href='index.php'">
            <span class="logo-icon">📦</span>
            <div class="logo-text">
                <span class="logo-main">SmartStock</span>
                <span class="logo-sub">Wholesale by Laobaan Bangladesh</span>
            </div>
        </div>
        <nav>
            <ul>
                <li><a href="#products">Products</a></li>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="#about">About Us</a></li>
                <li><a href="#contact">Contact</a></li>
                <?php if(isset($_SESSION['customer_logged_in'])): ?>
                    <li><a href="my-orders.php">My Orders</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="nav-icons">
            <button class="icon-btn" onclick="location.href='cart.php'">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-count" id="cart-count">0</span>
            </button>
            <?php if(isset($_SESSION['customer_logged_in'])): ?>
                <button class="icon-btn" onclick="location.href='customer-logout.php'" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            <?php else: ?>
                <button class="icon-btn" onclick="location.href='customer-login.php'" title="Login">
                    <i class="fas fa-user"></i>
                </button>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Hero Section -->
<section class="hero">
    <h1>💼 Wholesale Chinese Imported Products</h1>
    <p>Premium Quality Products at Best Wholesale Prices in Bangladesh Taka (BDT)</p>
    <div class="cta-buttons">
        <button class="btn btn-primary" onclick="document.getElementById('products').scrollIntoView()">Browse Products</button>
        <button class="btn btn-secondary" onclick="location.href='customer-register.php'">Join Now & Start Buying</button>
    </div>
</section>

<!-- Products Section -->
<section class="section" id="products">
    <h2 class="section-title">🛍️ Our Wholesale Products</h2>
    <p class="section-subtitle">Browse our extensive collection of authentic Chinese imported products</p>

    <div class="product-grid" id="product-grid">
        <?php
        // Fetch products from database
        $sql = "SELECT * FROM tbl_product WHERE active='Yes' ORDER BY featured DESC, product_id DESC LIMIT 12";
        $result = $conn->query($sql);
        
        if($result && $result->num_rows > 0):
            while($product = $result->fetch_assoc()):
                $discount = 0;
                if($product['original_price'] && $product['original_price'] > $product['price']) {
                    $discount = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
                }
                ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if($product['image_name']): ?>
                            <?php 
                            // Check if image_name is a URL (online) or local file
                            if (filter_var($product['image_name'], FILTER_VALIDATE_URL)) {
                                $img_src = htmlspecialchars($product['image_name']);
                            } else {
                                $img_src = "assets/images/" . htmlspecialchars($product['image_name']);
                            }
                            ?>
                            <img src="<?php echo $img_src; ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
                        <?php else: ?>
                            📦
                        <?php endif; ?>
                        <?php if($discount > 0): ?>
                            <span class="badge">-<?php echo $discount; ?>%</span>
                        <?php endif; ?>
                        <?php if($product['stock_quantity'] < 10): ?>
                            <span class="badge stock low-stock">Limited</span>
                        <?php else: ?>
                            <span class="badge stock">In Stock</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <div class="product-name"><?php echo htmlspecialchars($product['title']); ?></div>
                        <div class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 80)); ?>...</div>
                        <div class="product-price">
                            <div class="price-row">
                                <span class="price-label">Wholesale Price:</span>
                                <span class="price">৳ <?php echo number_format($product['price']); ?></span>
                            </div>
                            <?php if($product['original_price']): ?>
                                <div class="price-row">
                                    <span class="price-label">Original:</span>
                                    <span class="original-price">৳ <?php echo number_format($product['original_price']); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="product-actions">
                                <button class="btn-add-cart" onclick="addToCart(<?php echo $product['product_id']; ?>)">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            endwhile;
        else:
            echo '<p style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--text-light);">No products available yet. Check back soon!</p>';
        endif;
        ?>
    </div>
</section>

<!-- Features Section -->
<section class="features">
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">✅</div>
            <div class="feature-title">Authentic Products</div>
            <div class="feature-text">100% Genuine Chinese imported products directly from manufacturers</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🏆</div>
            <div class="feature-title">Best Wholesale Prices</div>
            <div class="feature-text">Competitive pricing for bulk orders with incredible savings in BDT</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🚚</div>
            <div class="feature-title">Fast Delivery</div>
            <div class="feature-text">Quick and reliable delivery across Bangladesh</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon">💳</div>
            <div class="feature-title">Easy Payment</div>
            <div class="feature-text">Secure payments in BDT with multiple payment options</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📱</div>
            <div class="feature-title">24/7 Support</div>
            <div class="feature-text">Round the clock customer support for all your queries</div>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📊</div>
            <div class="feature-title">Bulk Order Discounts</div>
            <div class="feature-text">Special rates for large quantity orders and regular customers</div>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="section" id="about" style="background: var(--bg-light);">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 class="section-title">About SmartStock Wholesale</h2>
        <p style="color: var(--text-light); font-size: 16px; line-height: 1.8; margin-bottom: 15px;">
            <strong>SmartStock</strong> is a leading wholesale distributor of authentic Chinese imported products in Bangladesh. We provide high-quality products directly from manufacturers at unbeatable prices in Bangladesh Taka (BDT).
        </p>
        <p style="color: var(--text-light); font-size: 16px; line-height: 1.8; margin-bottom: 15px;">
            Our mission is to make wholesale shopping easy and affordable for businesses and retailers across Bangladesh by offering genuine Chinese products with competitive pricing and excellent customer service.
        </p>
        <p style="color: var(--text-light); font-size: 16px; line-height: 1.8;">
            With over a decade of experience in international trade, <strong>Laobaan Bangladesh LTD.</strong> guarantees quality products and fast delivery across the country.
        </p>
    </div>
</section>

<!-- Contact Section -->
<section class="section" id="contact">
    <h2 class="section-title" style="text-align: center;">Get In Touch</h2>
    <div style="max-width: 600px; margin: 0 auto; background: var(--bg-light); padding: 40px; border-radius: 12px;">
        <div style="margin-bottom: 30px;">
            <strong style="color: var(--primary); font-size: 16px; display: block; margin-bottom: 8px;">📞 Phone</strong>
            <p style="color: var(--text-dark);">+880 1234-567890 | +880 1234-567891</p>
        </div>
        <div style="margin-bottom: 30px;">
            <strong style="color: var(--primary); font-size: 16px; display: block; margin-bottom: 8px;">📧 Email</strong>
            <p style="color: var(--text-dark);">wholesale@smartstock.com.bd<br>info@smartstock.com.bd</p>
        </div>
        <div style="margin-bottom: 30px;">
            <strong style="color: var(--primary); font-size: 16px; display: block; margin-bottom: 8px;">📍 Address</strong>
            <p style="color: var(--text-dark);">Dhaka, Bangladesh<br>Laobaan Bangladesh LTD.</p>
        </div>
        <div style="margin-bottom: 20px;">
            <strong style="color: var(--primary); font-size: 16px; display: block; margin-bottom: 8px;">💬 WhatsApp</strong>
            <p style="color: var(--text-dark);">+880 1234-567890</p>
        </div>
    </div>
</section>

<!-- Footer -->
<footer>
    <div class="footer-grid">
        <div class="footer-section">
            <h3>SmartStock Wholesale</h3>
            <div class="company-info">
                <div style="font-size: 24px;">📦</div>
                <div class="contact-info">
                    Authentic Chinese<br>Imported Products<br><br>
                    <strong>Laobaan Bangladesh LTD.</strong>
                </div>
            </div>
        </div>
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="#products">Products</a></li>
                <li><a href="#about">About Us</a></li>
                <li><a href="#contact">Contact</a></li>
                <li><a href="customer-login.php">Login</a></li>
                <li><a href="customer-register.php">Register</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Policies</h3>
            <ul>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms & Conditions</a></li>
                <li><a href="#">Return Policy</a></li>
                <li><a href="#">Shipping Info</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Contact Information</h3>
            <div class="contact-info">
                <strong>Email:</strong><br>
                wholesale@smartstock.com.bd<br><br>
                <strong>Phone:</strong><br>
                +880 1234-567890<br><br>
                <strong>WhatsApp:</strong><br>
                +880 1234-567890
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2024 SmartStock Wholesale | Laobaan Bangladesh LTD. | All Rights Reserved | Prices in BDT (৳)</p>
    </div>
</footer>

<script>
    // Update cart count
    function updateCartCount() {
        <?php
        $cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
        ?>
        document.getElementById('cart-count').textContent = <?php echo $cart_count; ?>;
    }

    // Add to cart
    function addToCart(productId) {
        window.location.href = 'cart.php?add=' + productId;
    }

    updateCartCount();
</script>

<?php include 'chat-widget.php'; ?>
</body>
</html>
