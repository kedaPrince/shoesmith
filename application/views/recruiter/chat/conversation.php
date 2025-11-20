<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div id="main-content">
    <div class="container-fluid">
        <div class="row clearfix">
            <div class="col-12">
                <div class="card">
                    <div class="body">
                        <div class="row">
                            <!-- Conversations List (Left Sidebar) -->
                            <div class="col-md-4 col-lg-3">
                                <div class="chat-conversations-list">
                                    <div class="list-group">
                                        <?php foreach ($all_conversations as $conv): ?>
                                        <a href="<?php echo site_url('recruiter/chat/conversation/' . $conv->id); ?>"
                                            class="list-group-item list-group-item-action flex-column align-items-start conversation-item <?php echo ($conversation->id == $conv->id) ? 'active' : ''; ?>">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">
                                                    <?php echo htmlspecialchars($conv->agency_name); ?>
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

                            <!-- Chat Area (Right Side) -->
                            <div class="col-md-8 col-lg-9">
                                <div class="chat-container">
                                    <!-- Chat Header -->
                                    <div class="chat-header border-bottom p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0">
                                                    <?php echo htmlspecialchars($conversation->agency_name); ?>
                                                </h5>
                                                <small class="text-muted">
                                                    <?php echo $conversation->job_name ? 'Job: ' . htmlspecialchars($conversation->job_name) : 'General Conversation'; ?>
                                                </small>
                                            </div>
                                            <div class="chat-actions">
                                                <button class="btn btn-sm btn-outline-secondary"
                                                    title="Conversation Info">
                                                    <i class="fa fa-info-circle"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Messages Area -->
                                    <div class="chat-messages p-3" id="chatMessages"
                                        style="height: 450px; overflow-y: auto;">
                                        <?php if (!empty($messages)): ?>
                                        <?php foreach ($messages as $message): ?>
                                        <div class="message-item mb-3 <?php echo $message->sender_type == 'recruiter' ? 'message-sent' : 'message-received'; ?>"
                                            data-message-id="<?php echo $message->id; ?>">
                                            <div
                                                class="message-bubble <?php echo $message->sender_type == 'recruiter' ? 'sent' : 'received'; ?>">
                                                <div class="message-text">
                                                    <?php echo nl2br(htmlspecialchars($message->message)); ?></div>
                                                <div class="message-time text-muted">
                                                    <small><?php echo date('H:i', strtotime($message->created_at)); ?></small>
                                                    <?php if ($message->sender_type == 'recruiter'): ?>
                                                    <i
                                                        class="fa fa-check<?php echo $message->is_read ? '-double text-info' : ''; ?> ml-1"></i>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                        <div class="text-center text-muted p-4">
                                            <p>No messages yet. Start the conversation!</p>
                                        </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Message Input -->
                                    <div class="chat-input border-top p-3">
                                        <form id="messageForm">
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="messageInput"
                                                    placeholder="Type your message..." autocomplete="off" required>
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

                                    <!-- Debug / Info -->
                                    <div class="mt-2 px-3">
                                        <small class="text-muted">
                                            Conversation ID: <span
                                                id="debugConversationId"><?php echo $conversation->id; ?></span>
                                            | Last Message ID: <span
                                                id="debugLastMessageId"><?php echo !empty($messages) ? end($messages)->id : 0; ?></span>
                                            | <button class="btn btn-sm btn-outline-secondary"
                                                onclick="fetchNewMessages()">Manual Refresh</button>
                                        </small>
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

<style>
.chat-conversations-list {
    border-right: 1px solid #dee2e6;
    height: 680px;
    overflow-y: auto;
}

.conversation-item {
    border: none;
    border-bottom: 1px solid #dee2e6;
    border-radius: 0;
}

.conversation-item:hover,
.conversation-item.active {
    background-color: #f8f9fa;
}

.conversation-item.active {
    background-color: #007bff;
    color: white;
}

