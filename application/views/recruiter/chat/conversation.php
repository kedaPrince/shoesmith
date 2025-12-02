<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<style>
/* ===== DARK THEME COLOR SCHEME ===== */
:root {
    --primary-gradient: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);
    --secondary-gradient: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
    --accent-gradient: linear-gradient(135deg, #10B981 0%, #059669 100%);
    --warning-gradient: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
    --dark-bg: rgba(15, 23, 42, 0.98);
    --darker-bg: rgba(3, 7, 18, 0.95);
    --dark-border: rgba(55, 65, 81, 0.3);
    --light-border: rgba(75, 85, 99, 0.2);
    --text-primary: #F9FAFB;
    --text-secondary: #D1D5DB;
    --text-muted: #9CA3AF;
    --text-accent: #C4B5FD;
    --shadow-glow: 0 8px 32px rgba(139, 92, 246, 0.15);
}

/* ===== COSMIC CHAT HEADER ===== */
.cosmic-chat-header {
    position: relative;
    /* background: var(--darker-bg); */
    /* backdrop-filter: blur(20px); */
    /* border: 1px solid var(--dark-border); */
    border-radius: 20px;
    margin: 20px 0;
    padding: 0;
    overflow: hidden;
    /* box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4),
        inset 0 1px 0 rgba(255, 255, 255, 0.05),
        0 0 0 1px rgba(255, 255, 255, 0.02); */
}

.cosmic-container {
    position: relative;
    z-index: 2;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 30px;
    min-height: 100px;
}

/* Navigation Styles */
.cosmic-navigation {
    flex: 1;
}

.nav-path {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 18px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--light-border);
    border-radius: 14px;
    color: var(--text-secondary);
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.nav-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg,
            transparent,
            rgba(139, 92, 246, 0.1),
            transparent);
    transition: left 0.6s ease;
}

.nav-item:hover::before {
    left: 100%;
}

.nav-item:hover {
    background: rgba(255, 255, 255, 0.05);
    border-color: rgba(139, 92, 246, 0.4);
    transform: translateY(-2px);
    box-shadow: var(--shadow-glow);
    color: var(--text-primary);
}

.nav-item.active {
    background: linear-gradient(135deg,
            rgba(139, 92, 246, 0.15),
            rgba(124, 58, 237, 0.1));
    border-color: rgba(139, 92, 246, 0.3);
    color: var(--text-accent);
}

.nav-icon {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
}

.nav-icon i {
    font-size: 1rem;
    color: inherit;
}

.nav-separator {
    color: var(--text-muted);
    font-size: 0.7rem;
    opacity: 0.6;
}

/* Current Conversation */
.current-conversation {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 20px;
    background: linear-gradient(135deg, rgb(11 155 42 / 12%), rgb(43 0 0 / 8%));
    border: 1px solid rgb(255 255 255 / 0%);
    border-radius: 16px;
    position: relative;
    backdrop-filter: blur(10px);
}

.avatar-glow {
    position: relative;
    width: 44px;
    height: 44px;
}

.avatar-pulse {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: var(--primary-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: white;
    font-size: 1.1rem;
    box-shadow:
        0 0 20px rgba(139, 92, 246, 0.4),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
    animation: avatarGlow 3s ease-in-out infinite;
}

@keyframes avatarGlow {

    0%,
    100% {
        box-shadow:
            0 0 20px rgba(139, 92, 246, 0.4),
            inset 0 1px 0 rgba(255, 255, 255, 0.2);
    }

    50% {
        box-shadow:
            0 0 30px rgba(139, 92, 246, 0.6),
            0 0 40px rgba(139, 92, 246, 0.3),
            inset 0 1px 0 rgba(255, 255, 255, 0.3);
    }
}

.status-indicator {
    position: absolute;
    bottom: 1px;
    right: 1px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    border: 2px solid var(--darker-bg);
    z-index: 2;
}

.status-indicator.online {
    background: var(--accent-gradient);
}

.status-indicator.offline {
    background: var(--text-muted);
}

.pulse-ring {
    position: absolute;
    top: -3px;
    left: -3px;
    right: -3px;
    bottom: -3px;
    border: 2px solid #10B981;
    border-radius: 50%;
    animation: pulseRing 2s linear infinite;
}

@keyframes pulseRing {
    0% {
        transform: scale(0.8);
        opacity: 1;
    }

    100% {
        transform: scale(1.8);
        opacity: 0;
    }
}

.conversation-info h4 {
    margin: 0;
    color: var(--text-primary);
    font-size: 1rem;
    font-weight: 600;
    line-height: 1.2;
}

.conversation-context {
    margin: 2px 0 0 0;
    color: var(--text-muted);
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    gap: 5px;
}

.conversation-context i {
    font-size: 0.7rem;
    opacity: 0.7;
}

/* Badges */
.cosmic-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: var(--warning-gradient);
    color: white;
    border-radius: 8px;
    padding: 3px 6px;
    font-size: 0.65rem;
    font-weight: 700;
    min-width: 18px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.cosmic-badge.pulse {
    animation: cosmicPulse 2s infinite;
}

@keyframes cosmicPulse {

    0%,
    100% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.08);
    }
}

.conversation-badge.cosmic {
    background: var(--accent-gradient);
    color: white;
    border-radius: 10px;
    padding: 5px 8px;
    font-size: 0.75rem;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Cosmic Background Elements */
.cosmic-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1;
    overflow: hidden;
    pointer-events: none;
}

