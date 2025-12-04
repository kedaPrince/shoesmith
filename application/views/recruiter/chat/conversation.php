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

/* ===== CANDIDATE CHAT WIDGET ===== */
.candidate-chat-widget {
    background: var(--darker-bg);
    backdrop-filter: blur(10px);
    border: 1px solid var(--dark-border);
    border-radius: 16px;
    padding: 16px;
    margin: 15px 0;
    position: relative;
    overflow: hidden;
}

.candidate-chat-widget::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--primary-gradient);
}

.candidate-widget-header {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--light-border);
}

.candidate-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #7289da, #424549);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    font-size: 1.2rem;
    font-weight: bold;
    color: white;
    position: relative;
}

.candidate-avatar::after {
    content: '';
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 10px;
    height: 10px;
    background: var(--accent-gradient);
    border: 2px solid var(--darker-bg);
    border-radius: 50%;
}

.candidate-info h5 {
    margin: 0;
    color: var(--text-primary);
    font-size: 1rem;
    font-weight: 600;
}

.candidate-info small {
    color: var(--text-muted);
    font-size: 0.8rem;
}

.candidate-details-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
    margin-bottom: 12px;
}

.candidate-detail-item {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--light-border);
    border-radius: 8px;
    padding: 8px;
}

.detail-label {
    color: var(--text-muted);
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 2px;
}

.detail-value {
    color: var(--text-primary);
    font-size: 0.85rem;
    font-weight: 500;
}

.candidate-chat-actions {
    display: flex;
    gap: 8px;
}

.btn-candidate-action {
    flex: 1;
    background: var(--primary-gradient);
    border: none;
    border-radius: 8px;
    color: white;
    padding: 8px 12px;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.btn-candidate-action:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-glow);
}

.btn-candidate-action.secondary {
    background: var(--dark-bg);
    color: var(--text-secondary);
}

.btn-candidate-action.secondary:hover {
    background: rgba(255, 255, 255, 0.05);
    color: var(--text-primary);
}

/* ===== CANDIDATE CONTEXT BADGE ===== */
.candidate-context-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    background: var(--accent-gradient);
    color: white;
    border-radius: 12px;
    padding: 4px 8px;
    font-size: 0.7rem;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
    z-index: 2;
}

/* ===== CANDIDATE DOCUMENTS ===== */
.candidate-documents {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid var(--light-border);
}

.documents-header {
    color: var(--text-muted);
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.document-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid var(--light-border);
    border-radius: 6px;
    padding: 6px 10px;
    margin-bottom: 6px;
    transition: all 0.2s ease;
}

.document-item:hover {
    background: rgba(255, 255, 255, 0.05);
    border-color: rgba(139, 92, 246, 0.3);
}

.document-info {
    flex: 1;
}

.document-name {
    color: var(--text-primary);
    font-size: 0.8rem;
    font-weight: 500;
    margin-bottom: 2px;
}

.document-meta {
    color: var(--text-muted);
    font-size: 0.7rem;
}

.document-actions {
    display: flex;
    gap: 5px;
}

.btn-document-action {
    background: transparent;
    border: 1px solid var(--light-border);
    border-radius: 4px;
    color: var(--text-muted);
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-document-action:hover {
    background: var(--dark-bg);
    color: var(--text-primary);
    border-color: var(--text-muted);
}

.btn-document-action.primary {
    background: var(--primary-gradient);
    border-color: rgba(139, 92, 246, 0.5);
    color: white;
}

.btn-document-action.primary:hover {
    background: rgba(139, 92, 246, 0.8);
}

/* ===== CANDIDATE ONBOARDING STATUS ===== */
.onboarding-status {
    margin-top: 12px;
    padding: 10px;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.05));
    border: 1px solid rgba(16, 185, 129, 0.2);
    border-radius: 8px;
}

.onboarding-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}

.onboarding-title {
    color: var(--text-primary);
    font-size: 0.85rem;
    font-weight: 600;
}

.onboarding-stage {
    background: var(--accent-gradient);
    color: white;
    border-radius: 6px;
    padding: 2px 6px;
    font-size: 0.7rem;
    font-weight: 600;
}

.onboarding-progress {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 4px;
    height: 6px;
    overflow: hidden;
    margin-bottom: 8px;
}

.onboarding-progress-bar {
    height: 100%;
    background: var(--accent-gradient);
    transition: width 0.3s ease;
}

