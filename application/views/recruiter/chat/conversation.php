<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<div id="main-content">
    <!-- Alternative Glass Morphism Design -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Enhanced Glass Breadcrumb with Notifications -->
                <nav class="glass-breadcrumb" aria-label="breadcrumb">
                    <div class="glass-container">
                        <div class="breadcrumb-path">
                            <a href="<?php echo site_url('recruiter/dashboard'); ?>" class="path-item">
                                <i class="fad fa-analytics"></i>
                                <span>Dashboard</span>
                            </a>
                            <div class="path-arrow">
                                <i class="fad fa-arrow-right"></i>
                            </div>
                            <div class="path-item position-relative">
                                <i class="fad fa-comments-alt"></i>
                                <span>Messages</span>
                                <?php if (isset($total_unread_count) && $total_unread_count > 0): ?>
                                <span class="notification-badge" id="globalNotificationBadge">
                                    <?php echo $total_unread_count > 99 ? '99+' : $total_unread_count; ?>
                                </span>
                                <?php endif; ?>
                            </div>

                            <?php if (isset($conversation)): ?>
                            <div class="path-arrow">
                                <i class="fad fa-arrow-right"></i>
                            </div>
                            <div class="current-chat">
                                <div
                                    class="chat-indicator <?php echo (isset($conversation->is_online) && $conversation->is_online) ? 'online' : 'offline'; ?>">
                                </div>
                                <div class="chat-details">
                                    <strong><?php echo htmlspecialchars($conversation->agency_name); ?></strong>
                                    <span><?php echo $conversation->job_name ? 'Job: ' . htmlspecialchars($conversation->job_name) : 'Direct Message'; ?></span>
                                </div>
                                <?php if (isset($conversation) && isset($conversation->unread_count) && $conversation->unread_count > 0): ?>
                                <span class="conversation-notification-badge">
                                    <?php echo $conversation->unread_count; ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="breadcrumb-stats">
                            <!-- Notification Bell Dropdown -->
                            <div class="notification-dropdown">
                                <button class="notification-bell btn btn-light position-relative" id="notificationBell">
                                    <i class="fad fa-bell"></i>
                                    <?php if (isset($total_unread_count) && $total_unread_count > 0): ?>
                                    <span class="notification-indicator"></span>
                                    <?php endif; ?>
                                </button>
                                <div class="notification-dropdown-menu" id="notificationDropdown">
                                    <div class="notification-header">
                                        <h6>Recent Notifications</h6>
                                        <a href="<?php echo site_url('recruiter/notifications'); ?>"
                                            class="view-all">View All</a>
                                    </div>
                                    <div class="notification-list" id="notificationList">
                                        <?php if (!empty($recent_notifications)): ?>
                                        <?php foreach ($recent_notifications as $notification): ?>
                                        <div class="notification-item <?php echo !$notification->is_read ? 'unread' : ''; ?>"
                                            data-notification-id="<?php echo $notification->id; ?>">
                                            <div class="notification-icon">
                                                <i class="fad fa-comment-alt"></i>
                                            </div>
                                            <div class="notification-content">
                                                <p class="notification-message">
                                                    <?php echo htmlspecialchars($notification->message); ?></p>
                                                <small
                                                    class="notification-time"><?php echo time_ago($notification->created_at); ?></small>
                                            </div>
                                            <?php if (!$notification->is_read): ?>
                                            <div class="notification-status"></div>
                                            <?php endif; ?>
                                        </div>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                        <div class="notification-empty">
                                            <i class="fad fa-bell-slash"></i>
                                            <p>No new notifications</p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="stat-item">
                                <i class="fad fa-clock"></i>
                                <span id="onlineStatus">Active now</span>
                            </div>
                            <div class="stat-item">
                                <i class="fad fa-message"></i>
                                <span
                                    id="messageCount"><?php echo isset($total_message_count) ? $total_message_count : 0; ?>
                                    messages</span>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>

    <div class="container-fluid p-0" style="height: 64vh; overflow: hidden;">
        <div class="row no-gutters" style="height: 100%;">
            <!-- Left Sidebar: Conversations & Agencies List -->
            <div class="col-md-4 col-lg-3"
                style="background-color: #202225; border-right: 1px solid #36393f; height: 100%; display: flex; flex-direction: column;">

                <!-- Search Box -->
                <div class="p-3 border-bottom" style="border-color: #36393f;">
                    <div class="input-group" style="background-color: #2f3136; border-radius: 8px; padding: 2px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="background-color: transparent; border: none;">
                                <i class="fa fa-search text-muted"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control" placeholder="Find or start a conversation"
                            style="background-color: transparent; border: none; color: #ffffff; font-size: 0.9rem;"
                            autocomplete="off">
                    </div>
                </div>

                <!-- Enhanced Sidebar with Notifications -->
                <div class="flex-grow-1 overflow-auto" style="padding: 0 10px;">
                    <!-- Active Conversations with Notifications -->
                    <div class="sidebar-section">
                        <h6 class="text-muted px-3 py-2" style="font-size: 0.8rem; letter-spacing: 0.5px;">
                            ACTIVE CHATS
                            <?php if (isset($total_unread_count) && $total_unread_count > 0): ?>
                            <span class="section-badge"><?php echo $total_unread_count; ?></span>
                            <?php endif; ?>
                        </h6>
                        <div class="list-group" style="background-color: transparent;">
                            <?php foreach ($all_conversations as $conv): ?>
                            <a href="<?php echo site_url('recruiter/chat/conversation/' . $conv->id); ?>"
                                class="list-group-item list-group-item-action d-flex align-items-center conversation-item <?php echo (isset($conversation) && $conversation->id == $conv->id) ? 'active' : ''; ?>"
                                style="border: none; border-radius: 8px; margin-bottom: 5px; padding: 10px 15px; transition: all 0.2s;"
                                data-conversation-id="<?php echo $conv->id; ?>">
                                <div class="conversation-avatar mr-3 position-relative">
                                    <div class="avatar"
                                        style="width: 40px; height: 40px; border-radius: 50%; background-color: #4e5058; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #ffffff;">
                                        <?php echo substr(htmlspecialchars(isset($conv->agency_name) ? $conv->agency_name : '?'), 0, 1); ?>
                                    </div>
                                    <?php if (isset($conv->is_online) && $conv->is_online): ?>
                                    <div class="online-indicator"></div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0" style="color: #ffffff; font-weight: 500;">
                                        <?php echo htmlspecialchars(isset($conv->agency_name) ? $conv->agency_name : 'Unknown Agency'); ?>
                                    </h6>
                                    <small class="text-muted d-block conversation-preview"
                                        style="font-size: 0.75rem; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                        data-conversation-id="<?php echo $conv->id; ?>">
                                        <?php if (isset($conv->unread_count) && $conv->unread_count > 0): ?>
                                        <strong><?php echo htmlspecialchars(isset($conv->last_message) && $conv->last_message ? $conv->last_message : 'New message'); ?></strong>
                                        <?php else: ?>
                                        <?php echo htmlspecialchars(isset($conv->last_message) && $conv->last_message ? $conv->last_message : 'No messages yet'); ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <div class="text-right ml-2">
                                    <small class="text-muted d-block conversation-time" style="font-size: 0.7rem;"
                                        data-conversation-id="<?php echo $conv->id; ?>">
                                        <?php echo time_ago(isset($conv->last_message_at) ? $conv->last_message_at : $conv->created_at); ?>
                                    </small>
                                    <?php if (isset($conv->unread_count) && $conv->unread_count > 0): ?>
                                    <span class="conversation-badge badge badge-primary badge-pill"
                                        data-conversation-id="<?php echo $conv->id; ?>"
                                        style="background-color: #5865F2; font-size: 0.7rem; padding: 2px 6px; margin-top: 2px;">
                                        <?php echo $conv->unread_count; ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Available Agencies with Notification Status -->
                    <?php if (!empty($available_agencies)): ?>
                    <div class="sidebar-section">
                        <h6 class="text-muted px-3 py-2 mt-3" style="font-size: 0.8rem; letter-spacing: 0.5px;">
                            AVAILABLE AGENCIES
                            <span class="section-badge new">New</span>
                        </h6>
                        <div class="list-group" style="background-color: transparent;">
                            <?php foreach ($available_agencies as $agency): ?>
                            <?php 
                            // Check if this agency already has a conversation
                            $has_conversation = false;
                            $agency_conversation = null;
                            foreach ($all_conversations as $conv) {
                                if ($conv->agency_id == $agency->id) {
                                    $has_conversation = true;
                                    $agency_conversation = $conv;
                                    break;
                                }
                            }
                            ?>

                            <?php if (!$has_conversation): ?>
                            <a href="<?php echo site_url('recruiter/chat/quick_start/' . $agency->id); ?>"
                                class="list-group-item list-group-item-action d-flex align-items-center agency-item"
                                style="border: none; border-radius: 8px; margin-bottom: 5px; padding: 10px 15px; transition: all 0.2s; border-left: 3px solid #25D366 !important;">
                                <div class="agency-avatar mr-3 position-relative">
                                    <div class="avatar"
                                        style="width: 40px; height: 40px; border-radius: 50%; background-color: #25D366; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #ffffff;">
                                        <?php echo substr(htmlspecialchars($agency->name), 0, 1); ?>
                                    </div>
                                    <div class="online-indicator"></div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0" style="color: #ffffff; font-weight: 500;">
                                        <?php echo htmlspecialchars($agency->name); ?>
                                    </h6>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">
                                        <span class="text-success">● Online</span> - Click to start chat
                                    </small>
                                </div>
                                <div class="text-right ml-2">
                                    <i class="fa fa-plus text-success"></i>
                                </div>
                            </a>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Main Chat Area -->
            <div class="col-md-8 col-lg-9"
                style="background-color: #e5ddd5; display: flex; flex-direction: column; height: 100%; background-image: url('data:image/svg+xml,%3Csvg width=\"
                100\" height=\"100\" viewBox=\"0 0 100 100\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cpath d=\"M11
                18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7
                3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0
                3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3
                1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79
                4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4
                4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4
                1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24
                5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5
                5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2
                .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895
                2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill=\"%2391a29e\" fill-opacity=\"0.1\"
                fill-rule=\"evenodd\"/%3E%3C/svg%3E');">

                <?php if (isset($conversation)): ?>
                <!-- Chat Header -->
                <div class="d-flex align-items-center justify-content-between p-3 border-bottom"
                    style="border-color: #e0e0e0; background-color: #f0f0f0; height: 60px; flex-shrink: 0; min-height: 60px;">
                    <div class="d-flex align-items-center">
                        <div class="avatar mr-3"
                            style="width: 40px; height: 40px; border-radius: 50%; background-color: #128C7E; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #ffffff; font-size: 1.2rem;">
                            <?php echo substr(htmlspecialchars($conversation->agency_name), 0, 1); ?>
                        </div>
                        <div>
                            <h5 class="mb-0" style="color: #3b4a54; font-weight: 500; font-size: 1rem;">
                                <?php echo htmlspecialchars($conversation->agency_name); ?></h5>
                            <small class="text-muted" style="font-size: 0.75rem;">
                                <?php echo $conversation->job_name ? 'Job: ' . htmlspecialchars($conversation->job_name) : 'Online'; ?>
                            </small>
                        </div>
                    </div>
                    <div class="chat-actions d-flex">
                        <button class="btn btn-sm btn-outline-secondary mr-2" title="Call"
                            style="border-color: #cccccc; color: #54656f; padding: 4px 8px;">
                            <i class="fa fa-phone"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary mr-2" title="Video Call"
                            style="border-color: #cccccc; color: #54656f; padding: 4px 8px;">
                            <i class="fa fa-video"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary mr-2" title="Info"
                            style="border-color: #cccccc; color: #54656f; padding: 4px 8px;">
                            <i class="fa fa-info-circle"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" title="Search"
                            style="border-color: #cccccc; color: #54656f; padding: 4px 8px;">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>

                <!-- Messages Area - WhatsApp Style -->
                <div class="flex-grow-1 overflow-auto p-2" id="chatMessages"
                    style="display: block; padding: 8px 12px; position: relative; height: calc(64vh - 120px); overflow-y: auto;">
                    <?php if (!empty($messages)): ?>
                    <?php foreach ($messages as $message): ?>
                    <div
                        class="d-flex <?php echo $message->sender_type == 'recruiter' ? 'justify-content-end' : 'justify-content-start'; ?> mb-2">
                        <div class="message-container" style="max-width: 70%;">
                            <div
                                class="message-content d-flex align-items-baseline <?php echo $message->sender_type == 'recruiter' ? 'justify-content-end' : 'justify-content-start'; ?>">
                                <!-- Message Text and Time in same line -->
                                <div class="message-text-time d-inline-flex align-items-baseline" style="background-color: <?php echo $message->sender_type == 'recruiter' ? '#dcf8c6' : '#ffffff'; ?>; 
                                    padding: 8px 12px; 
                                    border-radius: 7.5px;
                                    box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);">

                                    <!-- Message Text -->
                                    <span class="message-text"
                                        style="font-size: 14.2px; color: #303030; line-height: 1.3; margin-right: 8px; font-family: 'Segoe UI', 'Helvetica Neue', sans-serif;">
                                        <?php echo nl2br(htmlspecialchars($message->message)); ?>
                                    </span>

                                    <!-- Message Time -->
                                    <span class="message-meta d-inline-flex align-items-center">
                                        <small class="message-time"
                                            style="font-size: 11px; color: #667781; white-space: nowrap;">
                                            <?php echo date('g:i A', strtotime($message->created_at)); ?>
                                        </small>
                                        <?php if ($message->sender_type == 'recruiter'): ?>
                                        <span class="message-status" style="margin-left: 4px;">
                                            <i class="fa fa-check<?php echo $message->is_read ? '-double' : ''; ?>"
                                                style="font-size: 10px; color: <?php echo $message->is_read ? '#128C7E' : '#667781'; ?>;"></i>
                                        </span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="text-center text-muted d-flex align-items-center justify-content-center h-100"
                        style="font-size: 1rem;">
                        <div>
                            <i class="fa fa-comments fa-2x mb-2" style="color: #128C7E;"></i>
                            <p style="margin: 0;">No messages yet. Start the conversation!</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>


                <!-- Message Input -->
                <div class="border-top p-2"
                    style="border-color: #e0e0e0; background-color: #f0f0f0; height: 60px; flex-shrink: 0; min-height: 60px;">
                    <form id="messageForm" class="h-100">
                        <div class="input-group h-100"
                            style="background-color: #ffffff; border-radius: 20px; padding: 2px;">
                            <div class="input-group-prepend h-100">
                                <button type="button" class="btn btn-link h-100"
                                    style="color: #54656f; padding: 0 12px; border: none;">
                                    <i class="fa fa-smile"></i>
                                </button>
                            </div>
                            <input type="text" class="form-control h-100" id="messageInput" placeholder="Type a message"
                                style="background-color: transparent; border: none; color: #3b4a54; font-size: 0.9rem; padding: 0 12px;"
                                autocomplete="off" required>
                            <div class="input-group-append h-100">
                                <button type="button" class="btn btn-link h-100"
                                    style="color: #54656f; padding: 0 12px; border: none;">
                                    <i class="fa fa-paperclip"></i>
                                </button>
                                <button type="submit" class="btn btn-link h-100"
                                    style="color: #128C7E; padding: 0 12px; border: none;">
                                    <i class="fa fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                        <input type="hidden" id="conversationId" value="<?php echo $conversation->id; ?>">
                    </form>
                </div>
                <?php else: ?>
                <!-- No Conversation Selected -->
                <div class="d-flex align-items-center justify-content-center h-100">
                    <div class="text-center text-muted">
                        <i class="fa fa-comments fa-4x mb-3" style="color: #128C7E;"></i>
                        <h4>Welcome to Chat</h4>
                        <p>Select a conversation from the left sidebar to start chatting,<br>or start a new chat with an
                            available agency.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* WhatsApp Message Styles */