.floating-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(35px);
    opacity: 0.2;
    animation: float 8s ease-in-out infinite;
}

.orb-1 {
    width: 80px;
    height: 80px;
    background: radial-gradient(circle, #8B5CF6, transparent);
    top: 20%;
    left: 10%;
    animation-delay: 0s;
}

.orb-2 {
    width: 120px;
    height: 120px;
    background: radial-gradient(circle, #f59f0b0a, transparent);
    top: 60%;
    right: 15%;
    animation-delay: -3s;
}

.orb-3 {
    width: 60px;
    height: 60px;
    background: radial-gradient(circle, #10B981, transparent);
    bottom: 20%;
    left: 20%;
    animation-delay: -6s;
}

@keyframes float {

    0%,
    100% {
        transform: translateY(0) scale(1);
    }

    50% {
        transform: translateY(-15px) scale(1.05);
    }
}

.energy-wave {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg,
            transparent,
            #8B5CF6,
            #F59E0B,
            #10B981,
            transparent);
    opacity: 0.3;
    animation: waveFlow 4s linear infinite;
}

@keyframes waveFlow {
    0% {
        transform: translateX(-100%);
    }

    100% {
        transform: translateX(100%);
    }
}

/* Cosmic Glow Effect */
.cosmic-glow {
    position: relative;
}

.cosmic-glow::after {
    content: '';
    position: absolute;
    top: -1px;
    left: -1px;
    right: -1px;
    bottom: -1px;
    background: var(--primary-gradient);
    border-radius: inherit;
    z-index: -1;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.cosmic-glow:hover::after {
    opacity: 0.2;
}

/* ===== CHAT STYLES ===== */
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

/* WhatsApp-style Conversation Badges */
.conversation-badge {
    background: #25D366 !important;
    color: white !important;
    border-radius: 10px !important;
    padding: 2px 6px !important;
    font-size: 0.7rem !important;
    font-weight: bold !important;
    min-width: 18px !important;
    height: 18px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2) !important;
    animation: badgePulse 2s infinite !important;
    margin-top: 2px !important;
}

@keyframes badgePulse {
    0% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.05);
    }

    100% {
        transform: scale(1);
    }
}

/* Visual indicators for conversations with unread messages */
.conversation-item.has-unread-messages {
    background-color: rgba(37, 211, 102, 0.1) !important;
    border-left: 3px solid #25D366 !important;
}

.conversation-item.has-unread-messages .conversation-preview {
    font-weight: 600 !important;
}

/* Section badge styling */
.section-badge {
    background: #25D366 !important;
    color: white !important;
    border-radius: 8px !important;
    padding: 2px 6px !important;
    font-size: 0.7rem !important;
    margin-left: 8px !important;
}

/* Global notification badge */
.notification-badge {
    background: linear-gradient(135deg, #25D366, #128C7E) !important;
    color: white !important;
    border-radius: 10px !important;
    padding: 2px 6px !important;
    font-size: 0.7rem !important;
    font-weight: bold !important;
    min-width: 18px !important;
    text-align: center !important;
    box-shadow: 0 2px 5px rgba(37, 211, 102, 0.3) !important;
}

/* Online indicator */
.online-indicator {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 10px;
    height: 10px;
    background: #25D366;
    border: 2px solid #202225;
    border-radius: 50%;
}

/* Conversation item hover effects */
.conversation-item:hover {
    background-color: #2f3136 !important;
    transform: translateX(2px);
    transition: all 0.2s ease;
}

.conversation-item.active {
    background-color: #000000ff !important;
    color: white !important;
}

/* Bold text for unread messages in preview */
.conversation-preview strong {
    color: #ffffff !important;
    font-weight: 700 !important;
}

/* Time styling */
.conversation-time {
    color: #888 !important;
    font-size: 0.7rem !important;
}

.list-group-item.active {
    z-index: 2;
    color: #fff;
    background-color: #000000ff;
    border-color: #000000ff;
}

/* Chat Container Styles */
#chatMessages {
    display: block !important;
    flex-direction: column !important;
    height: calc(74vh - 120px) !important;
    overflow-y: auto !important;
    flex-shrink: 0 !important;
}

#main-content {
    overflow: hidden !important;
}

