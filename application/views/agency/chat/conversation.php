<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div id="main-content">
    <div class="container-fluid">
        <div class="row clearfix">
            <div class="col-12">
                <div class="card">
                    <div class="body">
                        <div class="row">
                            <!-- Sidebar -->
                            <div class="col-md-4 col-lg-3">
                                <div class="chat-conversations-list">
                                    <div class="list-group">
                                        <?php foreach ($all_conversations as $conv): ?>
                                        <a href="<?php echo site_url('agency/chat/conversation/' . $conv->id); ?>"
                                            class="list-group-item list-group-item-action flex-column align-items-start conversation-item <?php echo ($conversation->id == $conv->id) ? 'active' : ''; ?>">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($conv->recruiter_name); ?>
                                                </h6>
                                                <small><?php echo time_ago($conv->last_message_at); ?></small>
                                            </div>
                                            <p class="mb-1 text-truncate">
                                                <?php echo htmlspecialchars($conv->last_message ?: 'No messages yet'); ?>
                                            </p>
                                            <?php if ($conv->unread_count > 0): ?>
                                            <span
                                                class="badge badge-primary badge-pill"><?php echo $conv->unread_count; ?></span>
                                            <?php endif; ?>
                                        </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Chat Area -->
                            <div class="col-md-8 col-lg-9">
                                <div class="chat-container">
                                    <div class="chat-header border-bottom p-3">
                                        <h5 class="mb-0"><?php echo htmlspecialchars($conversation->recruiter_name); ?>
                                        </h5>
                                        <small class="text-muted">
                                            <?php echo $conversation->job_name ? 'Job: ' . htmlspecialchars($conversation->job_name) : 'General Conversation'; ?>
                                        </small>
                                    </div>

                                    <div class="chat-messages p-3" id="chatMessages"
                                        style="height: 480px; overflow-y: auto;">
                                        <?php foreach ($messages as $message): ?>
                                        <div class="message-item mb-3 <?php echo $message->sender_type == 'agency' ? 'message-sent' : 'message-received'; ?>"
                                            data-message-id="<?php echo $message->id; ?>">
                                            <div
                                                class="message-bubble <?php echo $message->sender_type == 'agency' ? 'sent' : 'received'; ?>">
                                                <div class="message-text">
                                                    <?php echo nl2br(htmlspecialchars($message->message)); ?></div>
                                                <div class="message-time text-muted">
                                                    <small><?php echo date('H:i', strtotime($message->created_at)); ?></small>
                                                    <?php if ($message->sender_type == 'agency'): ?>
                                                    <i
                                                        class="fa fa-check<?php echo $message->is_read ? '-double text-info' : ''; ?> ml-1"></i>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="chat-input border-top p-3">
                                        <form id="messageForm">
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="messageInput"
                                                    placeholder="Type a message..." autocomplete="off">
                                                <input type="hidden" id="conversationId"
                                                    value="<?php echo $conversation->id; ?>">
                                                <div class="input-group-append">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fa fa-paper-plane"></i> Send
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Add this debug section to both agency and recruiter conversation views -->
<div class="debug-info p-2 bg-light border-top">
    <small class="text-muted">
        Debug:
        Conversation ID: <span id="debugConversationId"><?php echo $conversation->id; ?></span> |
        Last Message ID: <span id="debugLastMessageId"><?php echo !empty($messages) ? end($messages)->id : 0; ?></span>
        |
        <button class="btn btn-sm btn-outline-secondary" onclick="fetchNewMessages()">Manual Refresh</button>
    </small>
</div>
<style>
.chat-messages {
    background: #fafafa;
}

.message-sent {
    justify-content: flex-end;
}

.message-received {
    justify-content: flex-start;
}

.message-bubble {
    max-width: 70%;
    padding: 10px 15px;
    border-radius: 18px;
    margin-bottom: 5px;
}

.message-bubble.sent {
    background: #007bff;
    color: white;
    border-bottom-right-radius: 4px;
}