.message-bubble.sent {
    background: #dcf8c6 !important;
    margin-left: auto;
    border-top-right-radius: 0px !important;
}

.message-bubble.received {
    background: #ffffff !important;
    margin-right: auto;
    border-top-left-radius: 0px !important;
}

.message-text {
    font-family: 'Segoe UI', 'Helvetica Neue', sans-serif !important;
}

/* Rest of your existing CSS remains the same */
.notification-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: linear-gradient(135deg, #ff6b6b, #ee5a52);
    color: white;
    border-radius: 10px;
    padding: 2px 6px;
    font-size: 0.7rem;
    font-weight: bold;
    min-width: 18px;
    text-align: center;
    box-shadow: 0 2px 5px rgba(255, 107, 107, 0.3);
}

.conversation-notification-badge {
    background: linear-gradient(135deg, #ff6b6b, #ee5a52);
    color: white;
    border-radius: 10px;
    padding: 4px 8px;
    font-size: 0.8rem;
    font-weight: bold;
    margin-left: 10px;
}

.notification-dropdown {
    position: relative;
    display: inline-block;
}

.notification-bell {
    background: rgba(255, 255, 255, 0.15) !important;
    border: 1px solid rgba(255, 255, 255, 0.3) !important;
    border-radius: 10px !important;
    padding: 10px 15px !important;
    color: #2c3e50 !important;
    transition: all 0.3s ease !important;
}

.notification-bell:hover {
    background: rgba(255, 255, 255, 0.25) !important;
    transform: translateY(-2px) !important;
}

.notification-indicator {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 8px;
    height: 8px;
    background: #ff6b6b;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        transform: scale(1);
        opacity: 1;
    }

    50% {
        transform: scale(1.2);
        opacity: 0.7;
    }

    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.notification-dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    width: 350px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    display: none;
    margin-top: 10px;
}

