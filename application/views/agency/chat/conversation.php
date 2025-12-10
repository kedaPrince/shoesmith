<?php 
defined('BASEPATH') || exit('No direct script access allowed');

// ===== CSRF FIX: Store token once to prevent multiple different tokens =====
$csrf_token = $this->security->get_csrf_hash();
$csrf_name = $this->security->get_csrf_token_name();
?>
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

/* ===== WHATSAPP MESSAGE STYLES - COMPACT FIX ===== */
#chatMessages {
    display: block !important;
    flex-direction: column !important;
    height: calc(74vh - 120px) !important;
    overflow-y: auto !important;
    flex-shrink: 0 !important;
    padding: 8px 12px !important;
    background-color: #e5ddd5 !important;
    background-image: url('data:image/svg+xml,<svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><path d="M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z" fill="%2391a29e" fill-opacity="0.1" fill-rule="evenodd"/></svg>') !important;
}

.message-wrapper {
    display: flex !important;
    margin-bottom: 8px !important;
    clear: both !important;
}

.message-sent {
    justify-content: flex-end !important;
}

.message-received {
    justify-content: flex-start !important;
}

.message-content {
    max-width: 65% !important;
    padding: 6px 9px 4px 9px !important;
    border-radius: 7.5px !important;
    box-shadow: 0 1px 0.5px rgba(0, 0, 0, 0.13) !important;
    position: relative !important;
    word-wrap: break-word !important;
    word-break: break-word !important;
    display: inline-block !important;
}

.message-sent .message-content {
    background-color: #dcf8c6 !important;
    border-radius: 7.5px 0 7.5px 7.5px !important;
}

.message-received .message-content {
    background-color: #ffffff !important;
    border-radius: 0 7.5px 7.5px 7.5px !important;
}

.message-text-wrapper {
    display: inline !important;
    line-height: 1.28 !important;
}

.message-text {
    font-size: 14.2px !important;
    color: #111b21 !important;
    line-height: 19px !important;
    font-family: 'Segoe UI', 'Helvetica Neue', sans-serif !important;
    word-wrap: break-word !important;
    white-space: inherit;
    text-align: left !important;
    display: inline !important;
    margin-right: 8px !important;
}

.message-meta {
    display: inline-flex !important;
    align-items: center !important;
    vertical-align: bottom !important;
    height: 15px !important;
    margin-left: 4px !important;
}

.message-time {
    font-size: 11px !important;
    color: #667781 !important;
    white-space: nowrap !important;
    display: inline-block !important;
    line-height: 15px !important;
}

.message-status {
    display: inline-flex !important;
    align-items: center !important;
    margin-left: 4px !important;
    height: 15px !important;
}

