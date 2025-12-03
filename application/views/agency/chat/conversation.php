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
    border-radius: 20px;
    margin: 20px 0;
    padding: 0;
    overflow: hidden;
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

/* Recruiter Profile Section */
.recruiter-profile {
    text-align: center;
    padding: 20px 16px;
}

.recruiter-avatar-large {
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

.recruiter-avatar-large.online::before {
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

.recruiter-avatar-large.offline::before {
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

.recruiter-name-large {
    color: white;
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 4px;
}

.recruiter-status {
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

/* Candidate Chat Header Styles */
.candidate-chat-header {
    position: relative;
    overflow: hidden;
}

.z-index-2 {
    z-index: 2;
    position: relative;
}

/* Badge styling for candidate tags */
.candidate-badge {
    background: linear-gradient(135deg, #8B5CF6, #7C3AED);
    color: white;
    border-radius: 8px;
    padding: 4px 8px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    box-shadow: 0 2px 8px rgba(139, 92, 246, 0.3);
}

/* Progress bar styling */
.progress {
    height: 6px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 3px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(135deg, #10B981, #059669);
    border-radius: 3px;
    transition: width 0.3s ease;
}

/* Status badge colors */
.badge-hired {
    background: linear-gradient(135deg, #10B981, #059669);
}

.badge-rejected {
    background: linear-gradient(135deg, #EF4444, #DC2626);
}

.badge-pending {
    background: linear-gradient(135deg, #F59E0B, #D97706);
}

/* Sidebar candidate info */
.sidebar-candidate-info {
    background: rgba(139, 92, 246, 0.1);
    border-radius: 6px;
    padding: 4px 8px;
    margin-top: 4px;
    border-left: 2px solid #8B5CF6;
}

/* Chat Type Indicators */
.chat-type-badge {
    font-size: 0.65rem;
    padding: 2px 6px;
    border-radius: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    display: inline-flex;
    align-items: center;
    gap: 3px;
}

.chat-type-indicator {
    position: absolute;
    top: -2px;
    left: -2px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #202225;
    z-index: 1;
}

/* Different colors for chat types */
.candidate-chat-indicator {
    background: linear-gradient(135deg, #8B5CF6, #7C3AED);
    box-shadow: 0 0 8px rgba(139, 92, 246, 0.5);
}

.general-chat-indicator {
    background: linear-gradient(135deg, #10B981, #059669);
    box-shadow: 0 0 8px rgba(16, 185, 129, 0.5);
}

/* Section headers with icons */
.sidebar-section h6 i {
    margin-right: 6px;
    font-size: 0.8rem;
}

/* Quick action buttons */
.chat-quick-actions {
    display: flex;
    gap: 6px;
    margin-top: 8px;
}

.chat-quick-actions .btn {
    padding: 4px 8px;
    font-size: 0.7rem;
    border-radius: 6px;
    border: none;
    font-weight: 500;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    min-width: 70px;
}

.chat-quick-actions .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Avatar colors based on chat type */
.avatar.candidate {
    background: linear-gradient(135deg, #8B5CF6, #7C3AED) !important;
}

.avatar.general {
    background: linear-gradient(135deg, #10B981, #059669) !important;
}

.avatar.new {
    background: linear-gradient(135deg, #F59E0B, #D97706) !important;
}
</style>
<div id="main-content">
    <!-- Cosmic Chat Header - Dark Theme -->
    <div class="cosmic-chat-header">
        <div class="cosmic-container">
            <!-- Left: Navigation & Chat Info -->
            <div class="cosmic-navigation">
                <div class="nav-path">
                    <a href="<?php echo site_url('agency/dashboard'); ?>" class="nav-item cosmic-glow">
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
                                    <?php echo substr(htmlspecialchars($conversation->recruiter_name), 0, 1); ?>
                                </div>
                                <div
                                    class="status-indicator <?php echo (isset($conversation->is_online) && $conversation->is_online) ? 'online' : 'offline'; ?>">
                                    <div class="pulse-ring"></div>
                                </div>
                            </div>
                        </div>
                        <div class="conversation-info">
                            <h4 class="recruiter-name">
                                <?php echo htmlspecialchars($conversation->recruiter_name); ?></h4>
                            <p class="conversation-context">
                                <?php if (!empty($conversation->candidate_id)): ?>
                                <span style="color: #8B5CF6; margin-right: 8px;">
                                    <i class="fa fa-user"></i> Candidate Chat
                                </span>
                                <?php else: ?>
                                <span style="color: #10B981; margin-right: 8px;">
                                    <i class="fa fa-comments"></i> General Chat
                                </span>
                                <?php endif; ?>

                                <i class="fa fa-briefcase"></i>
                                <?php echo $conversation->job_name ? htmlspecialchars($conversation->job_name) : 'Direct Message'; ?>

                                <?php if (!empty($conversation->candidate_id) && !empty($candidate_details)): ?>
                                <br>
                                <i class="fa fa-user" style="margin-right: 5px;"></i>
                                <span style="color: var(--text-accent);">
                                    <?= htmlspecialchars($candidate_details->first_name . ' ' . $candidate_details->last_name) ?>
                                </span>
                                <?php elseif (!empty($conversation->candidate_id)): ?>
                                <br>
                                <i class="fa fa-user" style="margin-right: 5px;"></i>
                                <span style="color: var(--text-accent);">
                                    Candidate #<?= $conversation->candidate_id ?>
                                </span>
                                <?php endif; ?>
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

    <!-- Candidate Chat Header -->
    <?php if (!empty($candidate_details)): ?>
    <div class="candidate-chat-header cosmic-glow"
        style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(16, 185, 129, 0.1)); border: 1px solid var(--light-border); border-radius: 16px; padding: 20px; margin: 20px 20px 0 20px; position: relative; overflow: hidden;">

        <div class="cosmic-background" style="opacity: 0.3;">
            <div class="floating-orb orb-1" style="background: radial-gradient(circle, #10B981, transparent);"></div>
            <div class="floating-orb orb-2" style="background: radial-gradient(circle, #8B5CF6, transparent);"></div>
        </div>

        <div class="d-flex justify-content-between align-items-center position-relative z-index-2">
            <div style="flex: 1;">
                <h4
                    style="color: var(--text-primary); font-weight: 600; margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                    <i class="fa fa-user-circle" style="color: #8B5CF6;"></i>
                    Discussing Candidate
                    <span class="cosmic-badge" style="background: var(--primary-gradient);">
                        <i class="fa fa-user"></i> Candidate Chat
                    </span>
                </h4>

                <div style="display: flex; flex-wrap: wrap; gap: 20px;">
                    <!-- Candidate Info -->
                    <div style="min-width: 200px;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                            <div
                                style="width: 40px; height: 40px; background: var(--primary-gradient); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                <?= substr(htmlspecialchars($candidate_details->first_name), 0, 1) ?>
                            </div>
                            <div>
                                <h5 style="color: var(--text-primary); margin: 0; font-size: 1.1rem;">
                                    <?= htmlspecialchars($candidate_details->first_name . ' ' . $candidate_details->last_name) ?>
                                </h5>
                                <small style="color: var(--text-muted);">
                                    Ref: <?= htmlspecialchars($candidate_details->reference_number) ?>
                                </small>
                            </div>
                        </div>

                        <div style="display: flex; flex-wrap: wrap; gap: 15px; margin-top: 10px;">
                            <?php if (!empty($candidate_details->email)): ?>
                            <div style="display: flex; align-items: center; gap: 5px;">
                                <i class="fa fa-envelope" style="color: var(--text-muted); font-size: 0.8rem;"></i>
                                <span style="color: var(--text-secondary); font-size: 0.85rem;">
                                    <?= htmlspecialchars($candidate_details->email) ?>
                                </span>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($candidate_details->phone)): ?>
                            <div style="display: flex; align-items: center; gap: 5px;">
                                <i class="fa fa-phone" style="color: var(--text-muted); font-size: 0.8rem;"></i>
                                <span style="color: var(--text-secondary); font-size: 0.85rem;">
                                    <?= htmlspecialchars($candidate_details->phone) ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Job & Status Info -->
                    <div style="min-width: 200px;">
                        <?php if (!empty($candidate_details->job_name)): ?>
                        <div style="margin-bottom: 10px;">
                            <div style="display: flex; align-items: center; gap: 5px; margin-bottom: 5px;">
                                <i class="fa fa-briefcase" style="color: var(--text-muted); font-size: 0.8rem;"></i>
                                <strong style="color: var(--text-secondary); font-size: 0.85rem;">Job:</strong>
                            </div>
                            <div
                                style="background: rgba(255, 255, 255, 0.05); padding: 8px 12px; border-radius: 8px; border: 1px solid var(--light-border);">
                                <span style="color: var(--text-primary); font-weight: 500;">
                                    <?= htmlspecialchars($candidate_details->job_name) ?>
                                </span>
                                <?php if (!empty($candidate_details->job_ref)): ?>
                                <small style="color: var(--text-muted); margin-left: 8px;">
                                    (<?= htmlspecialchars($candidate_details->job_ref) ?>)
                                </small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($candidate_details->status)): ?>
                        <div>
                            <div style="display: flex; align-items: center; gap: 5px; margin-bottom: 5px;">
                                <i class="fa fa-chart-line" style="color: var(--text-muted); font-size: 0.8rem;"></i>
                                <strong style="color: var(--text-secondary); font-size: 0.85rem;">Status:</strong>
                            </div>
                            <span class="badge" style="background: <?= 
                            $candidate_details->status == 'hired' ? 'linear-gradient(135deg, #10B981, #059669)' : 
                            ($candidate_details->status == 'rejected' ? 'linear-gradient(135deg, #EF4444, #DC2626)' : 
                            'linear-gradient(135deg, #F59E0B, #D97706)') ?>; 
                            color: white; padding: 6px 12px; border-radius: 8px; font-size: 0.8rem;">
                                <?= ucfirst($candidate_details->status) ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Onboarding Progress -->
                    <?php if (!empty($candidate_details->onboarding_stage) && $candidate_details->onboarding_stage != 'not_started'): ?>
                    <div style="min-width: 200px;">
                        <div style="display: flex; align-items: center; gap: 5px; margin-bottom: 5px;">
                            <i class="fa fa-tasks" style="color: var(--text-muted); font-size: 0.8rem;"></i>
                            <strong style="color: var(--text-secondary); font-size: 0.85rem;">Onboarding:</strong>
                        </div>
                        <div
                            style="background: rgba(255, 255, 255, 0.05); padding: 10px; border-radius: 8px; border: 1px solid var(--light-border);">
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <span style="color: var(--text-primary); font-size: 0.85rem; font-weight: 500;">
                                    <?= ucwords(str_replace('_', ' ', $candidate_details->onboarding_stage)) ?>
                                </span>
                                <?php if (!empty($candidate_details->onboarding_progress)): ?>
                                <span style="color: var(--text-accent); font-weight: 600; font-size: 0.9rem;">
                                    <?= round($candidate_details->onboarding_progress) ?>%
                                </span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($candidate_details->onboarding_progress)): ?>
                            <div class="progress"
                                style="height: 6px; background: rgba(255, 255, 255, 0.1); border-radius: 3px; overflow: hidden;">
                                <div class="progress-bar" role="progressbar"
                                    style="width: <?= $candidate_details->onboarding_progress ?>%; background: var(--accent-gradient);">
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- View Profile Button -->
            <div style="margin-left: 20px;">
                <a href="<?= site_url('agency/candidates/view/' . $candidate_details->id) ?>" target="_blank"
                    class="btn cosmic-glow"
                    style="background: var(--primary-gradient); color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 500; display: flex; align-items: center; gap: 8px; text-decoration: none; transition: all 0.3s ease;">
                    <i class="fa fa-external-link-alt"></i>
                    View Full Profile
                </a>
            </div>
        </div>
    </div>
    <?php elseif (isset($conversation) && !empty($conversation->candidate_id) && empty($candidate_details)): ?>
    <div class="candidate-chat-header cosmic-glow"
        style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.1)); border: 1px solid var(--light-border); border-radius: 16px; padding: 20px; margin: 20px 20px 0 20px;">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 style="color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: 10px;">
                    <i class="fa fa-exclamation-triangle" style="color: #EF4444;"></i>
                    Candidate Discussion
                </h5>
                <p style="color: var(--text-muted); margin: 8px 0 0 0;">
                    <strong>Candidate ID:</strong> <?= $conversation->candidate_id ?>
                    <small style="margin-left: 8px;">(Profile access restricted)</small>
                </p>
            </div>
            <span class="badge"
                style="background: var(--warning-gradient); color: white; padding: 6px 12px; border-radius: 8px;">
                Restricted Access
            </span>
        </div>
    </div>
    <?php endif; ?>

    <div class="container-fluid p-0" style="height: 64vh; overflow: hidden;">
        <div class="row no-gutters" style="height: 100%;">
            <!-- Left Sidebar: Conversations & Recruiters List -->
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

                <!-- Chat Type Tabs -->
                <div class="px-3 pt-3" style="border-bottom: 1px solid #36393f;">
                    <div class="d-flex" style="gap: 5px;">
                        <button
                            class="btn btn-sm flex-fill chat-type-tab <?= empty($_GET['chat_type']) || $_GET['chat_type'] == 'all' ? 'active' : '' ?>"
                            data-chat-type="all" style="background: <?= empty($_GET['chat_type']) || $_GET['chat_type'] == 'all' ? 'linear-gradient(135deg, #7289da, #5b6eae)' : '#2f3136' ?>; 
                                   color: white; border: none; border-radius: 8px; padding: 8px 0; font-size: 0.8rem;">
                            <i class="fa fa-comments mr-1"></i> All Chats
                        </button>
                        <button
                            class="btn btn-sm flex-fill chat-type-tab <?= isset($_GET['chat_type']) && $_GET['chat_type'] == 'candidate' ? 'active' : '' ?>"
                            data-chat-type="candidate" style="background: <?= isset($_GET['chat_type']) && $_GET['chat_type'] == 'candidate' ? 'linear-gradient(135deg, #8B5CF6, #7C3AED)' : '#2f3136' ?>; 
                                   color: white; border: none; border-radius: 8px; padding: 8px 0; font-size: 0.8rem;">
                            <i class="fa fa-user mr-1"></i> Candidate
                        </button>
                        <button
                            class="btn btn-sm flex-fill chat-type-tab <?= isset($_GET['chat_type']) && $_GET['chat_type'] == 'general' ? 'active' : '' ?>"
                            data-chat-type="general" style="background: <?= isset($_GET['chat_type']) && $_GET['chat_type'] == 'general' ? 'linear-gradient(135deg, #10B981, #059669)' : '#2f3136' ?>; 
                                   color: white; border: none; border-radius: 8px; padding: 8px 0; font-size: 0.8rem;">
                            <i class="fa fa-comments mr-1"></i> General
                        </button>
                    </div>
                </div>

                <!-- Enhanced Sidebar with Notifications -->
                <div class="flex-grow-1 overflow-auto" style="padding: 0 10px;">
                    <?php 
                    // Filter conversations based on tab selection
                    $filtered_conversations = $all_conversations;
                    if (isset($_GET['chat_type']) && $_GET['chat_type'] == 'candidate') {
                        $filtered_conversations = array_filter($all_conversations, function($conv) {
                            return !empty($conv->candidate_id);
                        });
                    } elseif (isset($_GET['chat_type']) && $_GET['chat_type'] == 'general') {
                        $filtered_conversations = array_filter($all_conversations, function($conv) {
                            return empty($conv->candidate_id);
                        });
                    }
                    ?>

                    <div class="sidebar-section">
                        <h6 class="text-muted px-3 py-2" style="font-size: 0.8rem; letter-spacing: 0.5px;">
                            <?php if (isset($_GET['chat_type']) && $_GET['chat_type'] == 'candidate'): ?>
                            <i class="fa fa-user mr-1" style="color: #8B5CF6;"></i> CANDIDATE CHATS
                            <?php elseif (isset($_GET['chat_type']) && $_GET['chat_type'] == 'general'): ?>
                            <i class="fa fa-comments mr-1" style="color: #10B981;"></i> GENERAL CHATS
                            <?php else: ?>
                            <i class="fa fa-comments mr-1"></i> ALL CHATS
                            <?php endif; ?>

                            <?php if (isset($total_unread_count) && $total_unread_count > 0): ?>
                            <span class="section-badge"><?php echo $total_unread_count; ?></span>
                            <?php endif; ?>
                        </h6>

                        <?php if (empty($filtered_conversations)): ?>
                        <div class="text-center py-4">
                            <i class="fa fa-comments fa-2x mb-2" style="color: #7289da;"></i>
                            <p class="text-muted" style="font-size: 0.8rem;">
                                <?php if (isset($_GET['chat_type']) && $_GET['chat_type'] == 'candidate'): ?>
                                No candidate chats yet
                                <?php elseif (isset($_GET['chat_type']) && $_GET['chat_type'] == 'general'): ?>
                                No general chats yet
                                <?php else: ?>
                                No chats yet
                                <?php endif; ?>
                            </p>
                        </div>
                        <?php else: ?>
                        <div class="list-group" style="background-color: transparent;">
                            <?php foreach ($filtered_conversations as $conv): ?>
                            <a href="<?php echo site_url('agency/chat/conversation/' .  $conv->uuid); ?>"
                                class="list-group-item list-group-item-action d-flex align-items-center conversation-item <?php echo (isset($conversation) && $conversation->uuid == $conv->uuid) ? 'active' : ''; ?>"
                                style="border: none; border-radius: 8px; margin-bottom: 5px; padding: 10px 15px; transition: all 0.2s; <?= !empty($conv->candidate_id) ? 'border-left: 3px solid #8B5CF6 !important;' : 'border-left: 3px solid #10B981 !important;' ?>"
                                data-conversation-id="<?php echo $conv->uuid; ?>">

                                <div class="conversation-avatar mr-3 position-relative">
                                    <div class="avatar"
                                        style="width: 40px; height: 40px; border-radius: 50%; background-color: <?= !empty($conv->candidate_id) ? '#8B5CF6' : '#10B981' ?>; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #ffffff;">
                                        <?php echo substr(htmlspecialchars($conv->recruiter_name), 0, 1); ?>
                                    </div>
                                    <?php if (isset($conv->is_online) && $conv->is_online): ?>
                                    <div class="online-indicator"></div>
                                    <?php endif; ?>
                                </div>

                                <div class="flex-grow-1" style="min-width: 0;">
                                    <!-- Recruiter Name with Chat Type Badge -->
                                    <div class="d-flex align-items-center mb-1">
                                        <h6 class="mb-0" style="color: #ffffff; font-weight: 500; margin-right: 8px;">
                                            <?php echo htmlspecialchars($conv->recruiter_name); ?>
                                        </h6>

                                        <!-- Chat Type Badge -->
                                        <span class="chat-type-badge"
                                            style="font-size: 0.65rem; padding: 2px 6px; border-radius: 10px; <?= !empty($conv->candidate_id) ? 'background: rgba(139, 92, 246, 0.2); color: #C4B5FD;' : 'background: rgba(16, 185, 129, 0.2); color: #A7F3D0;' ?>">
                                            <?php if (!empty($conv->candidate_id)): ?>
                                            <i class="fa fa-user mr-1" style="font-size: 0.6rem;"></i> Candidate
                                            <?php else: ?>
                                            <i class="fa fa-comments mr-1" style="font-size: 0.6rem;"></i> General
                                            <?php endif; ?>
                                        </span>
                                    </div>

                                    <!-- Candidate Info (only for candidate chats) -->
                                    <?php if (!empty($conv->candidate_info)): ?>
                                    <small class="d-block"
                                        style="color: #C4B5FD; font-size: 0.7rem; font-weight: 500; margin: 3px 0; display: flex; align-items: center; gap: 4px;">
                                        <i class="fa fa-user-circle" style="font-size: 0.6rem;"></i>
                                        <?= htmlspecialchars($conv->candidate_info->first_name . ' ' . $conv->candidate_info->last_name) ?>
                                        <span
                                            style="color: rgba(196, 181, 253, 0.7);">(<?= htmlspecialchars($conv->candidate_info->reference_number) ?>)</span>
                                    </small>
                                    <?php endif; ?>

                                    <!-- Job Info -->
                                    <?php if (!empty($conv->job_name)): ?>
                                    <small class="d-block"
                                        style="color: #9CA3AF; font-size: 0.7rem; margin: 2px 0; display: flex; align-items: center; gap: 4px;">
                                        <i class="fa fa-briefcase" style="font-size: 0.6rem;"></i>
                                        <?= htmlspecialchars($conv->job_name) ?>
                                    </small>
                                    <?php endif; ?>

                                    <!-- Message Preview -->
                                    <small class="text-muted d-block conversation-preview"
                                        style="font-size: 0.75rem; margin-top: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                        data-conversation-id="<?= $conv->uuid; ?>">
                                        <?php if (isset($conv->unread_count) && $conv->unread_count > 0): ?>
                                        <strong
                                            style="color: #ffffff;"><?php echo htmlspecialchars($conv->last_message ?: 'New message'); ?></strong>
                                        <?php else: ?>
                                        <?php echo htmlspecialchars($conv->last_message ?: 'No messages yet'); ?>
                                        <?php endif; ?>
                                    </small>
                                </div>

                                <div class="text-right ml-2" style="min-width: 40px;">
                                    <small class="text-muted d-block conversation-time" style="font-size: 0.7rem;"
                                        data-conversation-id="<?php echo $conv->uuid; ?>">
                                        <?php echo time_ago($conv->last_message_at ?: $conv->created_at); ?>
                                    </small>
                                    <?php if (isset($conv->unread_count) && $conv->unread_count > 0): ?>
                                    <span class="conversation-badge" data-conversation-id="<?php echo $conv->uuid; ?>"
                                        style="<?= !empty($conv->candidate_id) ? 'background: linear-gradient(135deg, #8B5CF6, #7C3AED);' : 'background: linear-gradient(135deg, #10B981, #059669);' ?>">
                                        <?php echo $conv->unread_count > 99 ? '99+' : $conv->unread_count; ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Available Recruiters Section -->
                    <?php if (!empty($available_recruiters)): ?>
                    <div class="sidebar-section" style="margin-top: 15px;">
                        <h6 class="text-muted px-3 py-2" style="font-size: 0.8rem; letter-spacing: 0.5px;">
                            START NEW CHAT
                            <span class="section-badge new"
                                style="background: linear-gradient(135deg, #F59E0B, #D97706);">New</span>
                        </h6>
                        <div class="list-group" style="background-color: transparent;">
                            <?php foreach ($available_recruiters as $recruiter): ?>
                            <?php 
                            // Check if this recruiter already has a conversation
                            $has_conversation = false;
                            foreach ($all_conversations as $conv) {
                                if ($conv->recruiter_id == $recruiter->id) {
                                    $has_conversation = true;
                                    break;
                                }
                            }
                            ?>

                            <?php if (!$has_conversation): ?>
                            <div class="list-group-item d-flex align-items-center recruiter-item"
                                style="border: none; border-radius: 8px; margin-bottom: 5px; padding: 10px 15px; background: rgba(245, 158, 11, 0.05);">
                                <div class="recruiter-avatar mr-3 position-relative">
                                    <div class="avatar"
                                        style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #F59E0B, #D97706); display: flex; align-items: center; justify-content: center; font-weight: bold; color: #ffffff;">
                                        <?php echo substr(htmlspecialchars($recruiter->first_name . ' ' . $recruiter->last_name), 0, 1); ?>
                                    </div>
                                    <div class="online-indicator"></div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0" style="color: #ffffff; font-weight: 500;">
                                        <?php echo htmlspecialchars($recruiter->first_name . ' ' . $recruiter->last_name); ?>
                                    </h6>
                                    <small class="text-muted d-block" style="font-size: 0.75rem; margin-top: 3px;">
                                        <span class="text-warning">● Online</span>
                                    </small>
                                </div>
                                <div class="d-flex flex-column ml-2" style="gap: 5px;">
                                    <a href="<?php echo site_url('agency/chat/quick_start/' . $recruiter->id); ?>"
                                        class="btn btn-sm"
                                        style="background: linear-gradient(135deg, #10B981, #059669); color: white; border: none; padding: 4px 8px; border-radius: 6px; font-size: 0.7rem; width: 70px;">
                                        <i class="fa fa-comments"></i> General
                                    </a>
                                    <a href="<?php echo site_url('agency/candidates/start_candidate_chat/' . $recruiter->id); ?>"
                                        class="btn btn-sm"
                                        style="background: linear-gradient(135deg, #8B5CF6, #7C3AED); color: white; border: none; padding: 4px 8px; border-radius: 6px; font-size: 0.7rem; width: 70px;">
                                        <i class="fa fa-user"></i> Candidate
                                    </a>
                                </div>
                            </div>
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
                2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z\' fill=\"%2391a29e\" fill-opacity=\"0.1\"
                fill-rule=\"evenodd\"/%3E%3C/svg%3E');">

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
                                    <?php echo substr(htmlspecialchars($conversation->recruiter_name), 0, 1); ?>
                                </div>
                                <div>
                                    <h5 class="mb-0" style="color: #ffffffff; font-weight: 500; font-size: 1.2rem;">
                                        <?php echo htmlspecialchars($conversation->recruiter_name); ?></h5>
                                    <small class="text-muted" style="font-size: 0.75rem; color:#a0a0a0ff!important;">
                                        <?php echo $conversation->job_name ? 'Job: ' . htmlspecialchars($conversation->job_name) : 'Online'; ?>
                                    </small>
                                </div>
                            </div>

                            <!-- Switch Chat Type Button -->
                            <div class="d-flex align-items-center gap-2">
                                <?php if (!empty($conversation->candidate_id)): ?>
                                <!-- Switch from Candidate to General -->
                                <a href="<?php echo site_url('agency/chat/switch_to_general/' . $conversation->uuid . '/' . $conversation->recruiter_id); ?>"
                                    class="btn btn-sm"
                                    style="background: linear-gradient(135deg, #10B981, #059669); color: white; border: none; border-radius: 6px; padding: 5px 10px; font-size: 0.7rem; display: flex; align-items: center; gap: 5px; text-decoration: none;">
                                    <i class="fa fa-exchange-alt"></i> Switch to General
                                </a>
                                <?php else: ?>
                                <!-- Switch from General to Candidate (show if you want to start a candidate chat) -->

                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Messages Area - WhatsApp Style -->
                        <div class="flex-grow-1 overflow-auto p-2" id="chatMessages"
                            style="display: block; padding: 8px 12px; position: relative; height: calc(74vh - 120px); overflow-y: auto;">
                            <?php if (!empty($messages)): ?>
                            <?php foreach ($messages as $message): ?>
                            <div
                                class="d-flex <?php echo $message->sender_type == 'agency' ? 'justify-content-end' : 'justify-content-start'; ?> mb-2">
                                <div class="message-container" style="max-width: 70%;">
                                    <div
                                        class="message-content d-flex align-items-baseline <?php echo $message->sender_type == 'agency' ? 'justify-content-end' : 'justify-content-start'; ?>">
                                        <div class="message-text-time d-inline-flex align-items-baseline" style="background-color: <?php echo $message->sender_type == 'agency' ? '#dcf8c6' : '#ffffff'; ?>; 
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
                                                <?php if ($message->sender_type == 'agency'): ?>
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
                            <form id="messageForm" class="h-100" onsubmit="return false;">

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

                                <!-- SECURE: UUID for API calls -->
                                <input type="hidden" id="conversationUuid"
                                    value="<?php echo isset($conversation) ? $conversation->uuid : ''; ?>">

                                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                                    value="<?php echo $this->security->get_csrf_hash(); ?>">
                            </form>
                        </div>
                    </div>
                </div>

                <?php else: ?>
                <!-- No Conversation Selected -->
                <div class="d-flex align-items-center justify-content-center h-100" style="flex: 1;">
                    <div class="text-center text-muted">
                        <i class="fa fa-comments fa-4x mb-3" style="color: #128C7E;"></i>
                        <h4>Welcome to Chat</h4>
                        <p>Select a conversation from the left sidebar to start chatting,<br>or start a new chat with an
                            available recruiter.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<script>
// ============================================
// AGENCY CHAT SYSTEM - CLEAN & STRUCTURED
// ============================================

'use strict';

// ===== CONFIGURATION =====
const CONFIG = {
    POLL_INTERVAL: 3000, // 3 seconds
    CONVERSATION_POLL: 8000, // 8 seconds
    CSRF_TOKEN_NAME: '<?php echo $this->security->get_csrf_token_name(); ?>',
    BASE_PATH: window.dynamicPath || window.location.origin + '/shoesmith/agency'
};

// ===== STATE MANAGEMENT =====
class ChatState {
    constructor() {
        this.isSending = false;
        this.isPolling = false;
        this.lastMessageId = <?php echo !empty($messages) ? end($messages)->id : 0; ?>;
        this.conversationUuid = document.getElementById('conversationUuid')?.value || null;
        this.displayedMessageIds = new Set();
        this.pollInterval = null;
        this.conversationInterval = null;

        // Initialize CSRF
        this.initCsrf();
    }

    initCsrf() {
        if (typeof csrfName === 'undefined') {
            window.csrfName = CONFIG.CSRF_TOKEN_NAME;
        }
        if (typeof csrf === 'undefined') {
            window.csrf = '<?php echo $this->security->get_csrf_hash(); ?>';
        }
    }

    addDisplayedMessageId(id) {
        if (id && !id.toString().startsWith('temp_')) {
            this.displayedMessageIds.add(parseInt(id));
        }
    }

    isMessageDisplayed(id) {
        return this.displayedMessageIds.has(parseInt(id));
    }
}

// ===== UTILITIES =====
const Utils = {
    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    scrollToBottom() {
        const container = document.getElementById('chatMessages');
        if (container) {
            setTimeout(() => {
                container.scrollTop = container.scrollHeight;
            }, 100);
        }
    },

    getTimeAgo(timestamp) {
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
    },

    showNotification(message, type = 'info') {
        const types = {
            info: {
                icon: 'info-circle',
                color: '#17a2b8'
            },
            success: {
                icon: 'check-circle',
                color: '#28a745'
            },
            error: {
                icon: 'exclamation-circle',
                color: '#dc3545'
            },
            warning: {
                icon: 'exclamation-triangle',
                color: '#ffc107'
            }
        };

        const config = types[type] || types.info;
        console.log(`${type.toUpperCase()}: ${message}`);
    }
};

// ===== MESSAGE MANAGER =====
class MessageManager {
    constructor(state) {
        this.state = state;
    }

    // Send a message
    async send(messageText) {
        if (this.state.isSending || !messageText.trim() || !this.state.conversationUuid) {
            return false;
        }

        this.state.isSending = true;
        const input = document.getElementById('messageInput');
        const originalMessage = messageText;

        // Clear input
        input.value = '';

        // Show optimistic message
        const tempId = this.showOptimisticMessage(originalMessage);

        try {
            const response = await this.ajaxRequest('ajax_send_message', {
                conversation_uuid: this.state.conversationUuid,
                message: originalMessage
            });

            if (response.success) {
                this.updateOptimisticMessage(tempId, response.message_id);
                this.state.lastMessageId = parseInt(response.message_id);
                Utils.showNotification('Message sent', 'success');
                return true;
            } else {
                throw new Error(response.message || 'Send failed');
            }

        } catch (error) {
            this.handleSendError(tempId, originalMessage, error.message);
            return false;
        } finally {
            this.state.isSending = false;
            input.focus();
        }
    }

    // Show optimistic (temporary) message
    showOptimisticMessage(text) {
        const container = document.getElementById('chatMessages');
        if (!container) return null;

        const tempId = Date.now();
        const message = document.createElement('div');
        message.className = 'd-flex justify-content-end mb-2';
        message.dataset.tempId = tempId;
        message.dataset.messageId = 'temp_' + tempId;

        message.innerHTML = `
            <div class="message-container" style="max-width: 70%;">
                <div class="message-content d-flex align-items-baseline justify-content-end">
                    <div class="message-text-time d-inline-flex align-items-baseline" 
                         style="background-color: #dcf8c6; padding: 8px 12px; border-radius: 7.5px; box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);">
                        <span class="message-text" style="font-size: 14.2px; color: #303030; line-height: 1.3; margin-right: 8px;">
                            ${Utils.escapeHtml(text)}
                        </span>
                        <span class="message-meta d-inline-flex align-items-center">
                            <small class="message-time" style="font-size: 11px; color: #667781;">
                                Sending...
                            </small>
                            <i class="fa fa-clock ml-1" style="font-size: 10px; color: #667781;"></i>
                        </span>
                    </div>
                </div>
            </div>
        `;

        container.appendChild(message);
        Utils.scrollToBottom();
        return tempId;
    }

    // Update optimistic message with real ID
    updateOptimisticMessage(tempId, realId) {
        const msg = document.querySelector(`[data-temp-id="${tempId}"]`);
        if (msg) {
            msg.dataset.messageId = realId;
            delete msg.dataset.tempId;

            const icon = msg.querySelector('.fa-clock');
            const time = msg.querySelector('.message-time');

            if (icon) {
                icon.className = 'fa fa-check ml-1';
                icon.style.color = '#128C7E';
            }
            if (time) time.textContent = 'Sent';

            this.state.addDisplayedMessageId(realId);
        }
    }

    // Handle send error
    handleSendError(tempId, originalMessage, error) {
        const input = document.getElementById('messageInput');
        const msg = document.querySelector(`[data-temp-id="${tempId}"]`);

        // Restore message to input
        input.value = originalMessage;

        // Remove optimistic message
        if (msg) msg.remove();

        Utils.showNotification(`Send failed: ${error}`, 'error');
    }

    // Fetch new messages
    async fetchNew() {
        if (this.state.isPolling || !this.state.conversationUuid) {
            return;
        }

        this.state.isPolling = true;

        try {
            const response = await this.ajaxRequest('ajax_get_messages', {
                conversation_uuid: this.state.conversationUuid,
                last_message_id: this.state.lastMessageId || 0
            });

            if (response.success && response.html) {
                this.appendMessages(response.html);

                if (response.last_message_id) {
                    this.state.lastMessageId = parseInt(response.last_message_id);
                }
            }

        } catch (error) {
            console.error('Poll error:', error.message);
        } finally {
            this.state.isPolling = false;
        }
    }

    // Append messages to chat
    appendMessages(html) {
        const container = document.getElementById('chatMessages');
        if (!container || !html.trim()) return;

        // Parse HTML
        const temp = document.createElement('div');
        temp.innerHTML = html;
        const newMessages = Array.from(temp.children);

        let added = 0;

        // Filter and append unique messages
        newMessages.forEach(msg => {
            const msgId = msg.dataset.messageId;

            if (!msgId || !this.state.isMessageDisplayed(msgId)) {
                container.appendChild(msg);
                this.state.addDisplayedMessageId(msgId);
                added++;
            }
        });

        if (added > 0) {
            Utils.scrollToBottom();
        }
    }

    // AJAX request wrapper
    async ajaxRequest(endpoint, data) {
        return new Promise((resolve, reject) => {
            if (typeof ajax_post !== 'function') {
                reject(new Error('ajax_post not available'));
                return;
            }

            // Add CSRF token
            if (window.csrfName && window.csrf) {
                data[window.csrfName] = window.csrf;
            }

            ajax_post(endpoint, data, (response) => {
                // Update CSRF if provided
                if (response && response.csrf) {
                    window.csrf = response.csrf;
                }

                if (response && !response.success && response.message && response.message.includes(
                        'CSRF')) {
                    this.refreshCsrfToken();
                }

                resolve(response || {
                    success: false,
                    message: 'No response'
                });
            });
        });
    }

    // Refresh CSRF token
    refreshCsrfToken() {
        console.log('Refreshing CSRF token...');

        fetch(`${CONFIG.BASE_PATH}/ajax_get_conversations?refresh_csrf=1`, {
                method: 'GET',
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.csrf) {
                    window.csrf = data.csrf;
                    console.log('CSRF token refreshed');
                }
            })
            .catch(error => console.error('CSRF refresh failed:', error));
    }
}

// ===== CONVERSATION MANAGER =====
class ConversationManager {
    constructor(state) {
        this.state = state;
    }

    // Fetch updated conversations
    async fetchUpdated() {
        try {
            const response = await this.ajaxRequest('ajax_get_conversations', {});

            if (response.success && response.conversations) {
                this.updateSidebar(response.conversations);

                // Update notification badges
                if (response.total_unread_count !== undefined) {
                    this.updateNotificationBadges(response.total_unread_count);
                }
            }

        } catch (error) {
            console.error('Conversation fetch error:', error.message);
        }
    }

    // Update sidebar conversations
    updateSidebar(conversations) {
        conversations.forEach(conv => {
            const item = document.querySelector(`.conversation-item[data-conversation-id="${conv.id}"]`);
            if (item) {
                this.updateConversationItem(item, conv);
            }
        });
    }

    // Update single conversation item
    updateConversationItem(item, conv) {
        // Update badge
        let badge = item.querySelector('.conversation-badge');

        if (conv.unread_count > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'conversation-badge';
                badge.dataset.conversationId = conv.id;

                const textRightDiv = item.querySelector('.text-right');
                if (textRightDiv) textRightDiv.appendChild(badge);
            }

            badge.textContent = conv.unread_count > 99 ? '99+' : conv.unread_count;
            badge.style.display = 'flex';
            item.classList.add('has-unread-messages');
        } else {
            if (badge) badge.style.display = 'none';
            item.classList.remove('has-unread-messages');
        }

        // Update preview
        const preview = item.querySelector('.conversation-preview');
        if (preview) {
            let previewText = '';

            if (conv.unread_count > 0) {
                const prefix = conv.last_sender_type === 'recruiter' ?
                    `${Utils.escapeHtml(conv.recruiter_name || 'Recruiter')}: ` :
                    'You: ';
                previewText = `<strong>${prefix}${Utils.escapeHtml(conv.last_message || 'New message')}</strong>`;
            } else {
                const prefix = conv.last_sender_type === 'recruiter' ?
                    `${Utils.escapeHtml(conv.recruiter_name || 'Recruiter')}: ` :
                    'You: ';
                previewText = `${prefix}${Utils.escapeHtml(conv.last_message || 'No messages yet')}`;
            }

            preview.innerHTML = previewText;
        }

        // Update time
        const time = item.querySelector('.conversation-time');
        if (time && conv.last_message_at) {
            time.textContent = Utils.getTimeAgo(conv.last_message_at);
        }
    }

    // Update notification badges
    updateNotificationBadges(totalUnread) {
        const globalBadge = document.getElementById('globalNotificationBadge');
        if (globalBadge) {
            if (totalUnread > 0) {
                globalBadge.textContent = totalUnread > 99 ? '99+' : totalUnread;
                globalBadge.style.display = 'inline-block';
            } else {
                globalBadge.style.display = 'none';
            }
        }

        const sectionBadge = document.querySelector('.sidebar-section .section-badge');
        if (sectionBadge) {
            if (totalUnread > 0) {
                sectionBadge.textContent = totalUnread;
                sectionBadge.style.display = 'inline-block';
            } else {
                sectionBadge.style.display = 'none';
            }
        }
    }

    // AJAX request wrapper
    async ajaxRequest(endpoint, data) {
        return new Promise((resolve) => {
            if (typeof ajax_post !== 'function') {
                resolve({
                    success: false,
                    message: 'ajax_post not available'
                });
                return;
            }

            // Add CSRF token
            if (window.csrfName && window.csrf) {
                data[window.csrfName] = window.csrf;
            }

            ajax_post(endpoint, data, resolve);
        });
    }
}

// ===== EVENT MANAGER =====
class EventManager {
    constructor(state, messageManager) {
        this.state = state;
        this.messageManager = messageManager;
    }

    // Setup all event listeners
    setup() {
        this.setupMessageForm();
        this.setupTabSwitching();
        this.setupChatTypeSwitching();
    }

    // Setup message form events
    setupMessageForm() {
        const form = document.getElementById('messageForm');
        const input = document.getElementById('messageInput');

        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleSendMessage();
            });
        }

        if (input) {
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.handleSendMessage();
                }
            });

            // Auto-focus
            setTimeout(() => input.focus(), 500);
        }
    }

    // Setup tab switching
    setupTabSwitching() {
        const tabs = document.querySelectorAll('.chat-type-tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const chatType = tab.dataset.chatType;
                const url = new URL(window.location.href);

                if (chatType === 'all') {
                    url.searchParams.delete('chat_type');
                } else {
                    url.searchParams.set('chat_type', chatType);
                }

                window.location.href = url.toString();
            });
        });
    }

    // Setup chat type switching
    setupChatTypeSwitching() {
        const buttons = document.querySelectorAll('.switch-chat-type');
        buttons.forEach(button => {
            button.addEventListener('click', () => {
                const action = button.dataset.action;
                const recruiterId = button.dataset.recruiterId;
                const conversationUuid = button.dataset.conversationUuid;

                if (action === 'switch_to_general' && conversationUuid) {
                    this.handleSwitchToGeneral(button, conversationUuid, recruiterId);
                } else if (action === 'start_candidate_chat' && recruiterId) {
                    window.location.href =
                        `<?php echo site_url("agency/candidates/start_candidate_chat/"); ?>${recruiterId}`;
                }
            });
        });
    }

    // Handle send message
    handleSendMessage() {
        const input = document.getElementById('messageInput');
        const message = input.value.trim();

        if (message) {
            this.messageManager.send(message);
        }
    }

    // Handle switch to general chat
    handleSwitchToGeneral(button, conversationUuid, recruiterId) {
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Switching...';
        button.disabled = true;

        this.messageManager.ajaxRequest('ajax_switch_chat_to_general', {
            conversation_uuid: conversationUuid,
            recruiter_id: recruiterId
        }).then(response => {
            button.innerHTML = originalText;
            button.disabled = false;

            if (response.success && response.general_conversation_uuid) {
                window.location.href =
                    `<?php echo site_url("agency/chat/conversation/"); ?>${response.general_conversation_uuid}`;
            } else {
                Utils.showNotification(response.message || 'Switch failed', 'error');
            }
        });
    }
}