.notification-dropdown.show .notification-dropdown-menu {
    display: block;
}

.notification-header {
    padding: 15px 20px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notification-header h6 {
    margin: 0;
    color: #2c3e50;
}

.view-all {
    color: #3498db;
    text-decoration: none;
    font-size: 0.8rem;
}

.notification-list {
    max-height: 300px;
    overflow-y: auto;
}

.notification-item {
    padding: 15px 20px;
    border-bottom: 1px solid #f8f9fa;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    transition: background-color 0.2s;
    cursor: pointer;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #f0f7ff;
}

.notification-icon {
    color: #3498db;
    font-size: 1.2rem;
    margin-top: 2px;
}

.notification-content {
    flex: 1;
}

.notification-message {
    margin: 0 0 5px 0;
    color: #2c3e50;
    font-size: 0.9rem;
    line-height: 1.4;
}

.notification-time {
    color: #6c757d;
    font-size: 0.8rem;
}

.notification-status {
    width: 8px;
    height: 8px;
    background: #3498db;
    border-radius: 50%;
    margin-top: 8px;
}

.notification-empty {
    padding: 30px 20px;
    text-align: center;
    color: #6c757d;
}

.notification-empty i {
    font-size: 2rem;
    margin-bottom: 10px;
    opacity: 0.5;
}

.sidebar-section {
    position: relative;
}

.section-badge {
    background: #3498db;
    color: white;
    border-radius: 8px;
    padding: 2px 6px;
    font-size: 0.7rem;
    margin-left: 8px;
}

.section-badge.new {
    background: #2ecc71;
}

.conversation-badge {
    animation: bounce 1s infinite;
}

@keyframes bounce {

    0%,
    100% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.1);
    }
}