.message-status i {
    font-size: 10px !important;
    line-height: 15px !important;
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

    .message-content {
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

/* ===== ENHANCED BADGE STYLES ===== */
.conversation-meta-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin: 4px 0;
}

.conversation-meta-badge {
    font-size: 0.65rem;
    padding: 2px 6px;
    border-radius: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    white-space: nowrap;
}

.conversation-meta-badge.chat-type {
    background: rgba(139, 92, 246, 0.2);
    color: #C4B5FD;
    border: 1px solid rgba(139, 92, 246, 0.3);
}

.conversation-meta-badge.chat-type.candidate {
    background: rgba(139, 92, 246, 0.2);
    color: #C4B5FD;
    border: 1px solid rgba(139, 92, 246, 0.3);
}

.conversation-meta-badge.chat-type.general {
    background: rgba(16, 185, 129, 0.2);
    color: #A7F3D0;
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.conversation-meta-badge.job {
    background: rgba(245, 158, 11, 0.2);
    color: #FDE68A;
    border: 1px solid rgba(245, 158, 11, 0.3);
}

.conversation-meta-badge.agency {
    background: rgba(59, 130, 246, 0.2);
    color: #93C5FD;
    border: 1px solid rgba(59, 130, 246, 0.3);
}

/* Enhanced conversation item with badges */
.conversation-item-with-badges {
    position: relative;
}

.conversation-badge-stack {
    display: flex;
    flex-direction: column;
    gap: 3px;
    margin-left: 8px;
}

/* Badge for job name in conversation list */
.job-name-badge {
    max-width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
<div id="main-content">
    <div id="csrf-container" style="display: none;">
        <input type="hidden" name="<?php echo $csrf_name; ?>" id="csrf_rfid_token" value="<?php echo $csrf_token; ?>">
        <meta name="csrf-token" content="<?php echo $csrf_token; ?>">
    </div>
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
                            <!-- Inside the conversation list in agency view -->
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

                                        <!-- ADDED: Chat Type Badge in conversation list -->
                                        <span
                                            class="conversation-meta-badge chat-type <?= !empty($conv->candidate_id) ? 'candidate' : 'general' ?>">
                                            <i class="fa fa-<?= !empty($conv->candidate_id) ? 'user' : 'comments' ?> mr-1"
                                                style="font-size: 0.6rem;"></i>
                                            <?= !empty($conv->candidate_id) ? 'Candidate' : 'General' ?>
                                        </span>
                                    </div>

                                    <!-- ADDED: Job Badge (if exists) -->
                                    <?php if (!empty($conv->job_name)): ?>
                                    <div class="conversation-meta-badges">
                                        <span class="conversation-meta-badge job job-name-badge"
                                            title="<?= htmlspecialchars($conv->job_name) ?>">
                                            <i class="fa fa-briefcase mr-1" style="font-size: 0.6rem;"></i>
                                            <?= strlen($conv->job_name) > 20 ? substr(htmlspecialchars($conv->job_name), 0, 20) . '...' : htmlspecialchars($conv->job_name) ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>

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
                style="background-color: #e5ddd5; display: flex; flex-direction: column; height: 100%;">

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

                        <!-- Messages Area - WhatsApp Style - COMPACT FIXED -->
                        <div id="chatMessages">
                            <?php if (!empty($messages)): ?>
                            <div id="messagesContainer">
                                <?php foreach ($messages as $message): ?>
                                <div
                                    class="message-wrapper <?php echo $message->sender_type == 'agency' ? 'message-sent' : 'message-received'; ?>">
                                    <div class="message-content">
                                        <span class="message-text-wrapper">
                                            <span class="message-text">
                                                <?php 
                                                if (strpos($message->message, '<a ') !== false && strpos($message->message, 'target="_blank"') !== false) {
                                                    echo $message->message;
                                                } else {
                                                    echo nl2br(htmlspecialchars($message->message));
                                                }
                                                ?>
                                            </span>
                                            <span class="message-meta">
                                                <span class="message-time">
                                                    <?php echo date('h:i A', strtotime($message->created_at)); ?>
                                                </span>
                                                <?php if ($message->sender_type == 'agency'): ?>
                                                <span class="message-status">
                                                    <i
                                                        class="fa fa-check<?php echo $message->is_read ? '-double' : ''; ?>"></i>
                                                </span>
                                                <?php endif; ?>
                                            </span>
                                        </span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="no-messages"
                                style="display: flex; align-items: center; justify-content: center; height: 100%;">
                                <div class="text-center" style="color: #667781;">
                                    <i class="fa fa-comments fa-3x mb-3" style="color: #128C7E;"></i>
                                    <p style="font-size: 1rem; margin: 0;">No messages yet. Start the conversation!</p>
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

                                <!-- ADD THIS: CSRF TOKEN -->
                                <input type="hidden" name="<?php echo $csrf_name; ?>"
                                    value="<?php echo $csrf_token; ?>">

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
// ===== CSRF TOKEN MANAGEMENT =====
let currentCsrfToken = '<?php echo $csrf_token; ?>';
const csrfTokenName = '<?php echo $csrf_name; ?>';

// ===== GLOBAL STATE =====
let chatState = {
    isSending: false,
    isPolling: false,
    lastMessageId: <?php echo !empty($messages) ? end($messages)->id : 0; ?>,
    currentConversationUuid: null,
    displayedMessageIds: new Set(),
    pollInterval: null,
    conversationInterval: null,
    activePolling: true
};

// ===== CSRF HELPER FUNCTIONS =====

// Get fresh CSRF token from page
function getFreshCsrfToken() {
    // Check hidden input
    const csrfInput = document.querySelector('input[name="csrf_rfid_token"]');
    if (csrfInput && csrfInput.value && csrfInput.value.length > 10) {
        return csrfInput.value;
    }

    // Check meta tag
    const metaToken = document.querySelector('meta[name="csrf-token"]');
    if (metaToken && metaToken.content && metaToken.content.length > 10) {
        return metaToken.content;
    }

    // Check JavaScript variable
    if (typeof currentCsrfToken !== 'undefined' && currentCsrfToken && currentCsrfToken.length > 10) {
        return currentCsrfToken;
    }

    console.error('No valid CSRF token found on page');
    return null;
}

// Update CSRF token on page
function updateCsrfTokenOnPage(newToken) {
    if (!newToken || newToken.length < 10) {
        console.error('Invalid CSRF token received for update');
        return false;
    }

    console.log(`Updated CSRF token to: ${newToken.substring(0, 10)}...`);

    // Update hidden input
    const csrfInput = document.querySelector('input[name="csrf_rfid_token"]');
    if (csrfInput) {
        csrfInput.value = newToken;
    }

    // Update meta tag
    const metaToken = document.querySelector('meta[name="csrf-token"]');
    if (metaToken) {
        metaToken.content = newToken;
    }

    // Update JavaScript variables
    window.latestCsrfToken = newToken;
    if (typeof currentCsrfToken !== 'undefined') {
        currentCsrfToken = newToken;
    }

    return true;
}

// ===== AJAX HELPER FUNCTION (EXACTLY LIKE RECRUITER) =====
async function makeAjaxRequest(endpoint, data = {}) {
    console.log(`Making AJAX request to: ${endpoint}`);

    // Use the correct URL pattern - CHANGED TO AGENCY
    const baseUrl = window.location.origin + '/shoesmith/agency/chat/';
    let fullUrl = baseUrl + endpoint;

    console.log(`Full URL: ${fullUrl}`);

    // Get CSRF token (still include it for consistency)
    const csrfToken = document.querySelector('input[name="csrf_rfid_token"]')?.value;

    // Build query string for GET request (EXACTLY LIKE RECRUITER)
    const params = new URLSearchParams();

    // Add CSRF token if available
    if (csrfToken) {
        params.append('csrf_rfid_token', csrfToken);
    }

    // Add other data
    Object.keys(data).forEach(key => {
        if (data[key] !== null && data[key] !== undefined) {
            params.append(key, data[key]);
        }
    });

    // Append query string to URL
    const queryString = params.toString();
    if (queryString) {
        fullUrl += '?' + queryString;
    }

    try {
        // Use GET method (EXACTLY LIKE RECRUITER)
        const response = await fetch(fullUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        // Update CSRF token if provided (even though we use GET)
        if (result.csrf_token) {
            document.querySelectorAll('input[name="csrf_rfid_token"]').forEach(input => {
                input.value = result.csrf_token;
            });
        }

        return result;

    } catch (error) {
        console.error('Request failed:', error);
        return {
            success: false,
            message: 'Network error: ' + error.message
        };
    }
}

// ===== UTILITY FUNCTIONS =====
function scrollToBottom() {
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        setTimeout(() => {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }, 50);
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) {
        return map[m];
    });
}

function renderMessageContent(content) {
    // Check if content has HTML tags for document links
    if (content.includes('<a ') && content.includes('target="_blank"')) {
        // Create a temporary div to parse the HTML
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = content;

        // Get all links and make sure they're safe
        const links = tempDiv.querySelectorAll('a');
        links.forEach(link => {
            // Ensure links are safe (only allow target="_blank" and basic attributes)
            const href = link.getAttribute('href');
            if (href && href.startsWith('http')) {
                // Make sure it opens in new tab
                link.setAttribute('target', '_blank');
                link.setAttribute('rel', 'noopener noreferrer');
            }
        });

        return tempDiv.innerHTML;
    }

    // For regular text, escape HTML and preserve line breaks
    const escaped = escapeHtml(content);
    return escaped.replace(/\n/g, '<br>');
}

// ===== MESSAGE DISPLAY FUNCTIONS =====
function createMessageElement(message, isAgency = false) {
    const messageWrapper = document.createElement('div');
    messageWrapper.className = `message-wrapper ${isAgency ? 'message-sent' : 'message-received'}`;
    messageWrapper.dataset.messageId = message.id;

    const messageTime = new Date(message.created_at);
    const timeString = messageTime.toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit'
    });

    const renderedContent = renderMessageContent(message.message || '');

    messageWrapper.innerHTML = `
        <div class="message-content">
            <span class="message-text-wrapper">
                <span class="message-text">
                    ${renderedContent}
                </span>
                <span class="message-meta">
                    <span class="message-time">
                        ${timeString}
                    </span>
                    ${isAgency ? `
                        <span class="message-status">
                            <i class="fa fa-check${message.is_read ? '-double' : ''}"></i>
                        </span>
                    ` : ''}
                </span>
            </span>
        </div>
    `;

    return messageWrapper;
}

