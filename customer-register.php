<?php
session_start();
include("admin/includes/db.php");

$showLogin = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);

    // Check existing email
    $stmt = $conn->prepare("SELECT customer_id FROM customer_registration WHERE customer_email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $check = $stmt->get_result();

    if ($check && $check->num_rows > 0) {
        $error = "Email already registered!";
        $showLogin = true;
    } else {

        $sql = "INSERT INTO customer_registration 
                (customer_name, customer_email, phone, customer_address, password)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $name, $email, $phone, $address, $password);

        if ($stmt->execute()) {
            $new_id = $conn->insert_id;

            $_SESSION['customer_logged_in'] = true;
            $_SESSION['customer_id'] = $new_id;
            $_SESSION['customer_email'] = $email;
            $_SESSION['customer_name'] = $name;
            $_SESSION['customer_address'] = $address;

            header("Location: index.php");
            exit;

        } else {
            $error = "Registration failed!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Register | SmartStock</title>

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
        }

        .register-box {
            width: 400px;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 25px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
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
        }

        button:hover {
            background: linear-gradient(45deg, #e64a19, #ffca28, #1976d2);
        }

        .error {
            color: red;
            text-align: center;
            margin-top: 15px;
        }

        .login-btn {
            display: block;
            text-align: center;
            margin-top: 10px;
        }

        .login-btn a button {
            width: 100%;
            padding: 10px;
            background: #28a745;
            border: none;
            border-radius: 6px;
            color: white;
            cursor: pointer;
            font-weight: bold;
        }

        .login-btn a button:hover {
            background: #218838;
        }
    </style>
</head>

<body>

<div class="register-box">

    <h2>Register</h2>

    <form method="POST">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="phone" placeholder="Phone" required>
        <input type="text" name="address" placeholder="Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Register</button>
    </form>

    <?php
    if (isset($error)) {
        echo "<p class='error'>$error</p>";
    }

    if ($showLogin) {
        echo "<div class='login-btn'>
                <a href='customer-login.php'>
                    <button type='button'>Login</button>
                </a>
              </div>";
    }
    ?>

</div>

</body>
</html>