.online-indicator {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 10px;
    height: 10px;
    background: #2ecc71;
    border: 2px solid #202225;
    border-radius: 50%;
}

.chat-indicator.offline {
    background: #95a5a6;
}

.chat-indicator.offline::after {
    display: none;
}

.conversation-preview strong {
    color: #ffffff;
}

.glass-breadcrumb {
    margin: 20px 0;
}

.glass-container {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    padding: 25px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
    position: relative;
    overflow: hidden;
}

.glass-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
    z-index: -1;
}

.breadcrumb-path {
    display: flex;
    align-items: center;
    gap: 12px;
}

.path-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 20px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 12px;
    color: #2c3e50;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.path-item:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.path-item i {
    font-size: 18px;
    color: #3498db;
}

.path-arrow {
    color: #7f8c8d;
    font-size: 14px;
}

.current-chat {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 25px;
    background: linear-gradient(135deg, #3498db, #2980b9);
    border-radius: 15px;
    color: white;
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.4);
}

.chat-indicator {
    width: 12px;
    height: 12px;
    background: #2ecc71;
    border-radius: 50%;
    position: relative;
}

.chat-indicator::after {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: #2ecc71;
    border-radius: 50%;
    animation: ripple 2s infinite;
}

@keyframes ripple {
    0% {
        transform: scale(1);
        opacity: 1;
    }

    100% {
        transform: scale(2);
        opacity: 0;
    }
}