.container-fluid.p-0 {
    overflow: hidden !important;
    height: 74vh !important;
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

/* Responsive Design */
@media (max-width: 1200px) {
    .cosmic-container {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }

    .nav-path {
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .cosmic-container {
        padding: 15px 20px;
    }

    .nav-path {
        gap: 6px;
    }

    .nav-item {
        padding: 8px 12px;
        font-size: 0.85rem;
    }

    .current-conversation {
        padding: 10px 14px;
        gap: 10px;
    }

    .avatar-glow {
        width: 36px;
        height: 36px;
    }

    .avatar-pulse {
        font-size: 0.9rem;
    }

    .col-md-4,
    .col-md-8 {
        width: 100%;
        flex: 0 0 100%;
    }

    .message-bubble {
        max-width: 85% !important;
    }

    #chatMessages {
        height: calc(74vh - 110px) !important;
    }
}

/* ===== DISCORD-STYLE RIGHT SIDEBAR ===== */
.chat-sidebar-right {
    width: 280px;
    background: #2f3136;
    border-left: 1px solid #36393f;
    display: flex;
    flex-direction: column;
    height: 100%;
    flex-shrink: 0;
}

.sidebar-section {
    padding: 16px;
    border-bottom: 1px solid #36393f;
}

.sidebar-section:last-child {
    border-bottom: none;
}

.sidebar-header {
    color: #8e9297;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

/* Agency Profile Section */
.agency-profile {
    text-align: center;
    padding: 20px 16px;
}

.agency-avatar-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #7289da, #424549);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    font-size: 2rem;
    font-weight: bold;
    color: white;
    border: 4px solid #36393f;
    position: relative;
}

.agency-avatar-large.online::before {
    content: '';
    position: absolute;
    bottom: 4px;
    right: 4px;
    width: 16px;
    height: 16px;
    background: #3ba55d;
    border: 3px solid #2f3136;
    border-radius: 50%;
    z-index: 2;
}

.agency-avatar-large.offline::before {
    content: '';
    position: absolute;
    bottom: 4px;
    right: 4px;
    width: 16px;
    height: 16px;
    background: #747f8d;
    border: 3px solid #2f3136;
    border-radius: 50%;
    z-index: 2;
}

.agency-name-large {
    color: white;
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 4px;
}

.agency-status {
    color: #b9bbbe;
    font-size: 0.9rem;
    margin-bottom: 12px;
}

.status-online {
    color: #3ba55d;
}

.status-offline {
    color: #747f8d;
}

/* Action Buttons */
.sidebar-actions {
    display: flex;
    gap: 8px;
    justify-content: center;
    margin-bottom: 16px;
}

.sidebar-btn {
    background: #4f545c;
    border: none;
    border-radius: 4px;
    color: white;
    padding: 8px 12px;
    font-size: 0.8rem;
    cursor: pointer;
    transition: background-color 0.2s;
    display: flex;
    align-items: center;
    gap: 4px;
}

.sidebar-btn:hover {
    background: #5d6269;
}

.sidebar-btn.primary {
    background: #7289da;
}

.sidebar-btn.primary:hover {
    background: #677bc4;
}

/* Info Cards */
.info-card {
    background: #40444b;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 8px;
}

.info-card-title {
    color: #b9bbbe;
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-card-value {
    color: white;
    font-size: 1rem;
    font-weight: 600;
}

.info-card-description {
    color: #8e9297;
    font-size: 0.8rem;
    margin-top: 4px;
}

/* Member List */
.member-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.member-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 4px 8px;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.member-item:hover {
    background: #393c42;
}

.member-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #7289da;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: bold;
    color: white;
}

.member-name {
    color: #8e9297;
    font-size: 0.9rem;
    flex: 1;
}

.member-role {
    background: #7289da;
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
}

/* Note Section */
.note-section {
    margin-top: 16px;
}

.note-textarea {
    width: 100%;
    background: #40444b;
    border: 1px solid #36393f;
    border-radius: 4px;
    color: white;
    padding: 8px;
    font-size: 0.9rem;
    resize: vertical;
    min-height: 80px;
}

.note-textarea:focus {
    outline: none;
    border-color: #7289da;
}

/* Quick Stats */
.stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-top: 12px;
}

.stat-item {
    text-align: center;
    padding: 8px;
    background: #40444b;
    border-radius: 4px;
}

.stat-number {
    color: white;
    font-size: 1.2rem;
    font-weight: 600;
    display: block;
}

.stat-label {
    color: #8e9297;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .chat-sidebar-right {
        width: 240px;
    }
}

@media (max-width: 992px) {
    .chat-sidebar-right {
        display: none;
    }

    .col-md-8.col-lg-9 {
        width: 100%;
        flex: 0 0 100%;
    }
}

/* Toggle button for mobile */
.sidebar-toggle {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #7289da;
    border: none;
    border-radius: 4px;
    color: white;
    padding: 8px;
    cursor: pointer;
    z-index: 1000;
    display: none;
}

@media (max-width: 992px) {
    .sidebar-toggle {
        display: block;
    }

    .chat-sidebar-right.mobile-open {
        display: flex;
        position: fixed;
        right: 0;
        top: 0;
        bottom: 0;
        z-index: 999;
        width: 280px;
    }
}