// ===== POLLING MANAGER =====
class PollingManager {
    constructor(state, messageManager, conversationManager) {
        this.state = state;
        this.messageManager = messageManager;
        this.conversationManager = conversationManager;
    }

    // Start all polling
    start() {
        this.startMessagePolling();
        this.startConversationPolling();
    }

    // Start message polling
    startMessagePolling() {
        if (this.state.pollInterval) {
            clearInterval(this.state.pollInterval);
        }

        this.state.pollInterval = setInterval(() => {
            this.messageManager.fetchNew();
        }, CONFIG.POLL_INTERVAL);

        // Initial poll
        setTimeout(() => this.messageManager.fetchNew(), 1000);
    }

    // Start conversation polling
    startConversationPolling() {
        if (this.state.conversationInterval) {
            clearInterval(this.state.conversationInterval);
        }

        this.state.conversationInterval = setInterval(() => {
            this.conversationManager.fetchUpdated();
        }, CONFIG.CONVERSATION_POLL);

        // Initial fetch
        setTimeout(() => this.conversationManager.fetchUpdated(), 1500);
    }

    // Stop all polling
    stop() {
        if (this.state.pollInterval) {
            clearInterval(this.state.pollInterval);
            this.state.pollInterval = null;
        }

        if (this.state.conversationInterval) {
            clearInterval(this.state.conversationInterval);
            this.state.conversationInterval = null;
        }
    }
}