.chat-details {
    display: flex;
    flex-direction: column;
}

.chat-details strong {
    font-size: 16px;
    font-weight: 600;
}

.chat-details span {
    font-size: 12px;
    opacity: 0.9;
}

.breadcrumb-stats {
    display: flex;
    gap: 20px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 15px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 10px;
    color: #2c3e50;
    font-size: 14px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.stat-item i {
    color: #e74c3c;
    font-size: 16px;
}

@media (max-width: 768px) {
    .glass-container {
        flex-direction: column;
        gap: 20px;
        text-align: center;
    }

    .breadcrumb-path {
        flex-wrap: wrap;
        justify-content: center;
    }

    .breadcrumb-stats {
        justify-content: center;
    }
}

#chatMessages {
    display: block !important;
    flex-direction: column !important;
    height: calc(64vh - 120px) !important;
    overflow-y: auto !important;
    flex-shrink: 0 !important;
}

#main-content {
    overflow: hidden !important;
}

.container-fluid.p-0 {
    overflow: hidden !important;
    height: 64vh !important;
}

.conversation-item:hover {
    background-color: #2f3136 !important;
}

.conversation-item.active {
    background-color: #5865F2 !important;
    color: white !important;
}

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #4e5058;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: #ffffff;
}

#chatMessages::-webkit-scrollbar {
    width: 6px;
}

#chatMessages::-webkit-scrollbar-track {
    background: transparent;
}

#chatMessages::-webkit-scrollbar-thumb {
    background: #cccccc;
    border-radius: 3px;
}

#chatMessages::-webkit-scrollbar-thumb:hover {
    background: #aaaaaa;
}

@media (max-width: 768px) {

    .col-md-4,
    .col-md-8 {
        width: 100%;
        flex: 0 0 100%;
    }

    .message-bubble {
        max-width: 85% !important;
    }

    #chatMessages {
        height: calc(64vh - 110px) !important;
    }

    .container-fluid.p-0 {
        height: 64vh !important;
    }
}

.input-group {
    min-height: 44px !important;
}

.btn-link {
    min-height: 40px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.message-bubble {
    margin: 0 !important;
}

/* Add this to your existing CSS */
@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(255, 107, 107, 0.7);
    }

    70% {
        box-shadow: 0 0 0 10px rgba(255, 107, 107, 0);
    }

    100% {
        box-shadow: 0 0 0 0 rgba(255, 107, 107, 0);
    }
}

.notification-bell.has-notifications {
    animation: pulse 2s infinite;
}
</style>
<style>
/* Add smooth transition for notification badges */
.notification-badge,
.chat-notification-badge {
    transition: all 0.3s ease;
}

/* Visual feedback when marking as read */
.messages-read {
    background-color: rgba(37, 211, 102, 0.1) !important;
    transition: background-color 0.5s ease;
}

/* Pulse animation for new messages */
@keyframes highlightMessage {
    0% {
        background-color: rgba(37, 211, 102, 0.2);
    }

    100% {
        background-color: transparent;
    }
}

.new-message {
    animation: highlightMessage 2s ease;
}
</style>


<script>
// ===== GLOBAL FUNCTIONS =====

// Function to mark notifications as read for CURRENT conversation only
function markNotificationsAsRead() {
    const convId = window.currentConversationId;
    if (!convId) {
        return;
    }

    const formData = new FormData();
    formData.append('conversation_id', convId);

    fetch('<?php echo site_url("recruiter/chat/ajax_mark_notifications_read"); ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                window.hasMarkedAsRead = true;
                window.lastMarkedTime = Date.now();

                // Update UI with new unread count
                if (res.unread_count !== undefined) {
                    updateAllNotificationBadges(res.unread_count);
                }

            } else {
            }
        })
        .catch(error => {
        });
}