function addMessageToDisplay(message, isAgency = false) {
    const chatMessages = document.getElementById('chatMessages');
    if (!chatMessages) return;

    if (chatState.displayedMessageIds.has(parseInt(message.id))) {
        return;
    }

    // Check if there's a messages container, if not create one
    let messagesContainer = document.getElementById('messagesContainer');
    if (!messagesContainer) {
        // Remove the "no messages" content
        const noMessages = chatMessages.querySelector('.no-messages');
        if (noMessages) noMessages.remove();

        // Create container
        messagesContainer = document.createElement('div');
        messagesContainer.id = 'messagesContainer';
        chatMessages.appendChild(messagesContainer);
    }

    const messageElement = createMessageElement(message, isAgency);
    messagesContainer.appendChild(messageElement);
    chatState.displayedMessageIds.add(parseInt(message.id));

    if (parseInt(message.id) > chatState.lastMessageId) {
        chatState.lastMessageId = parseInt(message.id);
    }

    scrollToBottom();
}

// ===== MESSAGE SENDING =====
async function sendMessage() {
    const messageInput = document.getElementById('messageInput');
    const messageText = messageInput.value.trim();
    const conversationUuid = document.getElementById('conversationUuid').value;

    if (!messageText || chatState.isSending || !conversationUuid) {
        return;
    }

    chatState.isSending = true;

    // Show optimistic message
    const tempId = Date.now();
    const tempMessage = {
        id: 'temp_' + tempId,
        message: messageText,
        created_at: new Date().toISOString(),
        sender_type: 'agency',
        is_read: false,
        sender_name: 'You'
    };

    addMessageToDisplay(tempMessage, true);
    messageInput.value = '';
    messageInput.focus();

    try {
        // Send via GET (EXACTLY LIKE RECRUITER)
        const response = await makeAjaxRequest('ajax_send_message', {
            conversation_uuid: conversationUuid,
            message: messageText
        });

        console.log("Send response:", response);

        if (response.success) {
            // Remove temp message
            const tempMsg = document.querySelector(`[data-message-id="temp_${tempId}"]`);
            if (tempMsg) tempMsg.remove();

            // Add real message if provided
            if (response.message_id) {
                const messageObj = {
                    id: response.message_id,
                    message: messageText,
                    created_at: new Date().toISOString(),
                    sender_type: 'agency',
                    is_read: false,
                    sender_name: 'You'
                };
                addMessageToDisplay(messageObj, true);

                if (response.message_id > chatState.lastMessageId) {
                    chatState.lastMessageId = parseInt(response.message_id);
                }
            }

            // Update CSRF token if provided
            if (response.csrf_token) {
                updateCsrfTokenOnPage(response.csrf_token);
            }

            // Refresh conversations
            setTimeout(fetchUpdatedConversations, 500);

            console.log("Message sent successfully");
        } else {
            // Handle error - remove optimistic message
            const tempMsg = document.querySelector(`[data-message-id="temp_${tempId}"]`);
            if (tempMsg) tempMsg.remove();

            // Restore message to input
            messageInput.value = messageText;

            console.error("Send error:", response.message);
            alert(response.message || "Failed to send message");
        }

    } catch (error) {
        console.error("Send fetch error:", error);

        // Remove optimistic message
        const tempMsg = document.querySelector(`[data-message-id="temp_${tempId}"]`);
        if (tempMsg) tempMsg.remove();

        // Restore message to input
        messageInput.value = messageText;

        alert("Network error. Please check your connection and try again.");
    } finally {
        chatState.isSending = false;
    }
}