.onboarding-info {
    color: var(--text-muted);
    font-size: 0.75rem;
    display: flex;
    justify-content: space-between;
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

                    <!-- In the current conversation section -->
                    <?php if (isset($conversation)): ?>
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
                            <h4 class="agency-name"><?php echo htmlspecialchars($conversation->agency_name); ?></h4>
                            <p class="conversation-context">
                                <!-- Add chat type indicator -->
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

                                <!-- Show candidate info if this is a candidate chat -->
                                <?php if (!empty($conversation->candidate_name)): ?>
                                <br>
                                <i class="fa fa-user" style="margin-right: 5px;"></i>
                                <span style="color: var(--text-accent);">
                                    <?= htmlspecialchars($conversation->candidate_name) ?>
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
                <a href="<?= site_url('recruiter/candidates/view/' . $candidate_details->id) ?>" target="_blank"
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

                <!-- Add this Chat Type Tabs section after the Search Box -->
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

                    <!-- Update the sidebar section header -->
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
                            <a href="<?php echo site_url('recruiter/chat/conversation/' . $conv->uuid); ?>"
                                class="list-group-item list-group-item-action d-flex align-items-center conversation-item <?php echo (isset($conversation) && $conversation->uuid == $conv->uuid) ? 'active' : ''; ?>"
                                style="border: none; border-radius: 8px; margin-bottom: 5px; padding: 10px 15px; transition: all 0.2s; <?= !empty($conv->candidate_id) ? 'border-left: 3px solid #8B5CF6 !important;' : 'border-left: 3px solid #10B981 !important;' ?>"
                                data-conversation-uuid="<?php echo $conv->uuid; ?>">

                                <div class="conversation-avatar mr-3 position-relative">
                                    <div class="avatar"
                                        style="width: 40px; height: 40px; border-radius: 50%; background-color: <?= !empty($conv->candidate_id) ? '#8B5CF6' : '#10B981' ?>; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #ffffff;">
                                        <?php echo substr(htmlspecialchars($conv->agency_name), 0, 1); ?>
                                    </div>
                                    <?php if (isset($conv->is_online) && $conv->is_online): ?>
                                    <div class="online-indicator"></div>
                                    <?php endif; ?>
                                </div>

                                <div class="flex-grow-1" style="min-width: 0;">
                                    <!-- Agency Name with Chat Type Badge -->
                                    <div class="d-flex align-items-center mb-1">
                                        <h6 class="mb-0" style="color: #ffffff; font-weight: 500; margin-right: 8px;">
                                            <?php echo htmlspecialchars($conv->agency_name); ?>
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
                                    <?php if (!empty($conv->candidate_name)): ?>
                                    <small class="d-block"
                                        style="color: #C4B5FD; font-size: 0.7rem; font-weight: 500; margin: 3px 0; display: flex; align-items: center; gap: 4px;">
                                        <i class="fa fa-user-circle" style="font-size: 0.6rem;"></i>
                                        <?= htmlspecialchars($conv->candidate_name) ?>
                                        <?php if (!empty($conv->candidate_ref)): ?>
                                        <span
                                            style="color: rgba(196, 181, 253, 0.7);">(<?= htmlspecialchars($conv->candidate_ref) ?>)</span>
                                        <?php endif; ?>
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
                                        data-conversation-uuid="<?= $conv->uuid; ?>">
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
                                        data-conversation-uuid="<?php echo $conv->uuid; ?>">
                                        <?php echo time_ago($conv->last_message_at ?: $conv->created_at); ?>
                                    </small>
                                    <?php if (isset($conv->unread_count) && $conv->unread_count > 0): ?>
                                    <span class="conversation-badge" data-conversation-uuid="<?php echo $conv->uuid; ?>"
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
                                <input type="hidden" id="conversationUuid"
                                    value="<?php echo isset($conversation) ? $conversation->uuid : ''; ?>">

                                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                                    value="<?php echo $this->security->get_csrf_hash(); ?>">
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
                        <!-- Candidate Information (If this is a candidate-specific chat) -->
                        <?php if (isset($candidate_details) && $candidate_details): ?>
                        <div class="sidebar-section">
                            <div class="sidebar-header"
                                style="display: flex; align-items: center; justify-content: space-between;">
                                <span>Candidate</span>
                                <span class="candidate-context-badge">Active Chat</span>
                            </div>

                            <div class="candidate-chat-widget">
                                <!-- Candidate Header -->
                                <div class="candidate-widget-header">
                                    <div class="candidate-avatar">
                                        <?php echo substr(htmlspecialchars($candidate_details->first_name), 0, 1) . substr(htmlspecialchars($candidate_details->last_name), 0, 1); ?>
                                    </div>
                                    <div class="candidate-info">
                                        <h5><?php echo htmlspecialchars($candidate_details->first_name . ' ' . $candidate_details->last_name); ?>
                                        </h5>
                                        <small>
                                            <i class="fa fa-id-card"></i>
                                            <?php echo htmlspecialchars($candidate_details->reference_number); ?>
                                        </small>
                                    </div>
                                </div>

                                <!-- Candidate Details Grid -->
                                <div class="candidate-details-grid">
                                    <div class="candidate-detail-item">
                                        <div class="detail-label">Status</div>
                                        <div class="detail-value">
                                            <?php 
                    $status = $candidate_details->status ?? 'new';
                    $status_labels = [
                        'new' => 'New',
                        'reviewed' => 'Reviewed',
                        'shortlisted' => 'Shortlisted',
                        'interviewed' => 'Interviewed',
                        'rejected' => 'Rejected',
                        'hired' => 'Hired',
                        'on_hold' => 'On Hold'
                    ];
                    echo isset($status_labels[$status]) ? $status_labels[$status] : ucfirst($status);
                    ?>
                                        </div>
                                    </div>

                                    <div class="candidate-detail-item">
                                        <div class="detail-label">Email</div>
                                        <div class="detail-value">
                                            <?php echo htmlspecialchars($candidate_details->email ?? 'N/A'); ?>
                                        </div>
                                    </div>

                                    <div class="candidate-detail-item">
                                        <div class="detail-label">Phone</div>
                                        <div class="detail-value">
                                            <?php echo htmlspecialchars($candidate_details->phone ?? 'N/A'); ?>
                                        </div>
                                    </div>

                                    <div class="candidate-detail-item">
                                        <div class="detail-label">Applied</div>
                                        <div class="detail-value">
                                            <?php echo date('M d, Y', strtotime($candidate_details->application_date ?? 'now')); ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Candidate Actions -->
                                <div class="candidate-chat-actions">
                                    <a href="<?php echo site_url('recruiter/candidates/view/' . $candidate_details->id); ?>"
                                        class="btn-candidate-action" target="_blank">
                                        <i class="fa fa-eye"></i>
                                        View Profile
                                    </a>
                                    <a href="<?php echo site_url('recruiter/candidates/edit/' . $candidate_details->id); ?>"
                                        class="btn-candidate-action secondary" target="_blank">
                                        <i class="fa fa-edit"></i>
                                        Edit
                                    </a>
                                </div>

                                <!-- Onboarding Status (if available) -->
                                <?php if (isset($candidate_details->onboarding_stage) && $candidate_details->onboarding_stage !== 'not_started'): ?>
                                <div class="onboarding-status">
                                    <div class="onboarding-header">
                                        <span class="onboarding-title">Onboarding Progress</span>
                                        <span class="onboarding-stage">
                                            <?php 
                    $stage = $candidate_details->onboarding_stage ?? 'not_started';
                    $stage_labels = [
                        'not_started' => 'Not Started',
                        'stage_under_review' => 'Under Review',
                        'stage_submitted_to_hm' => 'Submitted to HM',
                        'stage_hm_decision' => 'HM Decision',
                        'stage_documents_decision' => 'Docs Decision',
                        'stage_requested_docs' => 'Docs Requested',
                        'stage_position_offered' => 'Position Offered',
                        'completed' => 'Completed'
                    ];
                    echo isset($stage_labels[$stage]) ? $stage_labels[$stage] : ucfirst(str_replace('_', ' ', $stage));
                    ?>
                                        </span>
                                    </div>
                                    <div class="onboarding-progress">
                                        <div class="onboarding-progress-bar"
                                            style="width: <?php echo min(100, max(0, $candidate_details->onboarding_progress ?? 0)); ?>%">
                                        </div>
                                    </div>
                                    <div class="onboarding-info">
                                        <span><?php echo min(100, max(0, $candidate_details->onboarding_progress ?? 0)); ?>%
                                            Complete</span>
                                        <span>
                                            <?php if ($candidate_details->onboarding_completed_at): ?>
                                            Completed:
                                            <?php echo date('M d', strtotime($candidate_details->onboarding_completed_at)); ?>
                                            <?php else: ?>
                                            In Progress
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Recent Documents (if any) -->
                                <?php 
        // You'll need to pass candidate documents to the view
        if (isset($candidate_documents) && !empty($candidate_documents)): 
            $recent_docs = array_slice($candidate_documents, 0, 3); // Show only 3 most recent
        ?>
                                <div class="candidate-documents">
                                    <div class="documents-header">
                                        <i class="fa fa-file-alt"></i>
                                        Recent Documents
                                    </div>
                                    <?php foreach ($recent_docs as $doc): ?>
                                    <div class="document-item">
                                        <div class="document-info">
                                            <div class="document-name">
                                                <?php echo htmlspecialchars($doc->document_name); ?>
                                            </div>
                                            <div class="document-meta">
                                                <?php echo date('M d', strtotime($doc->created_at)); ?>
                                                • <?php echo $this->format_file_size($doc->file_size); ?>
                                            </div>
                                        </div>
                                        <div class="document-actions">
                                            <a href="<?php echo base_url($doc->file_path); ?>"
                                                class="btn-document-action primary" target="_blank" title="Download">
                                                <i class="fa fa-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php if (count($candidate_documents) > 3): ?>
                                    <a href="<?php echo site_url('recruiter/candidates/view/' . $candidate_details->id . '#documents'); ?>"
                                        class="btn-candidate-action secondary" style="width: 100%; margin-top: 8px;"
                                        target="_blank">
                                        <i class="fa fa-folder-open"></i>
                                        View All Documents (<?php echo count($candidate_documents); ?>)
                                    </a>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>

                                <!-- Switch to General Chat Button -->
                                <div class="candidate-chat-actions" style="margin-top: 12px;">
                                    <button class="btn-candidate-action secondary" id="switchToGeneralChat"
                                        style="width: 100%;">
                                        <i class="fa fa-exchange-alt"></i>
                                        Switch to General Chat
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Job Information -->
                        <?php if ($conversation->job_name): ?>
                        <div class="sidebar-section">
                            <div class="sidebar-header">Job Information</div>
                            <div class="info-card">
                                <div class="info-card-title">Position</div>
                                <div class="info-card-value"><?php echo htmlspecialchars($conversation->job_name); ?>
                                </div>
                                <?php if ($candidate_details->job_ref): ?>
                                <div class="info-card-description">
                                    Reference: <?php echo htmlspecialchars($candidate_details->job_ref); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php elseif ($conversation->job_name): ?>
                        <!-- If not candidate-specific, show regular job context -->
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
// ============================================
// RECRUITER CHAT SYSTEM - FIXED FOR REAL-TIME UPDATES
// ============================================

// ===== GLOBAL STATE =====
let chatState = {
    isSending: false,
    isPolling: false,
    lastMessageId: <?php echo !empty($messages) ? end($messages)->id : 0; ?>,
    currentConversationUuid: null,
    displayedMessageIds: new Set(),
    pollInterval: null,
    conversationInterval: null,
    activePolling: true,
    csrfToken: '<?php echo $this->security->get_csrf_hash(); ?>',
    csrfTokenName: '<?php echo $this->security->get_csrf_token_name(); ?>'
};

// Initialize displayed message IDs from existing messages
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

// ===== UTILITY FUNCTIONS =====
function scrollToBottom() {
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        // Wait for DOM update
        setTimeout(() => {
            // Scroll to the very bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;

            // Force a re-check after a short delay
            setTimeout(() => {
                if (chatMessages.scrollTop + chatMessages.clientHeight < chatMessages.scrollHeight -
                    50) {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            }, 100);
        }, 50);
    }
}

// ===== MESSAGE DISPLAY FUNCTIONS =====
function createMessageElement(message, isRecruiter = false) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `d-flex ${isRecruiter ? 'justify-content-end' : 'justify-content-start'} mb-2`;
    messageDiv.dataset.messageId = message.id;

    const senderName = isRecruiter ? 'You' : (message.sender_name || 'Agency');
    const bgColor = isRecruiter ? '#dcf8c6' : '#ffffff';
    const borderRadius = isRecruiter ? '7.5px 7.5px 0 7.5px' : '7.5px 7.5px 7.5px 0';

    // Format time
    const messageTime = new Date(message.created_at);
    const timeString = messageTime.toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit'
    });

    messageDiv.innerHTML = `
        <div class="message-container" style="max-width: 70%;">
            <div class="message-content d-flex align-items-baseline ${isRecruiter ? 'justify-content-end' : 'justify-content-start'}">
                <div class="message-text-time d-inline-flex align-items-baseline" 
                     style="background-color: ${bgColor}; 
                            padding: 8px 12px; 
                            border-radius: ${borderRadius};
                            box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);">
                    <span class="message-text" style="font-size: 14.2px; color: #303030; line-height: 1.3; margin-right: 8px;">
                        ${escapeHtml(message.message)}
                    </span>
                    <span class="message-meta d-inline-flex align-items-center">
                        <small class="message-time" style="font-size: 11px; color: #667781; white-space: nowrap;">
                            ${timeString}
                        </small>
                        ${isRecruiter ? `
                            <span class="message-status" style="margin-left: 4px;">
                                <i class="fa fa-check${message.is_read ? '-double' : ''}" 
                                   style="font-size: 10px; color: ${message.is_read ? '#128C7E' : '#667781'};"></i>
                            </span>
                        ` : ''}
                    </span>
                </div>
            </div>
        </div>
    `;

    return messageDiv;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function addMessageToDisplay(message, isRecruiter = false) {
    const chatMessages = document.getElementById('chatMessages');
    if (!chatMessages) return;

    // Check if message already displayed
    if (chatState.displayedMessageIds.has(parseInt(message.id))) {
        return;
    }

    // Create message wrapper
    const messageWrapper = document.createElement('div');
    messageWrapper.className = `d-flex ${isRecruiter ? 'justify-content-end' : 'justify-content-start'} mb-2`;
    messageWrapper.style.flexShrink = '0'; // Prevent shrinking
    messageWrapper.dataset.messageId = message.id;

    // Create message element
    const messageElement = createMessageElement(message, isRecruiter);
    messageWrapper.appendChild(messageElement);

    // Add to chat messages
    chatMessages.appendChild(messageWrapper);
    chatState.displayedMessageIds.add(parseInt(message.id));

    // Update last message ID
    if (parseInt(message.id) > chatState.lastMessageId) {
        chatState.lastMessageId = parseInt(message.id);
    }

    // Always scroll to bottom for new messages
    scrollToBottom();
}


