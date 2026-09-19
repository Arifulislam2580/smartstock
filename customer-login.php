<?php
session_start();
include("admin/includes/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT customer_id, customer_email, customer_name, customer_address, password FROM customer_registration WHERE customer_email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['customer_logged_in'] = true;
            $_SESSION['customer_id'] = $row['customer_id'];
            $_SESSION['customer_email'] = $row['customer_email'];
            $_SESSION['customer_name'] = $row['customer_name'];
            $_SESSION['customer_address'] = $row['customer_address'];

            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "No account found!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Customer Login | SmartStock</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #ffffff, #fff8e1, #e3f2fd);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: fadeIn 1s ease-in;
        }

        .login-box {
            width: 350px;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.8s ease-out;
        }

        h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 25px;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            background: linear-gradient(to right, #fff, #f9f9f9);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        input:focus {
            border-color: #2196f3;
            box-shadow: 0 0 5px rgba(33, 150, 243, 0.5);
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(45deg, #ff5722, #ffc107, #2196f3);
            border: none;
            border-radius: 6px;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: linear-gradient(45deg, #e64a19, #ffca28, #1976d2);
        }

        .error {
            color: red;
            text-align: center;
            margin-top: 15px;
            animation: fadeIn 0.5s ease-in;
        }

        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }

        .forgot-password a {
            text-decoration: none;
            color: #007bff;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .forgot-password a:hover {
            color: #0056b3;
        }

        /* NEW: Register link style */
        .register-link {
            text-align: center;
            margin-top: 10px;
        }

        .register-link a {
            text-decoration: none;
            color: #28a745;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .register-link a:hover {
            color: #1e7e34;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @media (max-width: 480px) {
            .login-box {
                padding: 20px;
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Customer Login</h2>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>

        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

        <div class="forgot-password">
            <a href="forgot_password.php">Forgot Password?</a>
        </div>

        <!-- NEW: Register Link -->
        <div class="register-link">
            <p>Don't have an account? <a href="customer-register.php">Register Here</a></p>
        </div>

    </div>
</body>
</html>