// ===== POLLING FUNCTIONS =====
async function fetchNewMessages() {
    if (chatState.isPolling || !chatState.currentConversationUuid || !chatState.activePolling) {
        return;
    }

    chatState.isPolling = true;

    try {
        const response = await makeAjaxRequest('ajax_get_messages', {
            conversation_uuid: chatState.currentConversationUuid,
            last_message_id: chatState.lastMessageId
        });

        console.log("Poll response:", response);

        if (response.success && response.messages) {
            // Process new messages
            response.messages.forEach(message => {
                const isAgency = message.sender_type === 'agency';
                addMessageToDisplay(message, isAgency);
            });

            // Update last message ID if we got new messages
            if (response.messages.length > 0) {
                const lastMsg = response.messages[response.messages.length - 1];
                chatState.lastMessageId = parseInt(lastMsg.id);
            }
        } else if (response.status === 404) {
            console.log('Messages endpoint returned 404, stopping polling');
            // Stop polling if endpoint doesn't exist
            stopPolling();
        }

    } catch (error) {
        console.error("Poll fetch error:", error);
    } finally {
        chatState.isPolling = false;
    }
}

async function fetchUpdatedConversations() {
    if (!chatState.activePolling) return;

    try {
        const response = await makeAjaxRequest('ajax_get_conversations', {});

        if (response.success && response.conversations) {
            updateSidebarConversationBadges(response.conversations);
            if (response.total_unread_count !== undefined) {
                updateAllNotificationBadges(response.total_unread_count);
            }
        } else if (response.status === 404) {
            console.log('Conversations endpoint returned 404, stopping polling');
            stopPolling();
        }
    } catch (error) {
        console.error("Conversations fetch error:", error);
    }
}

