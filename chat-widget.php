<style>
    .smartstock-chat-float {
        position: fixed;
        right: 20px;
        bottom: 20px;
        z-index: 9999;
        font-family: 'Poppins', Arial, sans-serif;
    }

    .smartstock-chat-toggle {
        border: none;
        background: linear-gradient(135deg, #1e40af, #0369a1);
        color: #fff;
        padding: 14px 18px;
        border-radius: 999px;
        box-shadow: 0 10px 25px rgba(3, 105, 161, 0.3);
        cursor: pointer;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .smartstock-chat-toggle:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(3, 105, 161, 0.35);
    }

    .smartstock-chat-panel {
        position: fixed;
        right: 20px;
        bottom: 90px;
        width: min(92vw, 420px);
        height: min(72vh, 620px);
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.18);
        display: none;
        border: 1px solid #e2e8f0;
    }

    .smartstock-chat-panel.open {
        display: block;
        animation: smartstockFadeIn 0.2s ease;
    }

    .smartstock-chat-header {
        background: linear-gradient(135deg, #1e40af, #0369a1);
        color: white;
        padding: 14px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .smartstock-chat-header strong {
        font-size: 15px;
    }

    .smartstock-chat-header button {
        background: transparent;
        color: white;
        border: none;
        font-size: 18px;
        cursor: pointer;
    }

    .smartstock-chat-frame {
        width: 100%;
        height: calc(100% - 56px);
        border: none;
    }

    @keyframes smartstockFadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="smartstock-chat-float">
    <button class="smartstock-chat-toggle" type="button" onclick="toggleSmartstockChat()">
        <span>💬</span> <span>Chat with me</span>
    </button>

    <div class="smartstock-chat-panel" id="smartstockChatPanel">
        <div class="smartstock-chat-header">
            <strong>SmartStock Assistant</strong>
            <button type="button" onclick="toggleSmartstockChat()" aria-label="Close chat">×</button>
        </div>
        <iframe class="smartstock-chat-frame" src="chatbot.php" title="SmartStock Assistant"></iframe>
    </div>
</div>

<script>
    function toggleSmartstockChat() {
        const panel = document.getElementById('smartstockChatPanel');
        if (!panel) return;
        panel.classList.toggle('open');
    }
</script>
