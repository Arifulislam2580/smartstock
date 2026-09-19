<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin-login.php");
    exit;
}

require_once("includes/db.php");
//include("includes/navbar.php");

$id = intval($_GET['id'] ?? 0);
if (!$id) { die("Product not found"); }

// Fetch product
$stmt = $conn->prepare("SELECT * FROM tbl_product WHERE product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die("Product not found");
}

// Fetch categories
$categories = $conn->query("SELECT * FROM tbl_category WHERE active='Yes'");
$hasSupplierTable = $conn->query("SHOW TABLES LIKE 'tbl_supplier'");
if ($hasSupplierTable && $hasSupplierTable->num_rows > 0) {
    $suppliers = $conn->query("SELECT * FROM tbl_supplier WHERE active='Yes' ORDER BY name ASC");
} else {
    $suppliers = false;
    $conn->query("CREATE TABLE IF NOT EXISTS tbl_supplier (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        contact_person VARCHAR(150),
        phone VARCHAR(30),
        email VARCHAR(150),
        address TEXT,
        active VARCHAR(10) DEFAULT 'Yes',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $original_price = !empty($_POST['original_price']) ? floatval($_POST['original_price']) : null;
    $stock_quantity = max(0, intval($_POST['stock_quantity']));
    $category_id = intval($_POST['category_id']);
    $supplier_id = !empty($_POST['supplier_id']) ? intval($_POST['supplier_id']) : null;
    $featured = trim($_POST['featured']);
    $active = trim($_POST['active']);
    $image_source = isset($_POST['image_source']) ? trim($_POST['image_source']) : 'keep';
    $image_name = $product['image_name'];

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxFileSize = 2 * 1024 * 1024;

    if ($image_source === 'online' && !empty($_POST['image_url'])) {
        $image_url = trim($_POST['image_url']);
        if (filter_var($image_url, FILTER_VALIDATE_URL)) {
            if (!empty($image_name) && !filter_var($image_name, FILTER_VALIDATE_URL) && file_exists("../assets/images/" . $image_name)) {
                unlink("../assets/images/" . $image_name);
            }
            $image_name = $image_url;
        }
    } else if ($image_source === 'upload' && !empty($_FILES['image']['name'])) {
        $fileName = basename($_FILES['image']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $imageInfo = @getimagesize($_FILES['image']['tmp_name']);

        if (!in_array($fileExt, $allowedExtensions) || !$imageInfo) {
            die('Invalid image file. Allowed formats: jpg, jpeg, png, gif, webp.');
        }

        if ($_FILES['image']['size'] > $maxFileSize) {
            die('Image file size must be 2MB or less.');
        }

        $new_image = time() . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
        $target = "../assets/images/" . $new_image;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            if (!empty($image_name) && !filter_var($image_name, FILTER_VALIDATE_URL) && file_exists("../assets/images/" . $image_name)) {
                unlink("../assets/images/" . $image_name);
            }
            $image_name = $new_image;
        }
    }

    $sql = "UPDATE tbl_product 
            SET title=?, description=?, price=?, original_price=?, stock_quantity=?, image_name=?, category_id=?, supplier_id=?, featured=?, active=? 
            WHERE product_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssddisiissi", $title, $description, $price, $original_price, $stock_quantity, $image_name, $category_id, $supplier_id, $featured, $active, $id);

    if ($stmt->execute()) {
        header("Location: manage-products.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Product | SmartStock Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <h2>Edit Product</h2>
    <form method="post" enctype="multipart/form-data">
        <label>Title:</label>
        <input type="text" name="title" value="<?php echo htmlspecialchars($product['title'], ENT_QUOTES, 'UTF-8'); ?>" required><br>

        <label>Description:</label>
        <textarea name="description"><?php echo htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8'); ?></textarea><br>

        <label>Price:</label>
        <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($product['price'], ENT_QUOTES, 'UTF-8'); ?>" required><br>

        <label>Original Price:</label>
        <input type="number" step="0.01" name="original_price" value="<?php echo htmlspecialchars($product['original_price'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"><br>

        <label>Stock Quantity:</label>
        <input type="number" min="0" name="stock_quantity" value="<?php echo htmlspecialchars($product['stock_quantity'] ?? 0, ENT_QUOTES, 'UTF-8'); ?>"><br>

        <label>Category:</label>
        <select name="category_id" required>
            <?php while ($cat = $categories->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($cat['id'], ENT_QUOTES, 'UTF-8'); ?>" 
                    <?php if ($cat['id'] == $product['category_id']) echo "selected"; ?>>
                    <?php echo htmlspecialchars($cat['title'], ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endwhile; ?>
        </select><br>

        <label>Current Image:</label><br>
        <?php if (!empty($product['image_name'])): ?>
            <?php 
            if (filter_var($product['image_name'], FILTER_VALIDATE_URL)) {
                echo '<img src="' . htmlspecialchars($product['image_name']) . '" width="100"><br>';
            } else {
                echo '<img src="../assets/images/' . htmlspecialchars($product['image_name']) . '" width="100"><br>';
            }
            ?>
        <?php else: ?>
            No Image<br>
        <?php endif; ?>
        
        <label>Image Source:</label>
        <div style="margin-bottom: 20px;">
            <label style="display: inline-block; margin-right: 20px;">
                <input type="radio" name="image_source" value="keep" checked> Keep Current
            </label>
            <label style="display: inline-block; margin-right: 20px;">
                <input type="radio" name="image_source" value="upload"> Upload File
            </label>
            <label style="display: inline-block;">
                <input type="radio" name="image_source" value="online"> Online URL
            </label>
        </div>

        <div id="upload-section" style="display:none;">
            <label>Upload New Image:</label>
            <input type="file" name="image"><br>
        </div>

        <div id="online-section" style="display:none;">
            <label>Image URL:</label>
            <input type="text" name="image_url" placeholder="https://example.com/image.jpg"><br>
        </div>

        <label>Supplier:</label>
        <select name="supplier_id">
            <option value="">-- Select Supplier --</option>
            <?php if ($suppliers && $suppliers->num_rows > 0): ?>
                <?php while ($supplier = $suppliers->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($supplier['id'], ENT_QUOTES, 'UTF-8'); ?>" <?php if (!empty($product['supplier_id']) && $supplier['id'] == $product['supplier_id']) echo 'selected'; ?>><?php echo htmlspecialchars($supplier['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endwhile; ?>
            <?php else: ?>
                <option value="">No suppliers available. Add one first.</option>
            <?php endif; ?>
        </select><br>

        <label>Featured:</label>
        <select name="featured">
            <option value="Yes" <?php if($product['featured']=="Yes") echo "selected"; ?>>Yes</option>
            <option value="No" <?php if($product['featured']=="No") echo "selected"; ?>>No</option>
        </select><br>

        <label>Active:</label>
        <select name="active">
            <option value="Yes" <?php if($product['active']=="Yes") echo "selected"; ?>>Yes</option>
            <option value="No" <?php if($product['active']=="No") echo "selected"; ?>>No</option>
        </select><br>

        <button type="submit">Update Product</button>
    </form>
    
    <script>
        const imageSourceRadios = document.querySelectorAll('input[name="image_source"]');
        const uploadSection = document.getElementById('upload-section');
        const onlineSection = document.getElementById('online-section');

        imageSourceRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'upload') {
                    uploadSection.style.display = 'block';
                    onlineSection.style.display = 'none';
                } else if (this.value === 'online') {
                    uploadSection.style.display = 'none';
                    onlineSection.style.display = 'block';
                } else {
                    uploadSection.style.display = 'none';
                    onlineSection.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>