// ===== SIDEBAR FUNCTIONS =====
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

function updateSidebarConversationBadges(conversationsData) {
    conversationsData.forEach(conv => {
        const conversationItem = document.querySelector(
            `.conversation-item[data-conversation-uuid="${conv.uuid}"]`);
        if (conversationItem) {
            updateSingleConversationBadge(conversationItem, conv);
        }
    });
}

function updateSingleConversationBadge(conversationItem, convData) {
    let badge = conversationItem.querySelector('.conversation-badge');

    if (convData.unread_count > 0) {
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'conversation-badge';
            badge.setAttribute('data-conversation-uuid', convData.uuid);

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
            if (convData.last_sender_type === 'recruiter') {
                previewText =
                    `<strong>${escapeHtml(convData.recruiter_name || 'Recruiter')}: ${escapeHtml(convData.last_message || 'New message')}</strong>`;
            } else {
                previewText = `<strong>You: ${escapeHtml(convData.last_message || 'New message')}</strong>`;
            }
        } else {
            if (convData.last_sender_type === 'recruiter') {
                previewText =
                    `${escapeHtml(convData.recruiter_name || 'Recruiter')}: ${escapeHtml(convData.last_message || 'No messages yet')}`;
            } else {
                previewText = `You: ${escapeHtml(convData.last_message || 'No messages yet')}`;
            }
        }

        previewElement.innerHTML = previewText;
    }
}

// ===== HELPER FUNCTIONS =====
function initializeDisplayedMessages() {
    const existingMessages = document.querySelectorAll('#chatMessages [data-message-id]');
    existingMessages.forEach(msg => {
        const msgId = msg.dataset.messageId;
        if (msgId && !msgId.startsWith('temp_')) {
            chatState.displayedMessageIds.add(parseInt(msgId));
        }
    });
    console.log(`Initialized ${chatState.displayedMessageIds.size} displayed messages`);
}

// ===== POLLING MANAGEMENT =====
function startPolling() {
    console.log("Starting polling...");

    // Clear any existing intervals
    if (chatState.pollInterval) clearInterval(chatState.pollInterval);
    if (chatState.conversationInterval) clearInterval(chatState.conversationInterval);

    // Message polling every 3 seconds
    chatState.pollInterval = setInterval(fetchNewMessages, 3000);

    // Conversation polling every 15 seconds
    chatState.conversationInterval = setInterval(fetchUpdatedConversations, 15000);

    // Initial fetches
    setTimeout(fetchNewMessages, 500);
    setTimeout(fetchUpdatedConversations, 1000);
}

function stopPolling() {
    console.log("Stopping polling...");
    chatState.activePolling = false;

    if (chatState.pollInterval) {
        clearInterval(chatState.pollInterval);
        chatState.pollInterval = null;
    }

    if (chatState.conversationInterval) {
        clearInterval(chatState.conversationInterval);
        chatState.conversationInterval = null;
    }
}

