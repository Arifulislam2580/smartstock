<?php
session_start();
include("admin/includes/db.php");
include("admin/includes/navbar.php");

$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';
$search = trim($_GET['search'] ?? '');
$category_filter = intval($_GET['category'] ?? 0);

$query = "SELECT p.*, c.title AS category_title FROM tbl_product p LEFT JOIN tbl_category c ON p.category_id = c.id WHERE p.active='Yes'";
$params = [];
$types = '';

if ($search !== '') {
    $like = '%' . $search . '%';
    $query .= " AND (p.title LIKE ? OR p.description LIKE ? OR c.title LIKE ?)";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'sss';
}

if ($category_filter > 0) {
    $query .= " AND p.category_id = ?";
    $params[] = $category_filter;
    $types .= 'i';
}

$query .= " ORDER BY p.created_at DESC";
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $bindParams = [$types];
    foreach ($params as $index => &$param) {
        $bindParams[] = &$params[$index];
    }
    call_user_func_array([$stmt, 'bind_param'], $bindParams);
}
$stmt->execute();
$products = $stmt->get_result();
$categories = $conn->query("SELECT * FROM tbl_category WHERE active='Yes' ORDER BY title ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Menu | SmartStock - Product Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet" />
    <style>
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

        body {
            font-family: 'Montserrat', sans-serif;
            background: var(--bg-light);
            padding: 120px 5% 50px;
            color: var(--text-dark);
        }
        h2 {
            text-align: center;
            font-size: 2.8rem;
            margin-bottom: 16px;
            color: var(--primary);
        }
        .page-header {
            max-width: 1100px;
            margin: 0 auto 28px;
            text-align: center;
        }
        .menu-summary {
            color: var(--text-light);
            font-size: 1rem;
            margin-top: 8px;
        }
        .filter-panel {
            max-width: 900px;
            margin: 0 auto 30px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            border: 1px solid rgba(229,231,235,0.9);
            border-radius: 14px;
            padding: 14px 18px;
            box-shadow: 0 10px 30px rgba(15,23,42,0.06);
        }
        .filter-panel input,
        .filter-panel select {
            min-width: 220px;
            flex: 1;
            max-width: 360px;
        }
        .category-badge {
            display: inline-block;
            margin-top: 12px;
            padding: 6px 14px;
            background: #eef2ff;
            color: #4338ca;
            border-radius: 999px;
            font-size: 0.92rem;
            font-weight: 600;
        }
        .no-products {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            padding: 28px 22px;
            border-radius: 16px;
            text-align: center;
            color: #475569;
            box-shadow: 0 12px 30px rgba(15,23,42,0.07);
            border: 1px solid rgba(226,232,240,0.8);
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
            max-width: 1600px;
            margin: 0 auto;
        }
        .product-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 18px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        .product-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        .product-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        .product-content {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .product-content h3 {
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--primary);
        }
        .product-content p {
            flex-grow: 1;
            font-size: 0.95rem;
            color: var(--text-light);
            margin-bottom: 15px;
        }
        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .price {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--secondary);
        }
        form {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        input[type="number"] {
            width: 60px;
            padding: 6px 8px;
            border: 1.5px solid var(--border);
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        input[type="number"]:focus {
            outline: none;
            border-color: var(--primary);
        }
        button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 16px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        button:hover {
            background: var(--secondary);
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 140px 3% 50px;
            }
            h2 {
                font-size: 2.2rem;
                margin-bottom: 30px;
            }
            .product-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
            }
            .product-card img {
                height: 150px;
            }
        }
        @media (max-width: 480px) {
            body {
                padding: 160px 2% 30px;
            }
            h2 {
                font-size: 1.8rem;
                margin-bottom: 20px;
            }
            .product-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
            }
            .product-card {
                border-radius: 8px;
            }
            .product-content {
                padding: 15px;
            }
            .product-content h3 {
                font-size: 1.1rem;
            }
            .product-content p {
                font-size: 0.9rem;
            }
            .price {
                font-size: 1.1rem;
            }
            input[type="number"] {
                width: 50px;
                padding: 4px 6px;
            }
            button {
                padding: 8px 12px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>

    <div class="page-header">
        <h2>Our Menu</h2>
        <?php if (!empty($_SESSION['customer_logged_in']) && !empty($_SESSION['customer_name'])): ?>
            <p class="menu-summary">Welcome back, <strong><?php echo htmlspecialchars($_SESSION['customer_name'], ENT_QUOTES, 'UTF-8'); ?></strong>. Browse the latest products below.</p>
        <?php else: ?>
            <p class="menu-summary">Browse our available products and add items to your cart.</p>
        <?php endif; ?>
        <p class="menu-summary">
            <?php if (!empty($search)): ?>
                Showing <?php echo $products->num_rows; ?> results for "<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>".
            <?php elseif ($category_filter > 0): ?>
                Showing <?php echo $products->num_rows; ?> products in the selected category.
            <?php else: ?>
                Showing <?php echo $products->num_rows; ?> available products.
            <?php endif; ?>
        </p>
    </div>

    <form class="filter-panel" method="get" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" role="search" aria-label="Product search">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search products..." aria-label="Search products" />
        <select name="category" aria-label="Filter by category">
            <option value="0">All Categories</option>
            <?php while ($category = $categories->fetch_assoc()): ?>
                <option value="<?php echo (int)$category['id']; ?>" <?php if ($category_filter === (int)$category['id']) echo 'selected'; ?>><?php echo htmlspecialchars($category['title']); ?></option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Filter</button>
    </form>

    <?php if ($products->num_rows === 0): ?>
        <div class="no-products">
            <h3>No products found</h3>
            <p>Try adjusting your search or select a different category.</p>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php while($row = $products->fetch_assoc()): ?>
            <div class="product-card">
                <?php $display_title = preg_replace('/\s*-\s*Sample Item$/i', '', $row['title']); ?>
                <?php 
                    $img_src = '';
                    if (!empty($row['image_name'])) {
                        if (filter_var($row['image_name'], FILTER_VALIDATE_URL)) {
                            $img_src = $row['image_name'];
                        } else {
                            $local_path = __DIR__ . '/assets/images/' . $row['image_name'];
                            if (file_exists($local_path)) {
                                $img_src = $baseUrl . 'assets/images/' . str_replace(' ', '%20', $row['image_name']);
                            }
                        }
                    }

                    if (empty($img_src)) {
                        $query = urlencode(preg_replace('/[^A-Za-z0-9 ]+/', '', $row['title']));
                        $img_src = "https://source.unsplash.com/700x420/?$query";
                    }
                ?>
                <img src="<?php echo htmlspecialchars($img_src); ?>" alt="<?php echo htmlspecialchars($display_title); ?>" onerror="this.onerror=null;this.src='https://via.placeholder.com/700x420?text=No+Image';" />
                <div class="product-content">
                    <?php $display_title = preg_replace('/\s*-\s*Sample Item$/i', '', $row['title']); ?>
                    <h3><?php echo htmlspecialchars($display_title); ?></h3>
                    <p><?php echo htmlspecialchars($row['description']); ?></p>
                    <div class="product-footer">
                        <div class="price">৳ <?php echo number_format($row['price']); ?></div>
                        <form method="post" action="<?php echo htmlspecialchars($baseUrl . 'cart.php'); ?>">
                            <input type="hidden" name="product_id" value="<?php echo (int)$row['product_id']; ?>" />
                            <input type="number" name="quantity" value="1" min="1" />
                            <button type="submit" name="add_to_cart">Add to Cart</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>

    <?php include 'chat-widget.php'; ?>
</body>
</html>