.message-bubble.received {
    background: #e9ecef;
    color: #333;
    border-bottom-left-radius: 4px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Agency chat initializing with vanilla JS...');

    let isSending = false;
    let pollingInterval;
    let lastMessageId = <?php echo !empty($messages) ? end($messages)->id : 0; ?>;

    const chatMessages = document.getElementById('chatMessages');
    const messageForm = document.getElementById('messageForm');
    const messageInput = document.getElementById('messageInput');
    const conversationId = document.getElementById('conversationId');

    console.log('Agency chat initialized - LastMessageId:', lastMessageId);

    function scrollToBottom() {
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    }

    scrollToBottom();

    function fetchNewMessages() {
        console.log('Agency fetching messages - LastMessageId:', lastMessageId);

        const convId = conversationId ? conversationId.value : null;
        if (!convId) return;

        const formData = new FormData();
        formData.append('conversation_id', convId);
        formData.append('last_message_id', lastMessageId);

        // UPDATED: Add proper headers and credentials
        fetch('<?php echo site_url("agency/chat/ajax_get_messages"); ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                console.log('Agency response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(res => {
                console.log('Agency poll response:', res);
                if (res.success && res.has_new_messages && res.html) {
                    chatMessages.insertAdjacentHTML('beforeend', res.html);
                    lastMessageId = res.last_message_id;
                    scrollToBottom();
                }
            })
            .catch(error => {
                console.error('Agency poll error:', error);
            });
    }

    if (messageForm) {
        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (isSending) return;

            const msg = messageInput.value.trim();
            if (!msg) return;

            isSending = true;
            const submitButton = messageForm.querySelector('button[type="submit"]');
            const originalHtml = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';

            const convId = conversationId ? conversationId.value : null;

            // Optimistic UI
            const tempId = 'tmp' + Date.now();
            const tempHtml = `<div class="message-item mb-3 message-sent" data-message-id="${tempId}">
                <div class="message-bubble sent">
                    <div class="message-text">${msg.replace(/\n/g, '<br>')}</div>
                    <div class="message-time text-muted"><small>Just now</small></div>
                </div>
            </div>`;

            chatMessages.insertAdjacentHTML('beforeend', tempHtml);
            scrollToBottom();
            messageInput.value = '';

            const formData = new FormData();
            formData.append('conversation_id', convId);
            formData.append('message', msg);

            // UPDATED: Add proper headers and credentials
            fetch('<?php echo site_url("agency/chat/ajax_send_message"); ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(response => {
                    console.log('Agency send response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(res => {
                    if (res.success) {
                        const tempElement = document.querySelector(`[data-message-id="${tempId}"]`);
                        if (tempElement) {
                            tempElement.remove();
                        }
                        // Force refresh to get the real message with proper ID
                        setTimeout(fetchNewMessages, 500);
                    } else {
                        alert('Failed to send');
                        const tempElement = document.querySelector(`[data-message-id="${tempId}"]`);
                        if (tempElement) {
                            tempElement.remove();
                        }
                        messageInput.value = msg;
                    }
                })
                .catch(error => {
                    alert('Network error');
                    const tempElement = document.querySelector(`[data-message-id="${tempId}"]`);
                    if (tempElement) {
                        tempElement.remove();
                    }
                    messageInput.value = msg;
                })
                .finally(() => {
                    isSending = false;
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalHtml;
                });
        });
    }

    // Poll every 2 seconds
    pollingInterval = setInterval(fetchNewMessages, 2000);

    // Pause when tab hidden
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(pollingInterval);
        } else {
            clearInterval(pollingInterval);
            pollingInterval = setInterval(fetchNewMessages, 2000);
            setTimeout(fetchNewMessages, 500);
        }
    });

    // Enter = send
    if (messageInput) {
        messageInput.addEventListener('keydown', e => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (messageForm) {
                    messageForm.dispatchEvent(new Event('submit'));
                }
            }
        });
    }

    // Focus on input
    if (messageInput) {
        messageInput.focus();
    }

    // Make function available for manual refresh
    window.fetchNewMessages = fetchNewMessages;
});
</script>