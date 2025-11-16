<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div id="main-content">
    <div class="container-fluid">
        <div class="row clearfix">
            <div class="col-12">
                <div class="card">
                    <div class="header">
                        <h2><?php echo $heading; ?></h2>
                        <a href="<?php echo site_url('recruiter/chat/start'); ?>" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Start New Conversation
                        </a>
                    </div>
                    <div class="body">
                        <?php if (!empty($conversations)): ?>
                        <div class="row">
                            <!-- Conversations List (Left Sidebar) -->
                            <div class="col-md-4 col-lg-3">
                                <div class="chat-conversations-list">
                                    <div class="list-group">
                                        <?php foreach ($conversations as $conversation): ?>
                                        <a href="<?php echo site_url('recruiter/chat/conversation/' . $conversation->id); ?>"
                                            class="list-group-item list-group-item-action flex-column align-items-start conversation-item <?php echo ($this->uri->segment(4) == $conversation->id) ? 'active' : ''; ?>">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">
                                                    <?php echo htmlspecialchars($conversation->agency_name); ?>
                                                </h6>
                                                <small><?php echo time_ago($conversation->last_message_at); ?></small>
                                            </div>
                                            <p class="mb-1 text-truncate">
                                                <?php echo htmlspecialchars($conversation->last_message ?: 'No messages yet'); ?>
                                            </p>
                                            <?php if ($conversation->unread_count > 0): ?>
                                            <span
                                                class="badge badge-primary badge-pill"><?php echo $conversation->unread_count; ?></span>
                                            <?php endif; ?>
                                        </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Chat Area (Right Side) -->
                            <div class="col-md-8 col-lg-9">
                                <div class="chat-container">
                                    <div class="chat-welcome text-center p-5">
                                        <i class="fa fa-comments fa-4x text-muted mb-3"></i>
                                        <h4 class="text-muted">Welcome to Chat</h4>
                                        <p class="text-muted">Select a conversation from the left to start chatting</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="text-center p-4">
                            <i class="fa fa-comments fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No conversations yet. Start a new conversation with an agency.</p>
                            <a href="<?php echo site_url('recruiter/chat/start'); ?>" class="btn btn-primary">
                                Start Conversation
                            </a>
                        </div>
                        <?php endif; ?>
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

.chat-welcome {
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}
</style>