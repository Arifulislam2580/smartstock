<?php
session_start();
include('admin/includes/db.php');
include('admin/includes/functions.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userMessage = trim($_POST['message'] ?? '');
    if ($userMessage !== '') {
        $reply = get_chatbot_reply($conn, $userMessage);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SmartStock Assistant</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f7fb; color: #243b53; }
        .chat-shell { max-width: 760px; margin: 40px auto; background: white; border-radius: 16px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); overflow: hidden; }
        .chat-header { background: linear-gradient(135deg, #1e40af, #0369a1); color: white; padding: 20px 24px; }
        .chat-body { padding: 20px; min-height: 320px; }
        .bubble { padding: 10px 14px; border-radius: 12px; margin-bottom: 12px; max-width: 80%; }
        .user { background: #dbeafe; margin-left: auto; }
        .assistant { background: #ecfdf5; }
        form { display: flex; gap: 10px; padding: 0 20px 20px; }
        input { flex: 1; padding: 12px; border-radius: 8px; border: 1px solid #cbd5e1; }
        button { padding: 12px 16px; border: none; border-radius: 8px; background: #1e40af; color: white; cursor: pointer; }
        .note { padding: 0 20px 20px; color: #64748b; font-size: 0.95rem; }
    </style>
</head>
<body>
    <div class="chat-shell">
        <div class="chat-header">
            <h2>SmartStock AI Assistant</h2>
            <p>Ask about products, stock, orders, or support.</p>
        </div>
        <div class="chat-body">
            <div class="bubble assistant">Hello! I’m SmartStock Assistant. Ask me anything about our products or service.</div>
            <?php if (isset($userMessage, $reply)): ?>
                <div class="bubble user"><?php echo htmlspecialchars($userMessage); ?></div>
                <div class="bubble assistant"><?php echo nl2br(htmlspecialchars($reply)); ?></div>
            <?php endif; ?>
        </div>
        <form method="post">
            <input type="text" name="message" placeholder="Type your question..." required />
            <button type="submit">Send</button>
        </form>
        <div class="note">This assistant provides guided responses and can escalate complex issues to the admin team.</div>
    </div>
</body>
</html>