// ===== EVENT LISTENERS =====
function setupEventListeners() {
    const messageForm = document.getElementById('messageForm');
    const messageInput = document.getElementById('messageInput');

    // Form submission
    if (messageForm) {
        // Remove existing listeners
        const newForm = messageForm.cloneNode(true);
        messageForm.parentNode.replaceChild(newForm, messageForm);

        // Add new listener
        document.getElementById('messageForm').addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            sendMessage();
            return false;
        });
    }

    // Message input - Enter key
    if (messageInput) {
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
                return false;
            }
        });

        // Focus input
        setTimeout(() => {
            messageInput.focus();
        }, 1000);
    }

    // Chat type filter buttons
    const chatTypeTabs = document.querySelectorAll('.chat-type-tab');
    chatTypeTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const chatType = this.getAttribute('data-chat-type');

            // Update active tab
            chatTypeTabs.forEach(t => {
                t.classList.remove('active');
                t.style.background = '#2f3136';
            });

            // Set active tab styles
            this.classList.add('active');
            if (chatType === 'all') {
                this.style.background = 'linear-gradient(135deg, #7289da, #5b6eae)';
            } else if (chatType === 'candidate') {
                this.style.background = 'linear-gradient(135deg, #8B5CF6, #7C3AED)';
            } else if (chatType === 'general') {
                this.style.background = 'linear-gradient(135deg, #10B981, #059669)';
            }

            // Update URL with filter parameter
            const currentUrl = new URL(window.location.href);
            if (chatType === 'all') {
                currentUrl.searchParams.delete('chat_type');
            } else {
                currentUrl.searchParams.set('chat_type', chatType);
            }

            window.location.href = currentUrl.toString();
        });
    });

    // Search functionality
    const searchInput = document.querySelector('.form-control[placeholder="Find or start a conversation"]');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const conversationItems = document.querySelectorAll('.conversation-item');

            conversationItems.forEach(item => {
                const recruiterName = item.querySelector('h6').textContent.toLowerCase();
                const previewText = item.querySelector('.conversation-preview').textContent
                    .toLowerCase();

                if (recruiterName.includes(searchTerm) || previewText.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
}

// ===== INITIALIZATION =====
function initializeChatSystem() {
    console.log('=== AGENCY CHAT SYSTEM INITIALIZING ===');

    // Verify CSRF token
    const csrfToken = getFreshCsrfToken();
    if (!csrfToken) {
        console.error('Cannot initialize chat system: No CSRF token found!');
        alert('Security token missing. Please refresh the page.');
        return;
    }

    console.log('CSRF token verified, length:', csrfToken.length);

    // Get current conversation UUID
    const uuidField = document.getElementById('conversationUuid');
    if (uuidField && uuidField.value) {
        chatState.currentConversationUuid = uuidField.value;
        console.log("Current conversation UUID:", chatState.currentConversationUuid);
    } else {
        console.error("No conversation UUID found!");
        return;
    }

    // Initialize displayed messages
    initializeDisplayedMessages();

    // Setup event listeners
    setupEventListeners();

    // Wait for layout to settle, then scroll to bottom
    setTimeout(() => {
        scrollToBottom();
        setTimeout(scrollToBottom, 500);
    }, 300);

    // Start polling
    startPolling();

    console.log('=== AGENCY CHAT SYSTEM INITIALIZED ===');
}

// ===== PAGE VISIBILITY =====
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        stopPolling();
    } else {
        chatState.activePolling = true;
        startPolling();
    }
});

// ===== MAIN INITIALIZATION =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Initializing Chat System');

    // Test CSRF token
    setTimeout(() => {
        const token = getFreshCsrfToken();
        console.log('CSRF Token Status:', token ? 'Present' : 'Missing');
        if (token) {
            console.log('Token length:', token.length);
        }
    }, 500);

    // Initialize chat system
    setTimeout(initializeChatSystem, 1000);
});

// ===== TEST FUNCTION =====
async function testAgencyAjax() {
    console.log('Testing Agency AJAX...');

    try {
        const response = await makeAjaxRequest('ajax_check_session', {});
        console.log('Agency test response:', response);

        if (response.success) {
            console.log('✅ Agency AJAX working!');
        } else {
            console.log('❌ Agency AJAX failed:', response.message);
        }
    } catch (error) {
        console.error('❌ Agency AJAX error:', error);
    }
}

// Run test after a delay
setTimeout(testAgencyAjax, 1500);
</script>