// Function to mark notifications as read ONLY when user is actively engaging
function markNotificationsOnUserAction() {
    if (window.currentConversationId && !window.hasMarkedAsRead) {
        markNotificationsAsRead();
    }
}

// Function to reset marked state when new notifications arrive
function resetMarkedStateForNewMessages() {
    window.hasMarkedAsRead = false;
    window.justReceivedNewMessages = true;
    window.newMessageReceivedTime = Date.now();

    // Also update the UI to show notifications immediately
    const chatNotifications = document.querySelector('.chat-notification-badge');
    if (chatNotifications) {
        chatNotifications.style.display = 'flex';
        chatNotifications.textContent = '1';
    }
}

// Function to check if we should allow marking as read (prevent immediate marking after new messages)
function shouldAllowMarkAsRead() {
    if (window.justReceivedNewMessages && window.newMessageReceivedTime) {
        const timeSinceNewMessage = Date.now() - window.newMessageReceivedTime;
        // Don't allow marking as read for at least 8 seconds after new messages (increased from 3)
        if (timeSinceNewMessage < 8000) {
            return false;
        }
    }
    return true;
}

// Function to update all notification badges
function updateAllNotificationBadges(totalUnreadCount) {

    // Update global notification badge in breadcrumb
    const globalBadge = document.getElementById('globalNotificationBadge');
    if (globalBadge) {
        if (totalUnreadCount > 0) {
            globalBadge.textContent = totalUnread_count > 99 ? '99+' : totalUnreadCount;
            globalBadge.style.display = 'inline-block';
        } else {
            globalBadge.style.display = 'none';
        }
    }

    // Update current conversation badge in sidebar
    updateCurrentConversationBadge();

    // Update total unread count in sidebar section
    const sectionBadge = document.querySelector('.sidebar-section .section-badge');
    if (sectionBadge) {
        if (totalUnreadCount > 0) {
            sectionBadge.textContent = totalUnreadCount;
            sectionBadge.style.display = 'inline-block';
        } else {
            sectionBadge.style.display = 'none';
        }
    }

}

// Function to update current conversation badge in sidebar
function updateCurrentConversationBadge() {
    if (!window.currentConversationId) return;

    // Remove badge from current conversation in sidebar
    const currentConvBadge = document.querySelector(`.conversation-item.active .conversation-badge`);
    if (currentConvBadge) {
        currentConvBadge.remove();
    }

    // Also remove from the specific conversation item
    const convItemBadge = document.querySelector(
        `.conversation-item[data-conversation-id="${window.currentConversationId}"] .conversation-badge`);
    if (convItemBadge) {
        convItemBadge.remove();
    }
}

// ===== CHAT NOTIFICATION POLLING =====
function startChatNotificationPolling() {

    // Poll for new chat messages every 5 seconds
    setInterval(fetchChatNotifications, 5000);

    // Initial fetch
    setTimeout(fetchChatNotifications, 1000);
}

function fetchChatNotifications() {

    fetch('<?php echo site_url("recruiter/chat/ajax_get_chat_notifications"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: '<?php echo $this->security->get_csrf_token_name(); ?>=<?php echo $this->security->get_csrf_hash(); ?>'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the UI with the server response
                updateChatNotificationUI(data);

                // Reset marked state if we have new unread notifications
                if (data.unread_count > 0 && !window.hasMarkedAsRead) {
                    resetMarkedStateForNewMessages();
                }
            }
        })
        .catch(error => {
            console.error('Error fetching chat notifications:', error);
        });
}

function updateChatNotificationUI(data) {
    const badge = document.querySelector('.chat-notification-badge');
    const countElement = document.querySelector('.chat-notification-count');
    const chatBell = document.querySelector('.chat-notifications-menu .dropdown-toggle');

    if (data.unread_count > 0) {
        // Update badge
        if (badge) {
            badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
            badge.style.display = 'flex';
        }

        // Update count text
        if (countElement) {
            countElement.textContent = data.unread_count;
        }

        // Add pulse animation
        if (chatBell) {
            chatBell.style.animation = 'chat-pulse 2s infinite';
        }
    } else {
        // Hide badge if no notifications
        if (badge) {
            badge.style.display = 'none';
        }
        if (countElement) {
            countElement.textContent = '0';
        }

        // Remove pulse animation
        if (chatBell) {
            chatBell.style.animation = 'none';
        }
    }
}

// ===== SYSTEM NOTIFICATION POLLING =====
function startRecruiterNotificationPolling() {

    // Poll for new notifications every 5 seconds
    setInterval(fetchRecruiterNotifications, 5000);

    // Initial fetch
    setTimeout(fetchRecruiterNotifications, 1000);
}

