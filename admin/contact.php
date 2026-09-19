<?php
require_once('includes/db.php');
// include("includes/navbar.php");

$successMessage = "";
$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize & assign inputs
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $recipient = trim($_POST['recipient'] ?? '');
    $userMessage = trim($_POST['message'] ?? '');

    if ($name && $email && $recipient && $userMessage) {
        $message = "[To: $recipient] " . $userMessage;

        // Insert into database with timestamp
        $sql = "INSERT INTO contact_messages (name, email, message, submitted_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("sss", $name, $email, $message);
            $stmt->execute();
            $successMessage = "Message sent successfully to $recipient!";
        } else {
            $errorMessage = "Database error: Failed to prepare statement.";
        }
    } else {
        $errorMessage = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Contact</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            background: #f2f2f2;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        form {
            width: 60%;
            margin: 30px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        button {
            margin-top: 20px;
            padding: 12px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            text-align: center;
            margin-top: 20px;
        }
        .message.success {
            color: green;
        }
        .message.error {
            color: red;
        }
    </style>
</head>
<body>

    <h2>Contact Form</h2>

    <?php if ($successMessage): ?>
        <p class="message success"><?php echo $successMessage; ?></p>
    <?php elseif ($errorMessage): ?>
        <p class="message error"><?php echo $errorMessage; ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Name:</label>
        <input type="text" name="name" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Send To:</label>
        <select name="recipient" required>
            <option value="">-- Select --</option>
            <option value="Admin">Admin</option>
            <option value="HR">HR</option>
        </select>

        <label>Message:</label>
        <textarea name="message" rows="5" required></textarea>

        <button type="submit">Send</button>
    </form>

</body>
</html>

