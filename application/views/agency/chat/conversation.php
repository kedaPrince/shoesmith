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
                                        <a href="<?php echo site_url('agency/chat/conversation/' . $conv->id); ?>"
                                            class="list-group-item list-group-item-action flex-column align-items-start conversation-item <?php echo ($conversation->id == $conv->id) ? 'active' : ''; ?>">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">
                                                    <?php echo htmlspecialchars($conv->recruiter_name); ?>
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
                                                    <?php echo htmlspecialchars($conversation->recruiter_name); ?></h5>
                                                <small
                                                    class="text-muted"><?php echo $conversation->job_name ? 'Job: ' . htmlspecialchars($conversation->job_name) : 'General Conversation'; ?></small>
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
                                        style="height: 400px; overflow-y: auto;">
                                        <?php if (!empty($messages)): ?>
                                        <?php foreach ($messages as $message): ?>
                                        <div class="message-item mb-3 <?php echo $message->sender_type == 'agency' ? 'message-sent' : 'message-received'; ?>"
                                            data-message-id="<?php echo $message->id; ?>">
                                            <div
                                                class="message-bubble <?php echo $message->sender_type == 'agency' ? 'sent' : 'received'; ?>">
                                                <div class="message-text">
                                                    <?php echo htmlspecialchars($message->message); ?></div>
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
                                                    placeholder="Type your message..." required>
                                                <input type="hidden" id="conversationId"
                                                    value="<?php echo $conversation->id; ?>">
                                                <input type="hidden" id="lastMessageId"
                                                    value="<?php echo !empty($messages) ? end($messages)->id : 0; ?>">
                                                <div class="input-group-append">
                                                    <button type="submit" class="btn btn-primary" id="sendButton">
                                                        <i class="fa fa-paper-plane"></i> Send
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <!-- Add this after the message input form for debugging -->
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            Last Message ID: <span
                                                id="debugLastMessageId"><?php echo !empty($messages) ? end($messages)->id : 0; ?></span>
                                            | Conversation ID: <span
                                                id="debugConversationId"><?php echo $conversation->id; ?></span>
                                            | <button
                                                class="btn btn-sm btn-outline-secondary refresh-messages">Refresh</button>
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
    height: 600px;
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
    height: 600px;
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

#sendButton:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}
</style>

