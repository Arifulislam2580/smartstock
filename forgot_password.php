<?php
include("admin/includes/db.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    $stmt = $conn->prepare("SELECT customer_id FROM customer_registration WHERE customer_email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows == 1) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE customer_registration SET password = ? WHERE customer_email = ?");
            $update_stmt->bind_param("ss", $hashed_password, $email);

            if ($update_stmt->execute()) {
                $message = "<p style='color: green;'>✅ Password has been updated successfully.</p>";
            } else {
                $message = "<p style='color: red;'>❌ Failed to update password. Please try again.</p>";
            }
        } else {
            $message = "<p style='color: red;'>❌ Passwords do not match!</p>";
        }
    } else {
        $message = "<p style='color: red;'>❌ No account found with that email.</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <style>
        body {
            background: #f7f7f7;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .box {
            background: white;
            padding: 30px;
            width: 360px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        h2 {
            text-align: center;
            color: #007bff;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-top: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            margin-top: 20px;
            padding: 12px;
            width: 100%;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }

        .message {
            margin-top: 15px;
            text-align: center;
        }

        a {
            display: block;
            text-align: center;
            margin-top: 15px;
            text-decoration: none;
            color: #333;
        }

        a:hover {
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="box">
        <h2>Reset Password</h2>
        <form method="POST">
            <input type="email" name="email" placeholder="Enter your registered email" required>
            <input type="password" name="new_password" placeholder="Enter new password" required>
            <input type="password" name="confirm_password" placeholder="Confirm new password" required>
            <button type="submit">Reset Password</button>
        </form>
        <div class="message">
            <?php echo $message; ?>
        </div>
        <a href="customer-login.php">← Back to Login</a>
    </div>
</body>
</html>