function fetchRecruiterNotifications() {

    fetch('<?php echo site_url("recruiter/notifications/ajax_get_notifications"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: '<?php echo $this->security->get_csrf_token_name(); ?>=<?php echo $this->security->get_csrf_hash(); ?>'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                updateRecruiterNotificationUI(data);
            }
        })
        .catch(error => {
        });
}

function updateRecruiterNotificationUI(data) {
    const badge = document.querySelector('.notification-badge');
    const countElement = document.querySelector('.notification-count');
    const notificationBell = document.querySelector('.notifications-menu .dropdown-toggle');

    if (data.system_unread_count > 0) {
        // Update badge
        if (badge) {
            badge.textContent = data.system_unread_count > 99 ? '99+' : data.system_unread_count;
            badge.style.display = 'flex';
        }

        // Update count text
        if (countElement) {
            countElement.textContent = data.system_unread_count;
        }

        // Add pulse animation
        if (notificationBell) {
            notificationBell.style.animation = 'pulse 2s infinite';
        }
    } else {
        // Hide badge if no system notifications
        if (badge) {
            badge.style.display = 'none';
        }
        if (countElement) {
            countElement.textContent = '0';
        }

        // Remove pulse animation
        if (notificationBell) {
            notificationBell.style.animation = 'none';
        }
    }
}

