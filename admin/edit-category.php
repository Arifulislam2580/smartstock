<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin-login.php");
    exit;
}

require_once("includes/db.php");

$id = intval($_GET['id'] ?? 0);
if (!$id) { die("Category not found"); }

// Fetch category
$stmt = $conn->prepare("SELECT * FROM tbl_category WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();

if (!$category) {
    die("Category not found");
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $featured = trim($_POST['featured']);
    $active = trim($_POST['active']);
    $image_source = isset($_POST['image_source']) ? trim($_POST['image_source']) : 'keep';
    $image_name = $category['image_name'];

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

    $sql = "UPDATE tbl_category 
            SET title=?, image_name=?, featured=?, active=? 
            WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $title, $image_name, $featured, $active, $id);

    if ($stmt->execute()) {
        header("Location: manage-categories.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Category | SmartStock Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <h2>Edit Category</h2>
    <form method="post" enctype="multipart/form-data">
        <label>Title:</label>
        <input type="text" name="title" value="<?php echo htmlspecialchars($category['title'], ENT_QUOTES, 'UTF-8'); ?>" required><br>

        <label>Current Image:</label><br>
        <?php if (!empty($category['image_name'])): ?>
            <?php 
            if (filter_var($category['image_name'], FILTER_VALIDATE_URL)) {
                echo '<img src="' . htmlspecialchars($category['image_name']) . '" width="100"><br>';
            } else {
                echo '<img src="../assets/images/' . htmlspecialchars($category['image_name']) . '" width="100"><br>';
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

        <label>Featured:</label>
        <select name="featured">
            <option value="Yes" <?php if($category['featured']=="Yes") echo "selected"; ?>>Yes</option>
            <option value="No" <?php if($category['featured']=="No") echo "selected"; ?>>No</option>
        </select><br>

        <label>Active:</label>
        <select name="active">
            <option value="Yes" <?php if($category['active']=="Yes") echo "selected"; ?>>Yes</option>
            <option value="No" <?php if($category['active']=="No") echo "selected"; ?>>No</option>
        </select><br>

        <button type="submit">Update Category</button>
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