<script>
$(document).ready(function() {
    let isSending = false;
    let refreshInterval;
    let isPolling = false;

    // Auto-scroll to bottom of messages
    function scrollToBottom() {
        const messagesContainer = $('#chatMessages');
        messagesContainer.scrollTop(messagesContainer[0].scrollHeight);
    }

    scrollToBottom();

    // Function to add new message to chat
    function addMessageToChat(message, isOwnMessage = false) {
        const messageClass = isOwnMessage ? 'message-sent' : 'message-received';
        const bubbleClass = isOwnMessage ? 'sent' : 'received';
        const checkIcon = isOwnMessage ? '<i class="fa fa-check ml-1"></i>' : '';

        const messageHtml = `
                <div class="message-item mb-3 ${messageClass}" data-message-id="${message.id}">
                    <div class="message-bubble ${bubbleClass}">
                        <div class="message-text">${escapeHtml(message.message)}</div>
                        <div class="message-time text-muted">
                            <small>${message.time}</small>
                            ${checkIcon}
                        </div>
                    </div>
                </div>
            `;

        $('#chatMessages').append(messageHtml);
        scrollToBottom();
        updateLastMessageId(message.id);
    }

    // Function to escape HTML to prevent XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Function to update last message ID
    function updateLastMessageId(messageId) {
        $('#lastMessageId').val(messageId);
        console.log('Last message ID updated to:', messageId);
    }

    // Function to fetch new messages
    function fetchNewMessages() {
        if (isPolling) {
            console.log('Already polling, skipping...');
            return;
        }

        const conversationId = $('#conversationId').val();
        const lastMessageId = $('#lastMessageId').val();

        console.log('Fetching new messages...', {
            conversationId,
            lastMessageId
        });

        isPolling = true;

        $.ajax({
            url: '<?php echo site_url("agency/chat/ajax_get_messages"); ?>',
            type: 'POST',
            data: {
                conversation_id: conversationId,
                last_message_id: lastMessageId
            },
            dataType: 'json',
            success: function(response) {
                console.log('Poll response:', response);

                if (response.success) {
                    if (response.has_new_messages && response.html) {
                        console.log('Adding new messages to chat');
                        // Update last message ID
                        updateLastMessageId(response.last_message_id);

                        // Add new messages to chat
                        $('#chatMessages').append(response.html);
                        scrollToBottom();

                        // Show notification for new messages
                        if (response.html.includes('message-received')) {
                            showNewMessageNotification();
                        }
                    } else {
                        console.log('No new messages');
                    }
                } else {
                    console.error('Poll error:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching messages:', error);
            },
            complete: function() {
                isPolling = false;
            }
        });
    }

    // Show notification for new messages
    function showNewMessageNotification() {
        // You can add a subtle notification here
        const notification = document.createElement('div');
        notification.className = 'alert alert-info alert-dismissible fade show';
        notification.innerHTML = `
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                New message received!
            `;
        document.querySelector('.chat-container').prepend(notification);

        // Auto remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Send message
    $('#messageForm').on('submit', function(e) {
        e.preventDefault();

        if (isSending) return;

        const message = $('#messageInput').val().trim();
        const conversationId = $('#conversationId').val();

        if (message === '') return;

        isSending = true;
        const sendButton = $('#sendButton');
        const originalText = sendButton.html();

        // Disable send button and show loading
        sendButton.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');

        // Add message immediately to chat (optimistic update)
        const tempMessage = {
            id: 'temp-' + Date.now(),
            message: message,
            time: 'Just now',
            sender_type: 'agency'
        };
        addMessageToChat(tempMessage, true);

        // Clear input
        $('#messageInput').val('');

        // Send to server
        $.ajax({
            url: '<?php echo site_url("agency/chat/ajax_send_message"); ?>',
            type: 'POST',
            data: {
                conversation_id: conversationId,
                message: message
            },
            dataType: 'json',
            success: function(response) {
                console.log('Send response:', response);

                if (response.success) {
                    // Remove temporary message and add real one
                    $(`[data-message-id="${tempMessage.id}"]`).remove();

                    const realMessage = {
                        id: response.message_id,
                        message: message,
                        time: 'Just now',
                        sender_type: 'agency'
                    };
                    addMessageToChat(realMessage, true);

                    // Force fetch new messages after sending
                    setTimeout(fetchNewMessages, 1000);
                } else {
                    // Remove temporary message if failed
                    $(`[data-message-id="${tempMessage.id}"]`).remove();
                    alert('Failed to send message: ' + response.message);
                    $('#messageInput').val(message); // Restore message
                }
            },
            error: function(xhr, status, error) {
                // Remove temporary message if error
                $(`[data-message-id="${tempMessage.id}"]`).remove();
                alert('Error sending message. Please try again.');
                $('#messageInput').val(message); // Restore message
                console.error('Send error:', error);
            },
            complete: function() {
                isSending = false;
                sendButton.prop('disabled', false).html(originalText);
            }
        });
    });

    // Auto-refresh messages every 2 seconds (more frequent)
    function startAutoRefresh() {
        console.log('Starting auto-refresh');
        refreshInterval = setInterval(fetchNewMessages, 2000);
    }

    // Stop auto-refresh
    function stopAutoRefresh() {
        console.log('Stopping auto-refresh');
        clearInterval(refreshInterval);
    }

    // Handle page visibility changes
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopAutoRefresh();
        } else {
            startAutoRefresh();
            // Immediately check for new messages when tab becomes visible
            setTimeout(fetchNewMessages, 500);
        }
    });

    // Start auto-refresh
    startAutoRefresh();

    // Also fetch messages when window gains focus
    $(window).on('focus', function() {
        console.log('Window focused, fetching messages');
        fetchNewMessages();
    });

    // Handle Enter key to send message
    $('#messageInput').on('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            $('#messageForm').submit();
        }
    });

    // Manual refresh button (optional - you can add this to your UI)
    $(document).on('click', '.refresh-messages', function() {
        fetchNewMessages();
    });
});
</script>