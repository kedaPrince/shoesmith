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
                                                    <?php echo htmlspecialchars($conversation->agency_name); ?></h5>
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
                                        <div
                                            class="message-item mb-3 <?php echo $message->sender_type == 'recruiter' ? 'message-sent' : 'message-received'; ?>">
                                            <div
                                                class="message-bubble <?php echo $message->sender_type == 'recruiter' ? 'sent' : 'received'; ?>">
                                                <div class="message-text">
                                                    <?php echo htmlspecialchars($message->message); ?></div>
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
                                                    placeholder="Type your message..." required>
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
</style>

<script>
$(document).ready(function() {
    // Auto-scroll to bottom of messages
    function scrollToBottom() {
        var messagesContainer = $('#chatMessages');
        messagesContainer.scrollTop(messagesContainer[0].scrollHeight);
    }

    scrollToBottom();

    // Send message
    $('#messageForm').on('submit', function(e) {
        e.preventDefault();

        var message = $('#messageInput').val().trim();
        var conversationId = $('#conversationId').val();

        if (message === '') return;

        $.ajax({
            url: '<?php echo site_url("recruiter/chat/ajax_send_message"); ?>',
            type: 'POST',
            data: {
                conversation_id: conversationId,
                message: message
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#messageInput').val('');
                    location.reload();
                } else {
                    alert('Failed to send message: ' + response.message);
                }
            },
            error: function() {
                alert('Error sending message');
            }
        });
    });
});
</script>