.avatar {
    width: 32px;
    border: none !important;
}
</style>
<div id="main-content">

    <!-- Cosmic Chat Header - Dark Theme -->
    <div class="cosmic-chat-header">
        <div class="cosmic-container">
            <!-- Left: Navigation & Chat Info -->
            <div class="cosmic-navigation">
                <div class="nav-path">
                    <a href="<?php echo site_url('recruiter/dashboard'); ?>" class="nav-item cosmic-glow">
                        <div class="nav-icon">
                            <i class="fa fa-chart-line"></i>
                        </div>
                        <span>Dashboard</span>
                    </a>

                    <div class="nav-separator">
                        <i class="fa fa-chevron-right"></i>
                    </div>

                    <div class="nav-item cosmic-glow active">
                        <div class="nav-icon">
                            <i class="fa fa-comments"></i>
                            <?php if (isset($total_unread_count) && $total_unread_count > 0): ?>
                            <span class="cosmic-badge pulse" id="globalNotificationBadge">
                                <?php echo $total_unread_count > 99 ? '99+' : $total_unread_count; ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <span>Messages</span>
                    </div>

                    <?php if (isset($conversation)): ?>
                    <div class="nav-separator">
                        <i class="fa fa-chevron-right"></i>
                    </div>

                    <div class="current-conversation cosmic-glow">
                        <div class="conversation-avatar">
                            <div class="avatar-glow">
                                <div class="avatar-pulse">
                                    <?php echo substr(htmlspecialchars($conversation->agency_name), 0, 1); ?>
                                </div>
                                <div
                                    class="status-indicator <?php echo (isset($conversation->is_online) && $conversation->is_online) ? 'online' : 'offline'; ?>">
                                    <div class="pulse-ring"></div>
                                </div>
                            </div>
                        </div>
                        <div class="conversation-info">
                            <h4 class="agency-name">
                                <?php echo htmlspecialchars($conversation->agency_name); ?></h4>
                            <p class="conversation-context">
                                <i class="fa fa-briefcase"></i>
                                <?php echo $conversation->job_name ? htmlspecialchars($conversation->job_name) : 'Direct Message'; ?>
                            </p>
                        </div>
                        <?php if (isset($conversation) && isset($conversation->unread_count) && $conversation->unread_count > 0): ?>
                        <div class="conversation-badge cosmic">
                            <span><?php echo $conversation->unread_count; ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Animated Background Elements -->
        <div class="cosmic-background">
            <div class="floating-orb orb-1"></div>
            <div class="floating-orb orb-2"></div>
            <div class="floating-orb orb-3"></div>
            <div class="energy-wave"></div>
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
                            <a href="<?php echo site_url('recruiter/chat/conversation/' . $conv->uuid); ?>"
                                class="list-group-item list-group-item-action d-flex align-items-center conversation-item <?php echo (isset($conversation) && $conversation->uuid == $conv->uuid) ? 'active' : ''; ?>"
                                style="border: none; border-radius: 8px; margin-bottom: 5px; padding: 10px 15px; transition: all 0.2s;"
                                data-conversation-uuid="<?php echo $conv->uuid; ?>">
                                <!-- Changed to uuid -->

                                <div class="conversation-avatar mr-3 position-relative">
                                    <div class="avatar"
                                        style="width: 40px; height: 40px; border-radius: 50%; background-color: #128C7E; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #ffffff;">
                                        <?php echo substr(htmlspecialchars($conv->agency_name), 0, 1); ?>
                                    </div>
                                    <?php if (isset($conv->is_online) && $conv->is_online): ?>
                                    <div class="online-indicator"></div>
                                    <?php endif; ?>
                                </div>

                                <div class="flex-grow-1">
                                    <h6 class="mb-0" style="color: #ffffff; font-weight: 500;">
                                        <?php echo htmlspecialchars($conv->agency_name); ?>
                                    </h6>
                                    <small class="text-muted d-block conversation-preview"
                                        style="font-size: 0.75rem; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                        data-conversation-uuid="<?php echo $conv->uuid; ?>">
                                        <!-- Changed to uuid -->
                                        <?php if (isset($conv->unread_count) && $conv->unread_count > 0): ?>
                                        <strong><?php echo htmlspecialchars($conv->last_message ?: 'New message'); ?></strong>
                                        <?php else: ?>
                                        <?php echo htmlspecialchars($conv->last_message ?: 'No messages yet'); ?>
                                        <?php endif; ?>
                                    </small>
                                </div>

                                <div class="text-right ml-2">
                                    <small class="text-muted d-block conversation-time" style="font-size: 0.7rem;"
                                        data-conversation-uuid="<?php echo $conv->uuid; ?>">
                                        <!-- Changed to uuid -->
                                        <?php echo time_ago($conv->last_message_at ?: $conv->created_at); ?>
                                    </small>
                                    <?php if (isset($conv->unread_count) && $conv->unread_count > 0): ?>
                                    <span class="conversation-badge"
                                        data-conversation-uuid="<?php echo $conv->uuid; ?>">
                                        <!-- Changed to uuid -->
                                        <?php echo $conv->unread_count > 99 ? '99+' : $conv->unread_count; ?>
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

            <!-- Main Chat Area with Right Sidebar -->
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

                <!-- Mobile Sidebar Toggle -->
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fa fa-info-circle"></i>
                </button>

                <?php if (isset($conversation)): ?>
                <div class="d-flex" style="flex: 1; overflow: hidden;">
                    <!-- Chat Messages Area -->
                    <div class="flex-grow-1 d-flex flex-column" style="min-width: 0;">
                        <!-- Chat Header -->
                        <div class="d-flex align-items-center justify-content-between p-3 border-bottom"
                            style="border-color: #e0e0e0; background-color: #007171; height: 60px; flex-shrink: 0; min-height: 60px;">
                            <div class="d-flex align-items-center">
                                <div class="avatar mr-3"
                                    style="width: 40px; height: 40px; border-radius: 50%; background-color: #128C7E; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #ffffff; font-size: 1.2rem;">
                                    <?php echo substr(htmlspecialchars($conversation->agency_name), 0, 1); ?>
                                </div>
                                <div>
                                    <h5 class="mb-0" style="color: #ffffffff; font-weight: 500; font-size: 1.2rem;">
                                        <?php echo htmlspecialchars($conversation->agency_name); ?></h5>
                                    <small class="text-muted" style="font-size: 0.75rem; color:#a0a0a0ff!important;">
                                        <?php echo $conversation->job_name ? 'Job: ' . htmlspecialchars($conversation->job_name) : 'Online'; ?>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Messages Area - WhatsApp Style -->
                        <div class="flex-grow-1 overflow-auto p-2" id="chatMessages"
                            style="display: block; padding: 8px 12px; position: relative; height: calc(74vh - 120px); overflow-y: auto;">
                            <?php if (!empty($messages)): ?>
                            <?php foreach ($messages as $message): ?>
                            <div
                                class="d-flex <?php echo $message->sender_type == 'recruiter' ? 'justify-content-end' : 'justify-content-start'; ?> mb-2">
                                <div class="message-container" style="max-width: 70%;">
                                    <div
                                        class="message-content d-flex align-items-baseline <?php echo $message->sender_type == 'recruiter' ? 'justify-content-end' : 'justify-content-start'; ?>">
                                        <div class="message-text-time d-inline-flex align-items-baseline" style="background-color: <?php echo $message->sender_type == 'recruiter' ? '#dcf8c6' : '#ffffff'; ?>; 
                                    padding: 8px 12px; 
                                    border-radius: 7.5px;
                                    box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);">
                                            <span class="message-text"
                                                style="font-size: 14.2px; color: #303030; line-height: 1.3; margin-right: 8px; font-family: 'Segoe UI', 'Helvetica Neue', sans-serif;">
                                                <?php echo nl2br(htmlspecialchars($message->message)); ?>
                                            </span>
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
                                <input type="hidden" id="conversationUuid"
                                    value="<?php echo isset($conversation) ? $conversation->uuid : ''; ?>">
                                <div class="input-group h-100"
                                    style="background-color: #ffffff; border-radius: 20px; padding: 2px;">
                                    <div class="input-group-prepend h-100">
                                        <button type="button" class="btn btn-link h-100"
                                            style="color: #54656f; padding: 0 12px; border: none;">
                                            <i class="fa fa-smile"></i>
                                        </button>
                                    </div>
                                    <input type="text" class="form-control h-100" id="messageInput"
                                        placeholder="Type a message"
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
                    </div>

                    <!-- Right Sidebar - Agency Details -->
                    <div class="chat-sidebar-right" id="agencySidebar">
                        <?php if (isset($agency_details)): ?>
                        <!-- Agency Profile -->
                        <div class="sidebar-section agency-profile">
                            <div class="agency-avatar-large <?php echo $agency_online ? 'online' : 'offline'; ?>">
                                <?php echo substr(htmlspecialchars($agency_details->name), 0, 1); ?>
                            </div>
                            <h3 class="agency-name-large"><?php echo htmlspecialchars($agency_details->name); ?></h3>
                            <div
                                class="agency-status <?php echo $agency_online ? 'status-online' : 'status-offline'; ?>">
                                <i class="fa fa-circle"></i>
                                <?php echo $agency_online ? 'Online' : 'Offline'; ?>
                            </div>

                            <div class="sidebar-actions">
                                <button class="sidebar-btn" title="Call">
                                    <i class="fa fa-phone"></i>
                                </button>
                                <button class="sidebar-btn" title="Video Call">
                                    <i class="fa fa-video"></i>
                                </button>
                                <button class="sidebar-btn primary" title="Start Call">
                                    <i class="fa fa-phone"></i>
                                    Call
                                </button>
                            </div>
                            <p>COMING SOON</p>
                        </div>

                        <!-- Agency Information -->
                        <div class="sidebar-section">
                            <div class="sidebar-header">Agency Information</div>

                            <?php if ($agency_details->email): ?>
                            <div class="info-card">
                                <div class="info-card-title">Email</div>
                                <div class="info-card-value"><?php echo htmlspecialchars($agency_details->email); ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($agency_details->phone): ?>
                            <div class="info-card">
                                <div class="info-card-title">Phone</div>
                                <div class="info-card-value"><?php echo htmlspecialchars($agency_details->phone); ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($agency_details->industry): ?>
                            <div class="info-card">
                                <div class="info-card-title">Industry</div>
                                <div class="info-card-value"><?php echo htmlspecialchars($agency_details->industry); ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($agency_details->website): ?>
                            <div class="info-card">
                                <div class="info-card-title">Website</div>
                                <div class="info-card-value">
                                    <a href="<?php echo htmlspecialchars($agency_details->website); ?>" target="_blank"
                                        style="color: #7289da; text-decoration: none;">
                                        Visit Website
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Quick Stats -->
                        <div class="sidebar-section">
                            <div class="sidebar-header">Statistics</div>
                            <div class="stats-grid">
                                <div class="stat-item">
                                    <span
                                        class="stat-number"><?php echo $agency_details->total_recruiters ?? 0; ?></span>
                                    <span class="stat-label">Recruiters</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number"><?php echo $agency_details->active_jobs ?? 0; ?></span>
                                    <span class="stat-label">Active Jobs</span>
                                </div>
                                <div class="stat-item">
                                    <span
                                        class="stat-number"><?php echo $agency_details->total_conversations ?? 0; ?></span>
                                    <span class="stat-label">Chats</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number">
                                        <?php echo $agency_online ? 'Now' : date('M j', strtotime($agency_details->last_activity_at ?? 'now')); ?>
                                    </span>
                                    <span
                                        class="stat-label"><?php echo $agency_online ? 'Active' : 'Last Seen'; ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Conversation Context -->
                        <?php if ($conversation->job_name): ?>
                        <div class="sidebar-section">
                            <div class="sidebar-header">Conversation Context</div>
                            <div class="info-card">
                                <div class="info-card-title">Job Position</div>
                                <div class="info-card-value"><?php echo htmlspecialchars($conversation->job_name); ?>
                                </div>
                                <div class="info-card-description">
                                    This conversation is regarding the job position mentioned above.
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Note Section -->
                        <div class="sidebar-section note-section">
                            <div class="sidebar-header">Private Notes</div>
                            <textarea class="note-textarea"
                                placeholder="Add private notes about this agency..."></textarea>
                        </div>

                        <?php else: ?>
                        <!-- Loading State -->
                        <div class="sidebar-section agency-profile">
                            <div class="agency-avatar-large offline" style="background: #40444b;">
                                <i class="fa fa-spinner fa-spin"></i>
                            </div>
                            <h3 class="agency-name-large">Loading...</h3>
                            <div class="agency-status status-offline">
                                <i class="fa fa-circle"></i>
                                Fetching details
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php else: ?>
                <!-- No Conversation Selected -->
                <div class="d-flex align-items-center justify-content-center h-100" style="flex: 1;">
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



