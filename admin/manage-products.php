<?php
session_start();
require_once("includes/db.php");

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin-login.php");
    exit;
}

$category_filter = intval($_GET['category'] ?? 0);

$sql = "SELECT p.*, c.title AS category 
        FROM tbl_product p 
        LEFT JOIN tbl_category c ON p.category_id = c.id";
if ($category_filter > 0) {
    $sql .= " WHERE p.category_id = ?";
}
$sql .= " ORDER BY p.product_id DESC";

$stmt = $conn->prepare($sql);
if ($category_filter > 0) {
    $stmt->bind_param("i", $category_filter);
}
$stmt->execute();
$products = $stmt->get_result();

$categories_result = $conn->query("SELECT * FROM tbl_category ORDER BY title ASC");
$categories = [];
while ($cat = $categories_result->fetch_assoc()) {
    $categories[] = $cat;
}

function getProductImageUrl($image_name)
{
    if (empty($image_name)) {
        return '';
    }

    if (filter_var($image_name, FILTER_VALIDATE_URL)) {
        return $image_name;
    }

    return '../assets/images/' . $image_name;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Manage Products | SmartStock Admin</title>
<link rel="stylesheet" href="../assets/css/admin.css" />
<style>
  /* Animated gradient background */
  body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(270deg, #ff4e50, #f9d423, #ffffff, #f9d423, #ff4e50);
    background-size: 1000% 1000%;
    animation: gradientAnimation 20s ease infinite;
    color: #3a3a3a;
  }
  @keyframes gradientAnimation {
    0%{background-position:0% 50%}
    50%{background-position:100% 50%}
    100%{background-position:0% 50%}
  }

  h2 {
    text-align: center;
    color: #b22222; /* Firebrick */
    margin: 25px 0 15px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.15);
  }

  a.add-product {
    display: inline-block;
    margin: 15px 0 30px 20px;
    padding: 12px 28px;
    background: linear-gradient(45deg, #f9d423, #ff4e50);
    color: white;
    font-weight: 700;
    border-radius: 25px;
    text-decoration: none;
    box-shadow: 0 6px 15px rgba(255,78,80,0.5);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  a.add-product:hover {
    transform: scale(1.1);
    box-shadow: 0 12px 25px rgba(255,78,80,0.8);
  }

  form {
    margin-bottom: 20px;
  }
  form label {
    font-weight: 700;
    color: #b22222;
    margin-right: 10px;
  }
  form select {
    padding: 8px 12px;
    border: 2px solid #f9d423;
    border-radius: 8px;
    background: #fff;
    font-size: 14px;
    cursor: pointer;
  }
  form select:focus {
    outline: none;
    border-color: #ff4e50;
    box-shadow: 0 0 5px rgba(255,78,80,0.5);
  }

  table {
    width: 95%;
    max-width: 1200px;
    margin: 0 auto 50px;
    border-collapse: collapse;
    box-shadow: 0 15px 50px rgba(255,78,80,0.15);
    background: rgba(255,255,255,0.95);
    border-radius: 12px;
    overflow: hidden;
  }

  th, td {
    padding: 16px 20px;
    text-align: center;
    border-bottom: 1px solid #eee;
    font-weight: 600;
    color: #5a2a27;
  }

  th {
    background: linear-gradient(90deg, #ff4e50, #f9d423);
    color: white;
    letter-spacing: 1.1px;
    font-size: 16px;
  }

  tr:hover {
    background: #fff4e6;
    transform: scale(1.02);
    box-shadow: 0 8px 18px rgba(255,78,80,0.25);
    transition: all 0.25s ease-in-out;
  }

  img {
    border-radius: 8px;
    box-shadow: 0 6px 12px rgba(255,78,80,0.3);
    transition: transform 0.3s ease;
    max-width: 80px;
  }
  img:hover {
    transform: scale(1.1);
  }

  /* Action links styling */
  td a {
    color: #b22222;
    font-weight: 700;
    text-decoration: none;
    margin: 0 6px;
    padding: 6px 12px;
    border-radius: 20px;
    border: 2px solid #b22222;
    transition: background-color 0.3s ease, color 0.3s ease;
    display: inline-block;
  }
  td a:hover {
    background-color: #b22222;
    color: white;
    box-shadow: 0 6px 15px rgba(178,34,34,0.5);
  }

  /* Responsive table */
  @media (max-width: 900px) {
    table, thead, tbody, th, td, tr { 
      display: block; 
    }
    thead tr { 
      position: absolute;
      top: -9999px;
      left: -9999px;
    }
    tr {
      margin-bottom: 20px;
      background: #fff8e1;
      border-radius: 12px;
      padding: 10px;
      box-shadow: 0 6px 15px rgba(255,78,80,0.1);
    }
    td {
      border: none;
      padding-left: 50%;
      text-align: left;
      position: relative;
    }
    td:before {
      position: absolute;
      top: 16px;
      left: 16px;
      width: 45%;
      white-space: nowrap;
      font-weight: 700;
      color: #b22222;
    }
    td:nth-of-type(1):before { content: "ID"; }
    td:nth-of-type(2):before { content: "Title"; }
    td:nth-of-type(3):before { content: "Category"; }
    td:nth-of-type(4):before { content: "Price"; }
    td:nth-of-type(5):before { content: "Image"; }
    td:nth-of-type(6):before { content: "Featured"; }
    td:nth-of-type(7):before { content: "Active"; }
    td:nth-of-type(8):before { content: "Actions"; }
    img {
      max-width: 60px;
    }
  }
</style>
</head>
<body>
    <h2>Manage Products</h2>

    <form method="get" style="margin-bottom: 20px;">
        <label for="category">Filter by Category:</label>
        <select name="category" id="category" onchange="this.form.submit()">
            <option value="0">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat['id']); ?>" 
                    <?php if ($cat['id'] == $category_filter) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($cat['title']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <a href="add-product.php" class="add-product">+ Add New Product</a>

    <table>
        <thead>
          <tr>
              <th>ID</th>
              <th>Title</th>
              <th>Category</th>
              <th>Price</th>
              <th>Image</th>
              <th>Featured</th>
              <th>Active</th>
              <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($products->num_rows > 0): ?>
            <?php while ($row = $products->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo htmlspecialchars($row['category'] ?? "Uncategorized"); ?></td>
                <td>$<?php echo number_format($row['price'], 2); ?></td>
                <td>
                    <?php $imageUrl = getProductImageUrl($row['image_name']); ?>
                    <?php if (!empty($imageUrl)): ?>
                        <img src="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Product Image">
                    <?php else: ?>
                        No Image
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($row['featured']); ?></td>
                <td><?php echo htmlspecialchars($row['active']); ?></td>
                <td>
                    <a href="edit-products.php?id=<?php echo urlencode($row['product_id']); ?>">Edit</a> | 
                    <a href="delete-product.php?id=<?php echo urlencode($row['product_id']); ?>" 
                       onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="8" style="text-align:center; padding: 30px 0; font-weight: 700; color: #b22222;">No products found</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</body>
</html>

