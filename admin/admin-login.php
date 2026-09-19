<?php
session_start();

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_unset();
    session_destroy();
    header("Location: admin-login.php");
    exit;
}

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    header("Location: dashboard.php");
    exit;
}

require_once("includes/db.php");

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM tbl_admin WHERE username = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows == 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $row['username'];
            header("Location: dashboard.php");
            exit;
        } elseif ($password === $row['password']) {
            // Legacy plain-text password support: upgrade to bcrypt
            $newHash = password_hash($password, PASSWORD_BCRYPT);
            $update = $conn->prepare("UPDATE tbl_admin SET password = ? WHERE id = ?");
            if ($update) {
                $update->bind_param("si", $newHash, $row['id']);
                $update->execute();
                $update->close();
            }

            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $row['username'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login | SmartStock</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #ffffff, #e3f2fd);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 1s ease-in;
        }

        .container {
            display: flex;
            gap: 30px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .login-box, .contact-box {
            width: 340px;
            background: white;
            padding: 30px 25px;
            border-radius: 10px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
            animation: slideUp 1s ease forwards;
        }

        h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
            margin-bottom: 6px;
        }

        input[type="text"],
        input[type="password"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-bottom: 15px;
            background: #f9f9f9;
        }

        button {
            width: 100%;
            padding: 10px;
            background: linear-gradient(45deg, #ff5722, #ffc107, #2196f3);
            border: none;
            border-radius: 6px;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s ease;
        }

        button:hover {
            background: linear-gradient(45deg, #d84315, #ffca28, #1976d2);
        }

.error {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }

        .contact-box {
            background: linear-gradient(135deg, #ffebee, #e3f2fd, #fffde7);
            animation: pulse 3s infinite alternate;
        }

        .contact-box h3 {
            text-align: center;
            margin-bottom: 15px;
            color: #d32f2f;
        }

        .contact-box textarea {
            resize: vertical;
            height: 80px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulse {
            0% {
                background-position: 0% 50%;
            }
            100% {
                background-position: 100% 50%;
            }
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Login Form -->
    <div class="login-box">
        <h2>Admin Login</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" autocomplete="off">
            <label>Username:</label>
            <input type="text" name="username" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>
        </form>
    </div>

    <!-- Contact Box -->
    <div class="contact-box">
        <h3>Need Help?</h3>
        <p style="text-align:center; font-size:14px;">Contact the system administrator</p>
        <form method="post" action="contact.php">
<label>Your Name:</label>
        <input type="text" name="name" placeholder="Your name" required>

        <label>Your Email:</label>
        <input type="email" name="email" placeholder="you@example.com" required>

        <label>Send To:</label>
        <select name="recipient" required>
            <option value="">-- Select --</option>
            <option value="Admin">Admin</option>
            <option value="HR">HR</option>
        </select>

        <label>Message:</label>
        <textarea name="message" placeholder="Your message..." required></textarea>

            <button type="submit">Send</button>
        </form>
    </div>
</div>

</body>
</html>