<script>
// ===== CSRF INITIALIZATION =====
// Ensure CSRF variables exist
if (typeof csrfName === 'undefined') {
    window.csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
}

if (typeof csrf === 'undefined') {
    window.csrf = '<?php echo $this->security->get_csrf_hash(); ?>';
}

// ===== LOADING CHECK =====
console.log("=== RECRUITER CHAT LOADING CHECK ===");

// Check if core.js is loaded
if (typeof ajax_post === 'undefined') {
    console.error("WARNING: core.js not loaded or ajax_post not defined!");
    console.error("This page needs core.js to be loaded before this script.");
    console.error("Make sure core.js is included in your HTML before this script.");

    // Let's check what scripts are loaded
    console.log("Loaded scripts:", Array.from(document.querySelectorAll('script[src]')).map(s => s.src));
}

// Check if we're in the right context
if (!window.location.href.includes('/recruiter/chat/')) {
    console.warn("This script is designed for /recruiter/chat/ pages");
}

// Create standalone ajax_post if missing
if (typeof ajax_post === 'undefined') {
    console.log("Creating complete standalone ajax_post function");

    // ===== IMPROVED AJAX_POST FUNCTION =====
    // Create standalone ajax_post if missing or override existing one
    console.log("Creating/updating ajax_post function...");

    window.ajax_post = function(path, params, callback, options) {
        // Add CSRF token to params
        const csrfName = window.csrfName || 'csrf_rfid_token';
        const csrfToken = window.csrf || '';

        params[csrfName] = csrfToken;

        // Build URL - USE RECRUITER PATH
        let url = path;
        const dynamicPath = window.dynamicPath || 'http://localhost/shoesmith/recruiter';

        if (dynamicPath && !path.startsWith('http')) {
            url = dynamicPath + '/' + path.replace(/^\//, '');
        }

        // Use FormData to ensure proper form submission
        const formData = new FormData();

        // Add all params to FormData
        Object.keys(params).forEach(key => {
            formData.append(key, params[key]);
        });

        // Use fetch instead of jQuery for better control
        fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Update CSRF if returned
                if (data.csrf) {
                    window.csrf = data.csrf;
                    // Update hidden CSRF field if exists
                    const csrfInput = document.querySelector('input[name="' + csrfName + '"]');
                    if (csrfInput) {
                        csrfInput.value = data.csrf;
                    }
                }

                if (typeof callback === 'function') {
                    callback(data);
                }
            })
            .catch(error => {
                console.error('AJAX error:', error);
                if (typeof callback === 'function') {
                    callback({
                        success: false,
                        message: 'AJAX error'
                    });
                }
            });
    };

    console.log("✅ ajax_post function created successfully");
}