// ===== MAIN CHAT SYSTEM =====
class ChatSystem {
    constructor() {
        this.state = new ChatState();
        this.messageManager = new MessageManager(this.state);
        this.conversationManager = new ConversationManager(this.state);
        this.eventManager = new EventManager(this.state, this.messageManager);
        this.pollingManager = new PollingManager(this.state, this.messageManager, this.conversationManager);

        this.initialize();
    }

    // Initialize the chat system
    initialize() {
        console.log('🚀 Initializing Chat System...');

        // Setup events
        this.eventManager.setup();

        // Start polling
        this.pollingManager.start();

        // Initial scroll
        Utils.scrollToBottom();

        console.log('✅ Chat System Ready');
    }

    // Public API
    sendMessage(message) {
        return this.messageManager.send(message);
    }

    fetchMessages() {
        return this.messageManager.fetchNew();
    }

    stop() {
        this.pollingManager.stop();
    }
}

// ===== INITIALIZATION =====
document.addEventListener('DOMContentLoaded', () => {
    // Create global chat instance
    window.chatSystem = new ChatSystem();

    // Test CSRF
    testCsrf();
});

// ===== CSRF TEST FUNCTION =====
function testCsrf() {
    console.log('Testing CSRF configuration...');

    if (typeof ajax_post === 'function') {
        ajax_post('ajax_get_conversations', {
            test: true
        }, (response) => {
            if (response.success) {
                console.log('✅ CSRF test passed');
            } else {
                console.error('❌ CSRF test failed:', response.message);
            }
        });
    } else {
        console.warn('⚠️ ajax_post not available for CSRF test');
    }
}

// ===== PUBLIC API =====
window.ChatUtils = {
    sendTestMessage: function() {
        const input = document.getElementById('messageInput');
        if (input && window.chatSystem) {
            input.value = 'Test at ' + new Date().toLocaleTimeString();
            window.chatSystem.sendMessage(input.value);
        }
    },

    refreshMessages: function() {
        if (window.chatSystem) {
            window.chatSystem.fetchMessages();
        }
    },

    getState: function() {
        return window.chatSystem ? window.chatSystem.state : null;
    }
};
</script>