// ===== MAIN CHAT FUNCTIONALITY =====
document.addEventListener('DOMContentLoaded', function() {

    // Initialize global state variables
    window.isSending = false;
    window.refreshInterval;
    window.isPolling = false;
    window.lastMessageId = <?php echo !empty($messages) ? end($messages)->id : 0; ?>;
    window.hasMarkedAsRead = false;
    window.currentConversationId = null;
    window.lastMarkedTime = null;
    window.userIsActive = false; // Track if user is actively engaging with chat
    window.justReceivedNewMessages = false; // Track if we just got new messages
    window.newMessageReceivedTime = null; // Track when new messages were received
    window.focusTimeout = null; // Track focus timeout

    const chatMessages = document.getElementById('chatMessages');
    const messageForm = document.getElementById('messageForm');
    const messageInput = document.getElementById('messageInput');
    const conversationId = document.getElementById('conversationId');

    if (conversationId) {
        window.currentConversationId = conversationId.value;
    }

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

    // Function to create messages in WhatsApp style
    function addMessage(message, isOwn = false) {
        if (!chatMessages) return;

        const html = `
            <div class="d-flex ${isOwn ? 'justify-content-end' : 'justify-content-start'}" style="margin-bottom: 2px;">
                <div class="message-bubble ${isOwn ? 'sent' : 'received'}"
                    style="max-width: 65%; padding: 6px 12px 4px 12px; border-radius: 7.5px; 
                           background-color: ${isOwn ? '#dcf8c6' : '#ffffff'};
                           box-shadow: 0 1px 0.5px rgba(0,0,0,0.13); position: relative;
                           ${isOwn ? 'border-top-right-radius: 0px;' : 'border-top-left-radius: 0px;'}">
                    <!-- Message Text -->
                    <div class="message-text" style="font-size: 14.2px; line-height: 1.3; color: #303030; margin: 0; word-wrap: break-word; padding-bottom: 2px; font-family: 'Segoe UI', 'Helvetica Neue', sans-serif;">
                        ${escapeHtml(message.message).replace(/\n/g, '<br>')}
                    </div>
                    
                    <!-- Message Meta (Time + Status) -->
                    <div class="d-flex justify-content-end align-items-center" style="margin-top: -2px;">
                        <small class="message-time" style="font-size: 11px; color: #667781; white-space: nowrap; margin-right: 4px;">
                            ${message.time || 'Just now'}
                        </small>
                        ${isOwn ? `
                        <span class="message-status" style="font-size: 11px; color: #667781;">
                            <i class="fa fa-check" style="font-size: 10px;"></i>
                        </span>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;

        chatMessages.insertAdjacentHTML('beforeend', html);
        scrollToBottom();

        // Update lastMessageId for real messages
        if (message.id && typeof message.id === 'number' && message.id > window.lastMessageId) {
            window.lastMessageId = message.id;
        }
    }

    // ===== CONTROLLED EVENT LISTENERS =====

    // Mark as read when user focuses on message input (typing) - WITH DELAY
    if (messageInput) {
        messageInput.addEventListener('focus', function() {
            window.userIsActive = true;

            // Clear any existing timeout
            if (window.focusTimeout) {
                clearTimeout(window.focusTimeout);
            }

            // Wait 2 seconds before allowing focus to mark as read
            window.focusTimeout = setTimeout(function() {
                window.justReceivedNewMessages = false; // Reset new message flag

                if (shouldAllowMarkAsRead()) {
                    markNotificationsOnUserAction();
                }
            }, 2000); // 2 second delay
        });

        // Clear timeout if user quickly leaves the input
        messageInput.addEventListener('blur', function() {
            if (window.focusTimeout) {
                clearTimeout(window.focusTimeout);
                window.focusTimeout = null;
            }
        });
    }

    // Mark as read when user starts typing
    if (messageInput) {
        messageInput.addEventListener('input', function() {
            window.userIsActive = true;
            window.justReceivedNewMessages = false; // Reset new message flag

            if (shouldAllowMarkAsRead()) {
                markNotificationsOnUserAction();
            }
        });
    }

    // Mark as read when clicking anywhere in chat area
    if (chatMessages) {
        chatMessages.addEventListener('click', function() {
            window.userIsActive = true;
            window.justReceivedNewMessages = false; // Reset new message flag

            if (shouldAllowMarkAsRead()) {
                markNotificationsOnUserAction();
            }
        });
    }

    // Scroll event listener to mark as read when user scrolls to view messages
    if (chatMessages) {
        chatMessages.addEventListener('scroll', function() {
            const scrollThreshold = 100;
            const isNearBottom = chatMessages.scrollHeight - chatMessages.scrollTop - chatMessages
                .clientHeight <= scrollThreshold;

            if (isNearBottom && !window.hasMarkedAsRead) {
                window.userIsActive = true;

                // Only mark as read if enough time has passed since new messages
                if (shouldAllowMarkAsRead()) {
                    markNotificationsOnUserAction();
                } else {
                }
            }
        });
    }

    function fetchNewMessages() {
        if (window.isPolling) {
            return;
        }

        const convId = window.currentConversationId;
        if (!convId) {
            console.error('No conversation ID found');
            return;
        }

        window.isPolling = true;

        const formData = new FormData();
        formData.append('conversation_id', convId);
        formData.append('last_message_id', window.lastMessageId);

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
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(res => {

                if (res.success) {
                    if (res.last_message_id && res.last_message_id > window.lastMessageId) {
                        window.lastMessageId = parseInt(res.last_message_id);
                    }

                    if (res.has_new_messages && res.html) {
                        document.querySelectorAll('[data-message-id^="temp-"]').forEach(tempMsg => {
                            tempMsg.remove();
                        });

                        chatMessages.insertAdjacentHTML('beforeend', res.html);
                        scrollToBottom();

                        // Reset marked state when new messages arrive
                        if (res.has_new_messages) {
                            resetMarkedStateForNewMessages();
                        }

                    }
                }
            })
            .catch(error => {
                console.error('Error fetching messages:', error);
            })
            .finally(() => {
                window.isPolling = false;
            });
    }

    // Message sending
    if (messageForm) {
        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Mark notifications as read when sending message
            if (!window.hasMarkedAsRead) {
                window.userIsActive = true;
                window.justReceivedNewMessages = false; // Reset new message flag
                markNotificationsAsRead();
            }

            if (window.isSending) {
                return;
            }

            const messageText = messageInput.value.trim();
            if (!messageText) {
                alert('Please enter a message');
                return;
            }

            window.isSending = true;
            const submitButton = messageForm.querySelector('button[type="submit"]');
            const originalHtml = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';

            const convId = window.currentConversationId;
            if (!convId) {
                alert('Conversation ID missing');
                submitButton.disabled = false;
                submitButton.innerHTML = originalHtml;
                return;
            }


            document.querySelectorAll('[data-message-id^="temp-"]').forEach(tempMsg => {
                tempMsg.remove();
            });

            const tempId = 'temp-' + Date.now();
            addMessage({
                id: tempId,
                message: messageText,
                time: 'Just now'
            }, true);
            messageInput.value = '';

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

                        if (res.message_id && !isNaN(res.message_id)) {
                            window.lastMessageId = Math.max(window.lastMessageId, parseInt(res
                                .message_id));
                        }

                        setTimeout(fetchNewMessages, 500);

                    } else {
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
                    const tempElement = document.querySelector(`[data-message-id="${tempId}"]`);
                    if (tempElement) {
                        tempElement.remove();
                    }
                    alert('Network error - please check connection and try again');
                    messageInput.value = messageText;
                })
                .finally(() => {
                    window.isSending = false;
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalHtml;
                    messageInput.focus();
                });
        });
    }

    function startPolling() {
        if (window.refreshInterval) {
            clearInterval(window.refreshInterval);
        }
        window.refreshInterval = setInterval(fetchNewMessages, 3000);
    }

    function stopPolling() {
        if (window.refreshInterval) {
            clearInterval(window.refreshInterval);
        }
    }

    startPolling();

    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopPolling();
        } else {
            startPolling();
            setTimeout(fetchNewMessages, 300);
        }
    });

    window.addEventListener('focus', function() {
        window.userIsActive = true;
        fetchNewMessages();
    });

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

    if (messageInput) {
        setTimeout(() => {
            messageInput.focus();
        }, 500);
    }

});

// Start all polling when page loads
document.addEventListener('DOMContentLoaded', function() {
    startRecruiterNotificationPolling();
    startChatNotificationPolling();
});
</script>