// ===== CHECK DEPENDENCIES =====
console.log("=== CHECKING DEPENDENCIES ===");
console.log("jQuery:", typeof jQuery !== 'undefined' ? "LOADED" : "MISSING");
console.log("ajax_post:", typeof ajax_post !== 'undefined' ? "LOADED" : "MISSING");
console.log("csrfName:", typeof csrfName !== 'undefined' ? csrfName : "MISSING");
console.log("csrf:", typeof csrf !== 'undefined' ? (csrf ? "LOADED" : "EMPTY") : "MISSING");
console.log("dynamicPath:", typeof dynamicPath !== 'undefined' ? dynamicPath : "MISSING");

// Fix missing dynamicPath for recruiter
if (typeof dynamicPath === 'undefined') {
    dynamicPath = window.location.origin + '/shoesmith/recruiter';
    console.log("Set dynamicPath to:", dynamicPath);
}

// ===== GLOBAL STATE =====
let chatState = {
    isSending: false,
    isPolling: false,
    lastMessageId: <?php echo !empty($messages) ? end($messages)->id : 0; ?>,
    currentConversationId: document.getElementById('conversationId') ? document.getElementById('conversationId')
        .value : null,
    currentConversationUuid: document.getElementById('conversationUuid') ? document.getElementById(
        'conversationUuid').value : null, // Read from hidden field
    hasMarkedAsRead: false,
    userIsActive: false,
    newMessageReceivedTime: null,
    focusTimeout: null
};