.chat-container {
    height: 680px;
    display: flex;
    flex-direction: column;
}

.message-item {
    display: flex;
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
    background-color: #007bff;
    color: white;
    border-bottom-right-radius: 5px;
}

.message-bubble.received {
    background-color: #f1f1f1;
    color: #333;
    border-bottom-left-radius: 5px;
}

.message-time {
    font-size: 0.75rem;
    margin-top: 5px;
    text-align: right;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Recruiter chat initializing with vanilla JS...');

    let isSending = false;
    let refreshInterval;
    let isPolling = false;
    let lastMessageId = <?php echo !empty($messages) ? end($messages)->id : 0; ?>;

    const chatMessages = document.getElementById('chatMessages');
    const messageForm = document.getElementById('messageForm');
    const messageInput = document.getElementById('messageInput');
    const conversationId = document.getElementById('conversationId');
    const debugLastMessageId = document.getElementById('debugLastMessageId');

    console.log('Recruiter chat initialized - LastMessageId:', lastMessageId);

    function scrollToBottom() {
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    }

    // Initial scroll
    setTimeout(scrollToBottom, 100);

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function addMessage(message, isOwn = false) {
        if (!chatMessages) return;

        const align = isOwn ? 'message-sent' : 'message-received';
        const bubble = isOwn ? 'sent' : 'received';
        const check = isOwn ? '<i class="fa fa-check ml-1"></i>' : '';

        const html = `
            <div class="message-item mb-3 ${align}" data-message-id="${message.id}">
                <div class="message-bubble ${bubble}">
                    <div class="message-text">${escapeHtml(message.message).replace(/\n/g, '<br>')}</div>
                    <div class="message-time text-muted">
                        <small>${message.time || 'Just now'}</small>
                        ${check}
                    </div>
                </div>
            </div>
        `;

        chatMessages.insertAdjacentHTML('beforeend', html);
        scrollToBottom();

        // Only update lastMessageId for real message IDs (not temp ones)
        if (message.id && typeof message.id === 'number' && message.id > lastMessageId) {
            lastMessageId = message.id;
            if (debugLastMessageId) {
                debugLastMessageId.textContent = lastMessageId;
            }
        }
    }

    function fetchNewMessages() {
        if (isPolling) {
            console.log('Already polling, skipping...');
            return;
        }

        const convId = conversationId ? conversationId.value : null;
        if (!convId) {
            console.error('No conversation ID found');
            return;
        }

        console.log('Recruiter fetching messages:', {
            conversationId: convId,
            lastMessageId: lastMessageId
        });

        isPolling = true;

        const formData = new FormData();
        formData.append('conversation_id', convId);
        formData.append('last_message_id', lastMessageId);

        fetch('<?php echo site_url("recruiter/chat/ajax_get_messages"); ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                console.log('Response status:', response.status, response.statusText);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(res => {
                console.log('Recruiter poll response:', res);

                if (res.success && res.has_new_messages && res.html) {
                    // Filter out any temporary messages before adding new ones
                    document.querySelectorAll('[data-message-id^="temp-"]').forEach(tempMsg => {
                        tempMsg.remove();
                    });

                    chatMessages.insertAdjacentHTML('beforeend', res.html);

                    // Make sure we're using a numeric last_message_id
                    if (res.last_message_id && !isNaN(res.last_message_id)) {
                        lastMessageId = parseInt(res.last_message_id);
                    } else if (res.last_message_id && typeof res.last_message_id === 'string' && !res
                        .last_message_id.startsWith('temp-')) {
                        lastMessageId = parseInt(res.last_message_id) || lastMessageId;
                    }

                    if (debugLastMessageId) {
                        debugLastMessageId.textContent = lastMessageId;
                    }
                    scrollToBottom();

                    console.log('New messages added:', res.message_count);
                } else {
                    console.log('No new messages available');
                }
            })
            .catch(error => {
                console.error('Error fetching messages:', error);
            })
            .finally(() => {
                isPolling = false;
            });
    }

    // Message sending - FIXED VERSION
    if (messageForm) {
        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (isSending) {
                console.log('Already sending, please wait...');
                return;
            }

            const messageText = messageInput.value.trim();
            if (!messageText) {
                alert('Please enter a message');
                return;
            }

            isSending = true;
            const submitButton = messageForm.querySelector('button[type="submit"]');
            const originalHtml = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sending...';

            const convId = conversationId ? conversationId.value : null;
            if (!convId) {
                alert('Conversation ID missing');
                submitButton.disabled = false;
                submitButton.innerHTML = originalHtml;
                return;
            }

            console.log('Sending message:', messageText);

            // Clear any existing temp messages first
            document.querySelectorAll('[data-message-id^="temp-"]').forEach(tempMsg => {
                tempMsg.remove();
            });

            // Optimistic UI update
            const tempId = 'temp-' + Date.now();
            addMessage({
                id: tempId,
                message: messageText,
                time: 'Just now'
            }, true);

            messageInput.value = '';

            // Send to server
            const formData = new FormData();
            formData.append('conversation_id', convId);
            formData.append('message', messageText);

            fetch('<?php echo site_url("recruiter/chat/ajax_send_message"); ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(response => {
                    console.log('Send response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(res => {
                    console.log('Send parsed response:', res);

                    if (res.success) {
                        // Remove temporary message
                        const tempElement = document.querySelector(`[data-message-id="${tempId}"]`);
                        if (tempElement) {
                            tempElement.remove();
                        }

                        console.log('Message sent successfully, ID:', res.message_id);

                        // Update last message ID
                        if (res.message_id && !isNaN(res.message_id)) {
                            lastMessageId = Math.max(lastMessageId, parseInt(res.message_id));
                            if (debugLastMessageId) {
                                debugLastMessageId.textContent = lastMessageId;
                            }
                        }

                        // Force immediate fetch to get the real message
                        setTimeout(fetchNewMessages, 300);
                    } else {
                        // Remove temporary message if failed
                        const tempElement = document.querySelector(`[data-message-id="${tempId}"]`);
                        if (tempElement) {
                            tempElement.remove();
                        }
                        alert('Failed to send: ' + (res.message || 'Unknown error'));
                        messageInput.value = messageText;
                    }
                })
                .catch(error => {
                    console.error('Send error:', error);
                    // Remove temporary message if error
                    const tempElement = document.querySelector(`[data-message-id="${tempId}"]`);
                    if (tempElement) {
                        tempElement.remove();
                    }
                    alert('Network error - please check connection and try again');
                    messageInput.value = messageText;
                })
                .finally(() => {
                    isSending = false;
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalHtml;
                    messageInput.focus();
                });
        });
    }

    function startPolling() {
        console.log('Starting recruiter polling interval');
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
        refreshInterval = setInterval(fetchNewMessages, 3000);
    }

    function stopPolling() {
        console.log('Stopping recruiter polling interval');
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    }

    // Start polling
    startPolling();

    // Handle page visibility
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopPolling();
        } else {
            startPolling();
            setTimeout(fetchNewMessages, 300);
        }
    });

    // Handle window focus
    window.addEventListener('focus', function() {
        console.log('Recruiter window focused');
        fetchNewMessages();
    });

    // Enter key handling
    if (messageInput) {
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (messageForm) {
                    const event = new Event('submit', {
                        bubbles: true,
                        cancelable: true
                    });
                    messageForm.dispatchEvent(event);
                }
            }
        });
    }

    // Focus input
    if (messageInput) {
        setTimeout(() => {
            messageInput.focus();
        }, 500);
    }

    // Make fetchNewMessages globally available for manual refresh
    window.fetchNewMessages = fetchNewMessages;

    console.log('Recruiter chat initialized successfully');
});
</script>