// ===== AJAX REQUEST HANDLER WITH PROPER ERROR HANDLING =====
// ===== FIXED AJAX REQUEST HANDLER =====
async function makeAjaxRequest(url, data = {}) {

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const csrfName = document.querySelector('meta[name="csrf-token-name"]')?.content;

    // Add CSRF token to FormData
    const formData = new FormData();
    if (csrfName && csrfToken) {
        formData.append(csrfName, csrfToken);
    }

    // Add CSRF token
    formData.append(chatState.csrfTokenName, chatState.csrfToken);

    // Add other data
    Object.keys(data).forEach(key => {
        formData.append(key, data[key]);
    });

    try {
        // Build the full URL - FIXED VERSION
        let fullUrl = url;

        // If URL doesn't start with http, it's a relative URL
        if (!url.startsWith('http')) {
            // Get the current page's base URL
            const currentPath = window.location.pathname;

            // If we're on a conversation page like /recruiter/chat/conversation/UUID
            // we need to go back to the base chat URL
            if (currentPath.includes('/conversation/')) {
                // Extract base URL: remove everything after /conversation/
                const basePath = currentPath.substring(0, currentPath.indexOf('/conversation/'));
                fullUrl = basePath + '/' + url.replace(/^\//, '');
            } else {
                // Otherwise use the current directory
                const basePath = window.location.pathname.replace(/\/[^\/]*$/, '');
                fullUrl = basePath + '/' + url.replace(/^\//, '');
            }

            // Make it a full URL
            fullUrl = window.location.origin + fullUrl;
        }

        console.log(`Making AJAX request to: ${fullUrl}`);

        const response = await fetch(fullUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();

            // Handle 403 Forbidden (likely CSRF error)
            if (response.status === 403) {
                console.error('403 Forbidden - CSRF or session issue');

                // Try to get new CSRF token and retry once
                const csrfResponse = await fetch(window.location.origin +
                    '/shoesmith/recruiter/chat/ajax_get_conversations', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                if (csrfResponse.ok) {
                    const csrfResult = await csrfResponse.json();
                    if (csrfResult.csrf) {
                        // Update CSRF and retry
                        chatState.csrfToken = csrfResult.csrf;
                        console.log('CSRF token updated, retrying...');
                        return await makeAjaxRequest(url, data); // Retry once
                    }
                }

                return {
                    success: false,
                    message: 'Access forbidden (403)'
                };
            }

            return {
                success: false,
                message: 'Server returned non-JSON response'
            };
        }

        // Parse JSON response
        const result = await response.json();

        // Update CSRF token if provided
        if (result.csrf) {
            chatState.csrfToken = result.csrf;

            // Update hidden input if exists
            const csrfInput = document.querySelector(`input[name="${chatState.csrfTokenName}"]`);
            if (csrfInput) {
                csrfInput.value = result.csrf;
            }
        }

        return result;
    } catch (error) {
        console.error('AJAX request failed:', error);
        return {
            success: false,
            message: error.message
        };
    }
}

// ===== POLLING FUNCTIONS =====
async function fetchNewMessages() {
    if (chatState.isPolling || !chatState.currentConversationUuid || !chatState.activePolling) {
        return;
    }

    chatState.isPolling = true;

    try {
        // First check session
        const sessionValid = await checkSession();
        if (!sessionValid) {
            console.log('Session invalid, stopping polling');
            stopPolling();
            return;
        }

        const response = await makeAjaxRequest('ajax_get_messages', {
            conversation_uuid: chatState.currentConversationUuid,
            last_message_id: chatState.lastMessageId
        });

        console.log("Poll response:", response);

        if (response.success && response.messages) {
            // Process new messages
            response.messages.forEach(message => {
                const isRecruiter = message.sender_type === 'recruiter';
                addMessageToDisplay(message, isRecruiter);
            });

            // Update last message ID if we got new messages
            if (response.messages.length > 0) {
                const lastMsg = response.messages[response.messages.length - 1];
                chatState.lastMessageId = parseInt(lastMsg.id);

                // Mark messages as read
                if (!response.messages[0].is_read && response.messages[0].sender_type !== 'recruiter') {
                    markMessagesAsRead();
                }
            }

            // If using HTML fallback
            if (response.html) {
                const chatMessages = document.getElementById('chatMessages');
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = response.html;

                // Add new messages
                const newMessages = tempDiv.children;
                for (let msg of newMessages) {
                    chatMessages.appendChild(msg);
                }

                scrollToBottom();
            }
        } else {
            console.error("Poll error:", response.message);

            // If session expired, redirect
            if (response.message && response.message.includes('Session expired')) {
                stopPolling();
                window.location.href = '<?php echo site_url("recruiter/login"); ?>';
            }
        }

    } catch (error) {
        console.error("Poll fetch error:", error);
    } finally {
        chatState.isPolling = false;
    }
}

async function markMessageAsRead(messageId) {
    try {
        // Only mark as read if we have a valid message ID
        if (!messageId || typeof messageId !== 'number') {
            console.log('Invalid message ID for mark read:', messageId);
            return;
        }

        console.log('Marking message as read:', messageId);

        const response = await makeAjaxRequest('ajax_mark_message_read', {
            message_id: messageId
        });

        if (response.success) {
            console.log('Message marked as read successfully');
        } else {
            console.log('Failed to mark message as read:', response.message);
        }
    } catch (error) {
        console.error("Mark read error:", error);
    }
}
async function checkSession() {
    try {
        const response = await makeAjaxRequest('ajax_check_session', {});

        if (response.success === false && response.message.includes('not logged in')) {
            console.warn('Session expired, redirecting to login...');
            window.location.href = '<?php echo site_url("recruiter/login"); ?>';
            return false;
        }

        return true;
    } catch (error) {
        console.error('Session check failed:', error);
        return false;
    }
}
async function fetchUpdatedConversations() {
    if (!chatState.activePolling) return;

    try {
        const response = await makeAjaxRequest(
            '<?php echo site_url("recruiter/chat/ajax_get_conversations"); ?>', {});

        if (response.success && response.conversations) {
            updateSidebarConversationBadges(response.conversations);
            if (response.total_unread_count !== undefined) {
                updateAllNotificationBadges(response.total_unread_count);
            }
        }
    } catch (error) {
        console.error("Conversations fetch error:", error);
    }
}

// ===== MESSAGE SENDING =====
async function sendMessage() {
    const messageInput = document.getElementById('messageInput');
    const messageText = messageInput.value.trim();

    if (!messageText || chatState.isSending || !chatState.currentConversationUuid) {
        return;
    }

    chatState.isSending = true;

    // Show optimistic message
    const tempId = Date.now();
    const tempMessage = {
        id: 'temp_' + tempId,
        message: messageText,
        created_at: new Date().toISOString(),
        sender_type: 'recruiter',
        is_read: false,
        sender_name: 'You'
    };

    addMessageToDisplay(tempMessage, true);
    messageInput.value = '';
    messageInput.focus();

    try {
        const response = await makeAjaxRequest('<?php echo site_url("recruiter/chat/ajax_send_message"); ?>', {
            conversation_uuid: chatState.currentConversationUuid,
            message: messageText
        });

        console.log("Send response:", response);

        if (response.success) {
            // Remove temp message
            const tempMsg = document.querySelector(`[data-message-id="temp_${tempId}"]`);
            if (tempMsg) tempMsg.remove();

            // Add real message if provided
            if (response.message_id) {
                // Create message object from response
                const messageObj = {
                    id: response.message_id,
                    message: messageText,
                    created_at: new Date().toISOString(),
                    sender_type: 'recruiter',
                    is_read: false,
                    sender_name: 'You'
                };
                addMessageToDisplay(messageObj, true);

                // Update last message ID
                if (response.message_id > chatState.lastMessageId) {
                    chatState.lastMessageId = parseInt(response.message_id);
                }
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

        alert("Network error. Please try again.");
    } finally {
        chatState.isSending = false;
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
}

// ===== POLLING MANAGEMENT =====
function startPolling() {
    console.log("Starting polling...");

    // Clear any existing intervals
    if (chatState.pollInterval) clearInterval(chatState.pollInterval);
    if (chatState.conversationInterval) clearInterval(chatState.conversationInterval);

    // Message polling every 2 seconds (faster for real-time)
    chatState.pollInterval = setInterval(fetchNewMessages, 2000);

    // Conversation polling every 10 seconds
    chatState.conversationInterval = setInterval(fetchUpdatedConversations, 10000);

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

// ===== INITIALIZATION =====
function initializeChatSystem() {
    console.log('=== RECRUITER CHAT SYSTEM INITIALIZING ===');

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

        // Force one more scroll after images/avatars load
        setTimeout(scrollToBottom, 500);
    }, 300);

    // Start polling
    startPolling();

    console.log('=== RECRUITER CHAT SYSTEM INITIALIZED ===');
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

    // Wait a bit for everything to load
    setTimeout(initializeChatSystem, 500);
});

// ===== DEBUG FUNCTIONS =====
window.debugChat = function() {
    console.log("=== CHAT DEBUG INFO ===");
    console.log("State:", {
        currentConversationUuid: chatState.currentConversationUuid,
        lastMessageId: chatState.lastMessageId,
        displayedMessageIds: Array.from(chatState.displayedMessageIds),
        isSending: chatState.isSending,
        isPolling: chatState.isPolling,
        activePolling: chatState.activePolling,
        csrfToken: chatState.csrfToken ? 'Available' : 'Missing'
    });

    // Force a poll
    fetchNewMessages();
};

// Add debug button for testing
setTimeout(() => {
    const debugBtn = document.createElement('button');
    debugBtn.innerHTML = '🔧 Debug Chat';
    debugBtn.style.position = 'fixed';
    debugBtn.style.bottom = '10px';
    debugBtn.style.right = '10px';
    debugBtn.style.zIndex = '9999';
    debugBtn.style.padding = '8px 12px';
    debugBtn.style.background = '#007bff';
    debugBtn.style.color = 'white';
    debugBtn.style.border = 'none';
    debugBtn.style.borderRadius = '5px';
    debugBtn.style.cursor = 'pointer';
    debugBtn.style.fontSize = '12px';
    debugBtn.onclick = window.debugChat;
    document.body.appendChild(debugBtn);
}, 2000);
</script>