// ===== UTILITY FUNCTIONS =====
function scrollToBottom() {
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function getTimeAgo(timestamp) {
    const now = new Date();
    const messageTime = new Date(timestamp);
    const diffMs = now - messageTime;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m`;
    if (diffHours < 24) return `${diffHours}h`;
    if (diffDays < 7) return `${diffDays}d`;
    return messageTime.toLocaleDateString();
}

// ===== NOTIFICATION FUNCTIONS =====
function updateAllNotificationBadges(totalUnreadCount) {
    const globalBadge = document.getElementById('globalNotificationBadge');
    if (globalBadge) {
        if (totalUnreadCount > 0) {
            globalBadge.textContent = totalUnreadCount > 99 ? '99+' : totalUnreadCount;
            globalBadge.style.display = 'inline-block';
        } else {
            globalBadge.style.display = 'none';
        }
    }

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

function markNotificationsAsRead() {
    const convUuid = chatState.currentConversationUuid; // Need to add this to chatState
    if (!convUuid) return;

    console.log("Marking notifications as read for conversation:", convUuid);

    ajax_post('ajax_mark_notifications_read', {
        conversation_uuid: convUuid // Changed from conversation_id to conversation_uuid
    }, function(res) {
        console.log("Mark read response:", res);
        if (res.success) {
            chatState.hasMarkedAsRead = true;
            updateAllNotificationBadges(res.unread_count);
            fetchUpdatedConversations();
        }
    });
}

function markNotificationsOnUserAction() {
    if (chatState.currentConversationUuid && !chatState.hasMarkedAsRead) { // Changed to UUID
        markNotificationsAsRead();
    }
}

function resetMarkedStateForNewMessages() {
    chatState.hasMarkedAsRead = false;
    chatState.newMessageReceivedTime = Date.now();
}

function shouldAllowMarkAsRead() {
    if (chatState.newMessageReceivedTime) {
        const timeSinceNewMessage = Date.now() - chatState.newMessageReceivedTime;
        if (timeSinceNewMessage < 5000) {
            return false;
        }
    }
    return true;
}

// ===== SIDEBAR FUNCTIONS =====
function updateSingleConversationBadge(conversationItem, convData) {
    let badge = conversationItem.querySelector('.conversation-badge');

    if (convData.unread_count > 0) {
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'conversation-badge';
            badge.setAttribute('data-conversation-uuid', convData.uuid); // Changed to uuid

            const textRightDiv = conversationItem.querySelector('.text-right');
            if (textRightDiv) {
                textRightDiv.appendChild(badge);
            }
        }

        badge.textContent = convData.unread_count > 99 ? '99+' : convData.unread_count;
        badge.style.display = 'flex';
        conversationItem.classList.add('has-unread-messages');

    } else {
        if (badge) {
            badge.style.display = 'none';
        }
        conversationItem.classList.remove('has-unread-messages');
    }

    const previewElement = conversationItem.querySelector('.conversation-preview');
    if (previewElement) {
        let previewText = '';

        if (convData.unread_count > 0) {
            if (convData.last_sender_type === 'agency') {
                previewText =
                    `<strong>${escapeHtml(convData.agency_name || 'Agency')}: ${escapeHtml(convData.last_message || 'New message')}</strong>`;
            } else {
                previewText = `<strong>You: ${escapeHtml(convData.last_message || 'New message')}</strong>`;
            }
        } else {
            if (convData.last_sender_type === 'agency') {
                previewText =
                    `${escapeHtml(convData.agency_name || 'Agency')}: ${escapeHtml(convData.last_message || 'No messages yet')}`;
            } else {
                previewText = `You: ${escapeHtml(convData.last_message || 'No messages yet')}`;
            }
        }

        previewElement.innerHTML = previewText;
    }

    const timeElement = conversationItem.querySelector('.conversation-time');
    if (timeElement && convData.last_message_at) {
        timeElement.textContent = getTimeAgo(convData.last_message_at);
    }
}

function updateSidebarConversationBadges(conversationsData) {
    conversationsData.forEach(conv => {
        const conversationItem = document.querySelector(
            `.conversation-item[data-conversation-uuid="${conv.uuid}"]`);
        if (conversationItem) {
            updateSingleConversationBadge(conversationItem, conv);
        }
    });
}

// ===== CHAT FUNCTIONS =====
function fetchNewMessages() {
    if (chatState.isPolling || !chatState.currentConversationUuid) return;

    chatState.isPolling = true;
    console.log("Fetching new messages...");

    ajax_post('ajax_get_messages', {
        conversation_uuid: chatState.currentConversationUuid,
        last_message_id: chatState.lastMessageId
    }, function(res) {
        console.log("New messages response:", res);
        if (res.success) {
            if (res.last_message_id && res.last_message_id > chatState.lastMessageId) {
                chatState.lastMessageId = parseInt(res.last_message_id);
            }

            if (res.has_new_messages && res.html) {
                document.querySelectorAll('[data-message-id^="temp-"]').forEach(tempMsg => {
                    tempMsg.remove();
                });

                const chatMessages = document.getElementById('chatMessages');
                if (chatMessages) {
                    chatMessages.insertAdjacentHTML('beforeend', res.html);
                    scrollToBottom();
                }

                if (res.has_new_messages) {
                    resetMarkedStateForNewMessages();
                    setTimeout(fetchUpdatedConversations, 300);
                }
            }
        }
        chatState.isPolling = false;
    });
}

function fetchUpdatedConversations() {
    console.log("Fetching updated conversations...");

    ajax_post('ajax_get_conversations', {}, function(data) {
        console.log("Conversations response:", data);
        if (data.success && data.conversations) {
            updateSidebarConversationBadges(data.conversations);
            if (data.total_unread_count !== undefined) {
                updateAllNotificationBadges(data.total_unread_count);
            }
        }
    });
}

function sendMessage() {
    console.log("=== SEND MESSAGE DEBUG ===");

    if (chatState.isSending) {
        console.log("Already sending, skipping");
        return;
    }

    const messageInput = document.getElementById('messageInput');
    const messageText = messageInput.value.trim();

    console.log("1. Message text:", messageText);
    console.log("2. Conversation UUID:", chatState.currentConversationUuid);
    console.log("3. CSRF Name:", csrfName);
    console.log("4. CSRF Value:", csrf ? "YES (" + csrf.substring(0, 10) + "...)" : "NO");
    console.log("5. dynamicPath:", dynamicPath);

    if (!messageText) {
        console.log("Empty message, skipping");
        alert("Please type a message first");
        return;
    }

    if (!chatState.currentConversationId) {
        console.error("No conversation ID selected!");
        alert("Please select a conversation first");
        return;
    }

    chatState.isSending = true;
    console.log("6. Setting isSending to true");

    const submitButton = document.querySelector('#messageForm button[type="submit"]');
    let originalHtml = '';
    if (submitButton) {
        originalHtml = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
        console.log("7. Updated submit button");
    }

    console.log("10. Calling ajax_post...");

    ajax_post('ajax_send_message', {
        conversation_uuid: chatState.currentConversationUuid,
        message: messageText
    }, function(res) {
        console.log("=== SEND MESSAGE RESPONSE ===");
        console.log("Response received:", res);
        console.log("Success:", res.success);
        console.log("Message:", res.message);
        console.log("Has CSRF in response:", !!res.csrf);

        if (res.success) {
            console.log("✅ Message sent successfully!");
            console.log("Message ID:", res.message_id);
            messageInput.value = '';

            // Refresh conversations and messages
            setTimeout(function() {
                console.log("Refreshing conversations...");
                fetchUpdatedConversations();
                fetchNewMessages();
            }, 300);
        } else {
            console.error("❌ Failed to send message:", res.message);

            // Show user-friendly error
            let errorMsg = "Failed to send message";
            if (res.message) {
                errorMsg += ": " + res.message;
            }
            alert(errorMsg);

            // If CSRF error, try to update token
            if (res.message && res.message.includes('CSRF')) {
                console.log("CSRF error detected, updating token...");
                if (res.csrf) {
                    csrf = res.csrf;
                    console.log("CSRF updated to:", csrf.substring(0, 10) + "...");
                }
            }
        }

        // Reset UI
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = originalHtml;
            console.log("Reset submit button");
        }

        chatState.isSending = false;
        console.log("Setting isSending to false");

        // Refocus on input
        if (messageInput) {
            setTimeout(() => {
                messageInput.focus();
                console.log("Refocused on message input");
            }, 100);
        }
    });
}

// ===== EVENT LISTENERS =====
function setupEventListeners() {
    const messageInput = document.getElementById('messageInput');
    const chatMessages = document.getElementById('chatMessages');
    const messageForm = document.getElementById('messageForm');

    console.log("Setting up event listeners...");

    // Prevent default form submission
    if (messageForm) {
        console.log("Setting up message form listener");
        messageForm.addEventListener('submit', function(e) {
            console.log("Form submit event fired");
            e.preventDefault();
            e.stopPropagation();
            console.log("Prevented default form submission");
            sendMessage();
            return false;
        });

        // Also prevent Enter key from submitting form normally
        if (messageInput) {
            messageInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    console.log("Enter key pressed in message input");
                    e.preventDefault();
                    sendMessage();
                }
            });
        }
    }

    if (messageInput) {
        messageInput.addEventListener('input', function() {
            chatState.userIsActive = true;
            if (shouldAllowMarkAsRead()) {
                markNotificationsOnUserAction();
            }
        });

        messageInput.addEventListener('focus', function() {
            chatState.userIsActive = true;
            if (chatState.focusTimeout) {
                clearTimeout(chatState.focusTimeout);
            }
            chatState.focusTimeout = setTimeout(() => {
                if (shouldAllowMarkAsRead()) {
                    markNotificationsOnUserAction();
                }
            }, 2000);
        });

        messageInput.addEventListener('blur', function() {
            if (chatState.focusTimeout) {
                clearTimeout(chatState.focusTimeout);
                chatState.focusTimeout = null;
            }
        });
    }

    if (chatMessages) {
        chatMessages.addEventListener('click', function() {
            chatState.userIsActive = true;
            if (shouldAllowMarkAsRead()) {
                markNotificationsOnUserAction();
            }
        });
    }

    console.log("Event listeners setup complete");
}

// ===== POLLING =====
function startPolling() {
    console.log("Starting polling...");
    setInterval(fetchNewMessages, 3000);
    setInterval(fetchUpdatedConversations, 4000);
}

// ===== INITIALIZATION =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== RECRUITER CHAT SYSTEM INITIALIZING ===');

    // Test CSRF first
    console.log('Testing CSRF configuration...');
    console.log('CSRF Name:', csrfName);
    console.log('CSRF Value:', csrf ? csrf.substring(0, 10) + '...' : 'EMPTY');

    // Make initial test call
    ajax_post('ajax_get_conversations', {
        init: true
    }, function(data) {
        console.log('CSRF Test:', data.success ? 'PASSED ✓' : 'FAILED ✗');
        console.log('Test response:', data);

        if (data.success) {
            console.log('Recruiter chat system ready! Starting...');

            // Initial scroll
            setTimeout(scrollToBottom, 100);

            // Setup event listeners
            setupEventListeners();

            // Start polling
            startPolling();

            // Focus on input
            const messageInput = document.getElementById('messageInput');
            if (messageInput) {
                setTimeout(() => {
                    console.log("Focusing on message input");
                    messageInput.focus();
                }, 500);
            }

            console.log('=== RECRUITER CHAT SYSTEM INITIALIZED SUCCESSFULLY ===');
        } else {
            console.error('CSRF test failed:', data.message);
            console.error('Please check CSRF configuration');
            alert('Chat system initialization failed: ' + data.message);
        }
    });
});
</script>