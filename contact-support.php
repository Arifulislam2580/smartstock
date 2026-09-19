<?php
session_start();
include('admin/includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name !== '' && $email !== '' && $message !== '') {
        $stmt = $conn->prepare("INSERT INTO customers_sms (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $subject = 'Support Request';
        $stmt->bind_param('ssss', $name, $email, $subject, $message);
        $stmt->execute();
        $success = 'Your message has been sent successfully. We will get back to you soon.';
    } else {
        $error = 'Please fill out all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Contact Support | SmartStock</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f8fafc; }
        .wrap { max-width: 700px; margin: 40px auto; background: white; padding: 28px; border-radius: 16px; box-shadow: 0 12px 30px rgba(0,0,0,0.08); }
        h2 { margin-top: 0; color: #1e40af; }
        label { display: block; margin-top: 12px; font-weight: 600; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; margin-top: 6px; }
        button { margin-top: 16px; padding: 12px 16px; border: none; border-radius: 8px; background: #1e40af; color: white; cursor: pointer; }
        .success { color: green; margin-top: 12px; }
        .error { color: red; margin-top: 12px; }
    </style>
</head>
<body>
    <div class="wrap">
        <h2>Contact Support</h2>
        <p>Need help with your order or want to send feedback? Use the form below.</p>
        <form method="post">
            <label>Name</label>
            <input type="text" name="name" required />
            <label>Email</label>
            <input type="email" name="email" required />
            <label>Message</label>
            <textarea name="message" rows="6" required></textarea>
            <button type="submit">Send Message</button>
        </form>
        <?php if (!empty($success)) echo '<div class="success">' . htmlspecialchars($success) . '</div>'; ?>
        <?php if (!empty($error)) echo '<div class="error">' . htmlspecialchars($error) . '</div>'; ?>
    </div>
</body>
</html>
