<?php 
defined('BASEPATH') || exit('No direct script access allowed');

// ===== CSRF FIX: Store token once to prevent multiple different tokens =====
$csrf_token = $this->security->get_csrf_hash();
$csrf_name = $this->security->get_csrf_token_name();

// Check online status for each recruiter (5 minute threshold)
// Check online status for each recruiter (5 minute threshold)
$online_recruiters_count = 0;
if (!empty($available_recruiters)) {
    foreach ($available_recruiters as $recruiter) {
        // Check if recruiter has been active in the last 5 minutes
        $recruiter->is_online = false;
        
        // Debug: Check what data we have
        // echo "<!-- DEBUG Recruiter {$recruiter->id}: last_activity_at = " . ($recruiter->last_activity_at ?? 'NULL') . ", last_login = " . ($recruiter->last_login ?? 'NULL') . " -->\n";
        
        // First check last_activity_at field
        if (!empty($recruiter->last_activity_at) && $recruiter->last_activity_at != '0000-00-00 00:00:00') {
            $last_activity = strtotime($recruiter->last_activity_at);
            if ($last_activity !== false && (time() - $last_activity) < 300) { // 5 minutes
                $recruiter->is_online = true;
                // echo "<!-- Online via last_activity_at: " . date('Y-m-d H:i:s', $last_activity) . " -->\n";
            }
        }
        
        // If no last_activity_at, check last_login
        if (!$recruiter->is_online && !empty($recruiter->last_login) && $recruiter->last_login != '0000-00-00 00:00:00') {
            $last_login = strtotime($recruiter->last_login);
            if ($last_login !== false && (time() - $last_login) < 300) { // 5 minutes
                $recruiter->is_online = true;
                // echo "<!-- Online via last_login: " . date('Y-m-d H:i:s', $last_login) . " -->\n";
            }
        }
        
        // If still not online, check if they have a current session
        if (!$recruiter->is_online) {
            // You might need to check session table if you have one
            // This is a fallback method
        }
        
        // Count online recruiters
        if ($recruiter->is_online) {
            $online_recruiters_count++;
            // echo "<!-- Recruiter {$recruiter->id} is ONLINE -->\n";
        } else {
            // echo "<!-- Recruiter {$recruiter->id} is OFFLINE -->\n";
        }
    }
}
// echo "<!-- Total online: {$online_recruiters_count} -->\n";
?>
<style>
/* ===== MODERN CHAT UI STYLING ===== */
:root {
    --bg: #0f172a;
    --bg-alt: #020617;
    --sidebar-bg: #020617;
    --accent: #22c55e;
    --accent-soft: rgba(34, 197, 94, 0.1);
    --text-main: #e5e7eb;
    --text-muted: #9ca3af;
    --bubble-me: #22c55e;
    --bubble-them: #111827;
    --border-subtle: #1f2937;
    --input-bg: #020617;
    --danger: #ef4444;
    --warning: #f59e0b;
    --info: #3b82f6;
}

/* ===== COSMIC CHAT HEADER ===== */
.cosmic-chat-header {
    position: relative;
    border-radius: 20px;
    margin: 20px 0;
    padding: 0;
    overflow: hidden;
    background: linear-gradient(93deg, #59c4bc -60%, rgba(23, 162, 184, 0) 55%) !important;
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
    border-radius: 14px;
    color: var(--text-main);
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
    border-color: var(--accent);
    transform: translateY(-2px);
    box-shadow: 0 8px 32px rgba(34, 197, 94, 0.15);
    color: var(--text-main);
}

.nav-item.active {
    background: var(--accent-soft);
    border-color: var(--accent);
    color: var(--accent);
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
    background: var(--accent-soft);
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
    background: var(--accent);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: white;
    font-size: 1.1rem;
    box-shadow:
        0 0 20px rgba(34, 197, 94, 0.4),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
    animation: avatarGlow 3s ease-in-out infinite;
}

@keyframes avatarGlow {

    0%,
    100% {
        box-shadow:
            0 0 20px rgba(34, 197, 94, 0.4),
            inset 0 1px 0 rgba(255, 255, 255, 0.2);
    }

    50% {
        box-shadow:
            0 0 30px rgba(34, 197, 94, 0.6),
            0 0 40px rgba(34, 197, 94, 0.3),
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
    background: var(--accent);
    border: 2px solid var(--bg-alt);
    z-index: 2;
}

.status-indicator.offline {
    background: var(--text-muted);
}

.conversation-info h4 {
    margin: 0;
    color: var(--text-main);
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

/* ===== MODERN MESSAGE BUBBLES ===== */
#chatMessages {
    display: flex !important;
    flex-direction: column !important;
    height: calc(74vh - 120px) !important;
    overflow-y: auto !important;
    flex-shrink: 0 !important;
    padding: 16px 20px !important;
    gap: 0.5rem;
    scroll-behavior: smooth;
}

.messages-container {
    flex: 1;
    padding: 1rem 1.1rem;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    scroll-behavior: smooth;
}

.day-divider {
    text-align: center;
    font-size: 0.7rem;
    color: var(--text-muted);
    margin: 0.75rem 0;
    position: relative;
}

.day-divider::before,
.day-divider::after {
    content: "";
    position: absolute;
    top: 50%;
    width: 30%;
    height: 1px;
    background: var(--border-subtle);
}

.day-divider::before {
    left: 0;
}

.day-divider::after {
    right: 0;
}

/* Message row alignment - FIXED */
.message-row {
    display: flex;
    width: 100%;
    margin-bottom: 8px;
    clear: both;
}

.message-row.me {
    justify-content: flex-end;
}

.message-row.them {
    justify-content: flex-start;
}

/* Message bubbles - CLEAN DESIGN */
.message-bubble {
    max-width: 40%;
    padding: 0.5rem 0.7rem 0.3rem;
    border-radius: 1rem;
    font-size: 0.8rem;
    position: relative;
    word-wrap: break-word;
    display: block;
    align-items: unset;
    justify-content: unset;
    height: auto;
    min-height: 0;
    padding-top: 6px;
    padding-bottom: 6px;
    line-height: 1.35;
    text-align: left;
    box-sizing: border-box;
}

.message-bubble>* {
    vertical-align: top !important;
}

.message-row.me .message-bubble {
    background: var(--bubble-me);
    color: white;
    border-bottom-right-radius: 0.25rem;
}

.message-row.them .message-bubble {
    background: var(--bubble-them);
    border-bottom-left-radius: 0.25rem;
    color: var(--text-main);
    border: 1px solid var(--border-subtle);
}

.message-meta {
    font-size: 0.65rem;
    color: rgba(255, 255, 255, 0.7);
    margin-top: 0.2rem;
    text-align: right;
    opacity: 0.85;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 4px;
}

.message-row.them .message-meta {
    color: var(--text-muted);
}

.message-time {
    font-size: 0.65rem;
}

.message-status {
    display: flex;
    align-items: center;
}

/* ===== MODERN SIDEBAR ===== */
.chat-list {
    flex: 1;
    overflow-y: auto;
    padding: 0.25rem 0.25rem 0.75rem;
}

.chat-item {
    display: flex;
    gap: 0.6rem;
    padding: 0.6rem 0.6rem;
    margin: 0.1rem 0.4rem;
    border-radius: 12px;
    cursor: pointer;
    transition: background 0.3s ease;
    align-items: center;
    text-decoration: none;
    color: var(--text-main);
}

.chat-item:hover {
    background: rgba(15, 23, 42, 0.6);
}

.chat-item.active {
    background: var(--accent-soft);
    border-left: 3px solid var(--accent);
}

.sidebar-avatar {
    width: 32px;
    height: 32px;
    border-radius: 999px;
    background: linear-gradient(135deg, var(--accent), #16a34a);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 600;
    color: white;
    flex-shrink: 0;
}

.chat-item-text {
    flex: 1;
    min-width: 0;
}

.chat-name {
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--text-main);
}

.chat-last-msg {
    font-size: 0.75rem;
    color: var(--text-muted);
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
}

.chat-meta {
    font-size: 0.7rem;
    color: var(--text-muted);
    text-align: right;
}

.chat-unread {
    display: inline-block;
    min-width: 16px;
    padding: 0 0.25rem;
    font-size: 0.65rem;
    background: var(--accent);
    color: white;
    border-radius: 999px;
    text-align: center;
    margin-top: 0.2rem;
}

/* ===== MODERN CHAT INPUT ===== */
.chat-input-area {
    padding: 0.6rem 0.8rem;
    border-top: 1px solid var(--border-subtle);
    background: var(--bg-alt);
    display: flex;
    align-items: center;
    gap: 0.55rem;
}

.chat-input-container {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 0.45rem;
    background: var(--input-bg);
    border-radius: 999px;
    border: 1px solid var(--border-subtle);
    padding: 0.4rem 0.7rem;
}

.chat-input {
    border: none;
    outline: none;
    background: transparent;
    flex: 1;
    color: var(--text-main);
    font-size: 0.85rem;
    padding: 0.2rem 0;
}

.chat-input::placeholder {
    color: var(--text-muted);
}

.send-btn {
    border-radius: 999px;
    padding: 0.4rem 0.9rem;
    border: none;
    background: var(--accent);
    color: white;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

.send-btn:disabled {
    opacity: 0.5;
    cursor: default;
}

/* ===== SIDEBAR FILTER TABS ===== */
.filter-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
    padding: 0 1rem;
}

.filter-tab {
    flex: 1;
    padding: 0.5rem 0.75rem;
    background: transparent;
    border: 1px solid var(--border-subtle);
    border-radius: 8px;
    color: var(--text-muted);
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
}

.filter-tab:hover {
    background: var(--accent-soft);
    color: var(--accent);
}

.filter-tab.active {
    background: var(--accent);
    color: var(--sidebar-bg);
    border-color: var(--accent);
}

/* ===== RESPONSIVE DESIGN ===== */
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
        max-width: 85%;
    }

    #chatMessages {
        height: calc(74vh - 110px) !important;
        padding: 12px 16px !important;
    }
}

/* ===== BADGES ===== */
.cosmic-badge {
    background: var(--accent);
    color: var(--bg-alt);
    padding: 3px 6px;
    border-radius: 8px;
    font-size: 0.65rem;
    font-weight: 700;
    min-width: 18px;
    text-align: center;
    border: none;
    box-shadow: none;
}

.conversation-badge.cosmic {
    background: var(--accent);
    color: var(--bg-alt);
    border-radius: 10px;
    padding: 5px 8px;
    font-size: 0.75rem;
    font-weight: 700;
    border: none;
    box-shadow: none;
}

/* ===== OVERRIDE EXISTING STYLES ===== */
#main-content {
    height: calc(100vh - 120px);
    overflow: hidden;
}

.container-fluid.p-0 {
    height: calc(96vh - 200px) !important;
    overflow: hidden;
    background: linear-gradient(0deg, #59c4bc -60%, #00000000 55%) !important;
}

/* Fix the container layout */
.row.no-gutters {
    height: 100%;
}

/* Sidebar background */
.col-md-4.col-lg-3 {
    border-right: 1px solid var(--border-subtle) !important;
}

/* Main chat area */
.col-md-8.col-lg-9 {
    display: flex;
    flex-direction: column;
}

/* Chat header */
.d-flex.align-items-center.justify-content-between.p-3.border-bottom {
    background: linear-gradient(93deg, #59c4bc -60%, rgba(23, 162, 184, 0) 55%) !important;
    border-bottom: 1px solid var(--border-subtle) !important;
    padding: 1rem 1.1rem !important;
}

/* Search box */
.p-3.border-bottom {
    background: linear-gradient(93deg, #59c4bc -60%, rgba(23, 162, 184, 0) 55%) !important;
    border-bottom: 1px solid var(--border-subtle) !important;
    display: none;
}

.input-group {
    background: var(--input-bg) !important;
    border-radius: 999px !important;
    padding: 0.3rem !important;
    border: 1px solid var(--border-subtle) !important;
}

.input-group-text {
    background: transparent !important;
    border: none !important;
    color: var(--text-muted) !important;
}

.form-control {
    background: transparent !important;
    border: none !important;
    color: var(--text-main) !important;
}

.form-control::placeholder {
    color: var(--text-muted) !important;
}

/* Sidebar sections */
.sidebar-section h6 {
    color: var(--text-muted) !important;
    font-size: 0.8rem !important;
}

/* Conversation items in sidebar */
.conversation-item {
    background: transparent !important;
    border: none !important;
    border-radius: 12px !important;
    margin-bottom: 4px !important;
    padding: 0.6rem 0.6rem !important;
    transition: background 0.3s ease !important;
}

.conversation-item:hover {
    background: rgba(15, 23, 42, 0.6) !important;
}

.conversation-item.active {
    background: var(--accent-soft) !important;
    border-left: 3px solid var(--accent) !important;
}

.conversation-item .avatar {
    width: 32px !important;
    height: 32px !important;
    background: linear-gradient(135deg, var(--accent), #16a34a) !important;
    font-size: 0.8rem !important;
}

.conversation-item h6 {
    color: var(--text-main) !important;
    font-size: 0.85rem !important;
}

.conversation-preview {
    color: var(--text-muted) !important;
    font-size: 0.75rem !important;
}

.conversation-time {
    color: var(--text-muted) !important;
    font-size: 0.7rem !important;
}

.conversation-badge {
    background: var(--accent) !important;
    color: var(--bg-alt) !important;
    border: none !important;
    box-shadow: none !important;
}

/* Remove WhatsApp background pattern */
#chatMessages {
    background-image: url('http://localhost/shoesmith/resources/cms/images/wheel-of-fortune-smwdyono.png') !important;
    background-repeat: repeat !important;
    background-size: 400px !important;
    background-color: rgb(15 36 42 / 65%) !important;
    background-blend-mode: overlay !important;
}

/* Message wrapper cleanup */
.message-wrapper {
    display: flex !important;
    width: 100% !important;
    margin-bottom: 0.5rem !important;
}

/* Clean up old message styles */
.message-content {
    background: transparent !important;
    border-radius: 1rem !important;
    padding: 0.5rem 0.7rem 0.3rem !important;
    max-width: 100% !important;
    box-shadow: none !important;
}

.message-text-wrapper {
    display: block !important;
}

.message-meta {
    display: flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 4px !important;
    margin-top: 0.2rem !important;
    height: auto !important;
    margin-left: 0 !important;
}

.message-time {
    font-size: 0.65rem !important;
    color: rgba(255, 255, 255, 0.7) !important;
    white-space: nowrap !important;
    display: inline !important;
    line-height: normal !important;
}

.message-status {
    display: flex !important;
    align-items: center !important;
    margin-left: 0 !important;
    height: auto !important;
}

.message-status i {
    font-size: 0.65rem !important;
    line-height: normal !important;
}

/* Input area */
.border-top.p-2 {
    background: var(--bg-alt) !important;
    border-top: 1px solid var(--border-subtle) !important;
    padding: 0.6rem 0.8rem !important;
}

.input-group.h-100 {
    background: var(--input-bg) !important;
    border-radius: 999px !important;
    border: 1px solid var(--border-subtle) !important;
    padding: 0.3rem !important;
}

.btn-link {
    color: var(--text-muted) !important;
    background: transparent !important;
}

.btn-link:hover {
    color: var(--accent) !important;
}

#messageInput {
    color: var(--text-main) !important;
    background: transparent !important;
}

#messageInput::placeholder {
    color: var(--text-muted) !important;
}

/* Scrollbar styling */
#chatMessages::-webkit-scrollbar {
    width: 6px;
}

#chatMessages::-webkit-scrollbar-track {
    background: transparent;
}

#chatMessages::-webkit-scrollbar-thumb {
    background: var(--border-subtle);
    border-radius: 3px;
}

#chatMessages::-webkit-scrollbar-thumb:hover {
    background: #374151;
}

/* Empty state */
.no-messages .text-center {
    color: var(--text-muted) !important;
}

.no-messages .fa-comments {
    color: var(--accent) !important;
}

/* ===== INSTAGRAM-STYLE RECRUITER PROFILES SECTION ===== */
.recruiter-profiles-section {
    border-bottom: 1px solid var(--border-subtle);
    padding: 12px 16px;
    height: 160px;
}

.profiles-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.profiles-header h6 {
    margin: 0;
    color: var(--text-main);
    font-size: 0.85rem;
    font-weight: 600;
}

.profiles-header .view-all {
    color: var(--accent);
    font-size: 0.7rem;
    cursor: pointer;
    transition: opacity 0.2s;
}

.profiles-header .view-all:hover {
    opacity: 0.8;
}

.profiles-container {
    display: flex;
    gap: 25px;
    overflow-x: auto;
    padding: 5px 10px;
    scrollbar-width: thin;
    scrollbar-color: var(--border-subtle) transparent;
    height: 120px;
}

.profiles-container::-webkit-scrollbar {
    height: 4px;
}

.profiles-container::-webkit-scrollbar-track {
    background: transparent;
}

.profiles-container::-webkit-scrollbar-thumb {
    background: var(--border-subtle);
    border-radius: 2px;
}

.profiles-container::-webkit-scrollbar-thumb:hover {
    background: #374151;
}

.profile-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    min-width: 70px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    flex-shrink: 0;
}

.profile-item:hover {
    transform: translateY(-5px);
}

.profile-item.active {
    transform: scale(1.05);
}

/* Active profile gradient border - IMPORTANT: This is where the active state is defined */
.profile-item.active .profile-avatar-wrapper {
    position: relative;
    border-radius: 50%;
    padding: 3px;
    background: linear-gradient(93deg, #59c4bc -60%, rgba(23, 162, 184, 0) 55%) !important;
    display: inline-block;
}

.profile-item.active .profile-avatar {
    border-color: transparent !important;
    box-shadow: 0 0 20px rgba(34, 197, 94, 0.3);
}

.profile-avatar-wrapper {
    position: relative;
    margin-bottom: 8px;
}

.profile-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.profile-avatar.online {
    animation: avatarGlow 3s ease-in-out infinite;
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    transition: transform 0.3s ease, filter 0.3s ease;
}

.profile-item:hover .profile-avatar img {
    transform: scale(1.1);
    filter: brightness(1.1);
}

.profile-avatar::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg,
            rgba(34, 197, 94, 0.1),
            rgba(59, 130, 246, 0.1),
            rgba(139, 92, 246, 0.1));
    opacity: 0;
    transition: opacity 0.3s ease;
    border-radius: 50%;
    z-index: 1;
}

.profile-avatar:hover::before {
    opacity: 1;
}

.profile-avatar::after {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    border-radius: 50%;
    background: linear-gradient(45deg, var(--accent), #3b82f6, #8b5cf6);
    z-index: -1;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.profile-avatar:hover::after {
    opacity: 0.6;
}

@keyframes avatarGlow {

    0%,
    100% {
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2),
            0 0 20px rgba(34, 197, 94, 0.4);
    }

    50% {
        box-shadow: 0 4px 25px rgba(0, 0, 0, 0.3),
            0 0 30px rgba(34, 197, 94, 0.6),
            0 0 40px rgba(34, 197, 94, 0.3);
    }
}

@keyframes avatarRotate {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}

.profile-avatar.rotating img {
    animation: avatarRotate 20s linear infinite;
}

.profile-unread-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: var(--danger);
    color: white;
    font-size: 0.6rem;
    font-weight: 700;
    min-width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid var(--bg-alt);
    z-index: 2;
    animation: badgePulse 2s infinite;
}

@keyframes badgePulse {

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
    bottom: 8px;
    right: 8px;
    width: 14px;
    height: 14px;
    background: var(--accent);
    border-radius: 50%;
    border: 2px solid var(--bg-alt);
    z-index: 2;
    animation: indicatorPulse 2s infinite;
}

@keyframes indicatorPulse {

    0%,
    100% {
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
    }

    70% {
        box-shadow: 0 0 0 6px rgba(34, 197, 94, 0);
    }

    100% {
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
    }
}

.profile-name {
    font-size: 0.8rem;
    color: var(--text-main);
    text-align: center;
    max-width: 100%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-weight: 500;
    margin-top: 2px;
    padding: 4px 8px;
    background: rgba(15, 23, 42, 0.7);
    border-radius: 12px;
    transition: all 0.2s ease;
    backdrop-filter: blur(10px);
    border: 1px solid var(--border-subtle);
}

.profile-item:hover .profile-name {
    background: rgba(34, 197, 94, 0.2);
    color: var(--accent);
    font-weight: 600;
    border-color: var(--accent);
    transform: translateY(-2px);
}

.profile-item.active .profile-name {
    background: var(--accent);
    color: var(--bg-alt);
    font-weight: 700;
    border-color: var(--accent);
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
}

.no-profiles-msg {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 10px;
    color: var(--text-muted);
    font-size: 0.8rem;
}

/* Responsive design for profiles */
@media (max-width: 992px) {
    .recruiter-profiles-section {
        height: 150px;
        padding: 10px 12px;
    }

    .profile-avatar {
        width: 65px;
        height: 65px;
    }

    .profiles-container {
        height: 115px;
        gap: 22px;
    }

    .profile-name {
        font-size: 0.75rem;
    }
}

@media (max-width: 768px) {
    .recruiter-profiles-section {
        height: 140px;
        padding: 8px 10px;
    }

    .profile-avatar {
        width: 60px;
        height: 60px;
    }

    .profiles-container {
        height: 110px;
        gap: 20px;
    }

    .profile-name {
        font-size: 0.7rem;
    }

    .profiles-header h6 {
        font-size: 0.8rem;
    }

    .profiles-header .view-all {
        font-size: 0.65rem;
    }
}

/* Loading animation for avatars */
@keyframes avatarLoading {
    0% {
        background-position: 200% 0;
    }

    100% {
        background-position: -200% 0;
    }
}

.profile-avatar.loading {
    background: linear-gradient(90deg,
            var(--border-subtle) 25%,
            var(--bg-alt) 50%,
            var(--border-subtle) 75%);
    background-size: 200% 100%;
    animation: avatarLoading 1.5s infinite;
}

.profile-avatar.loading img {
    opacity: 0;
}

/* Offline profile styling */
.profile-avatar.offline {
    filter: grayscale(0.7);
    opacity: 0.7;
}

.profile-avatar.offline img {
    filter: grayscale(0.7);
}

.profile-avatar.offline .online-indicator {
    background: var(--text-muted);
    animation: none;
    display: none;
}

/* ===== LOADING OVERLAY FOR RECRUITER SWITCHING ===== */
.recruiter-switch-loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 23, 42, 0.95);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    transition: opacity 0.3s ease;
    opacity: 0;
    pointer-events: none;
}

.recruiter-switch-loader.active {
    opacity: 1;
    pointer-events: all;
}

.loader-content {
    text-align: center;
    color: var(--text-main);
    padding: 30px;
    border-radius: 16px;
    background: rgba(2, 6, 23, 0.8);
    backdrop-filter: blur(10px);
    border: 1px solid var(--border-subtle);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    animation: fadeInUp 0.4s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.switch-loader-icon {
    width: 60px;
    height: 60px;
    border: 3px solid var(--border-subtle);
    border-top-color: var(--accent);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 20px;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.switch-loader-text {
    font-size: 1rem;
    font-weight: 500;
    margin-bottom: 10px;
    color: var(--text-main);
}

.switch-loader-subtext {
    font-size: 0.85rem;
    color: var(--text-muted);
    max-width: 300px;
    line-height: 1.4;
}

.loading-pulse {
    animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {

    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: 0.5;
    }
}
</style>

<div id="main-content">
    <div id="csrf-container" style="display: none;">
        <input type="hidden" name="<?php echo $csrf_name; ?>" id="csrf_rfid_token" value="<?php echo $csrf_token; ?>">
        <meta name="csrf-token" content="<?php echo $csrf_token; ?>">
    </div>

    <!-- Cosmic Chat Header -->
    <div class="cosmic-chat-header">
        <div class="cosmic-container">
            <!-- Left: Navigation & Chat Info -->
            <div class="cosmic-navigation">
                <div class="nav-path">
                    <a href="<?php echo site_url('agency/dashboard'); ?>" class="nav-item">
                        <div class="nav-icon">
                            <i class="fa fa-chart-line"></i>
                        </div>
                        <span>Dashboard</span>
                    </a>

                    <div class="nav-separator">
                        <i class="fa fa-chevron-right"></i>
                    </div>

                    <div class="nav-item active">
                        <div class="nav-icon">
                            <i class="fa fa-comments"></i>
                            <?php if (isset($total_unread_count) && $total_unread_count > 0): ?>
                            <span class="cosmic-badge" id="globalNotificationBadge">
                                <?php echo $total_unread_count > 99 ? '99+' : $total_unread_count; ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <span>Agency Chat</span>
                    </div>

                    <?php if (isset($conversation)): ?>
                    <div class="nav-separator">
                        <i class="fa fa-chevron-right"></i>
                    </div>

                    <div class="current-conversation">
                        <div class="conversation-avatar">
                            <div class="avatar-glow">
                                <div class="avatar-pulse">
                                    <?php echo substr(htmlspecialchars($conversation->recruiter_name), 0, 1); ?>
                                </div>
                                <!-- FIXED: Check if current conversation's recruiter is online -->
                                <?php 
                                $current_recruiter_online = false;
                                if (!empty($available_recruiters)) {
                                    foreach ($available_recruiters as $recruiter) {
                                        if ($recruiter->id == $conversation->recruiter_id && !empty($recruiter->is_online)) {
                                            $current_recruiter_online = true;
                                            break;
                                        }
                                    }
                                }
                                ?>
                                <div
                                    class="status-indicator <?php echo $current_recruiter_online ? 'online' : 'offline'; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="conversation-info">
                            <h4 class="recruiter-name">
                                <?php echo htmlspecialchars($conversation->recruiter_name); ?></h4>
                            <p class="conversation-context">
                                <?php if (!empty($conversation->candidate_id)): ?>
                                <span style="color: var(--accent); margin-right: 8px;">
                                    <i class="fa fa-user"></i> Candidate Chat
                                </span>
                                <?php else: ?>
                                <span style="color: var(--accent); margin-right: 8px;">
                                    <i class="fa fa-comments"></i> General Chat
                                </span>
                                <?php endif; ?>

                                <?php if ($conversation->job_name): ?>
                                <i class="fa fa-briefcase"></i>
                                <?php echo htmlspecialchars($conversation->job_name); ?>
                                <?php endif; ?>

                                <?php if (!empty($conversation->candidate_id) && !empty($candidate_details)): ?>
                                <br>
                                <i class="fa fa-user" style="margin-right: 5px;"></i>
                                <span style="color: var(--accent);">
                                    <?= htmlspecialchars($candidate_details->first_name . ' ' . $candidate_details->last_name) ?>
                                </span>
                                <?php elseif (!empty($conversation->candidate_id)): ?>
                                <br>
                                <i class="fa fa-user" style="margin-right: 5px;"></i>
                                <span style="color: var(--accent);">
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
    </div>

    <!-- Main Chat Container - SIMPLIFIED HEIGHT -->
    <div class="container-fluid p-0" style="height: calc(100vh - 240px);">
        <div class="row no-gutters" style="height: 100%;">
            <!-- Left Sidebar: Conversations -->
            <div class="col-md-4 col-lg-3" style="height: 100%;">
                <div style="height: 100%; display: flex; flex-direction: column;">
                    <!-- Search Box -->
                    <div class="p-3 border-bottom">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fa fa-search"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control" placeholder="Find or start a conversation"
                                autocomplete="off">
                        </div>
                    </div>

                    <!-- Filter Tabs -->
                    <div class="filter-tabs">
                        <button
                            class="filter-tab <?= empty($_GET['chat_type']) || $_GET['chat_type'] == 'all' ? 'active' : '' ?>"
                            data-chat-type="all">All Chats</button>
                        <button
                            class="filter-tab <?= isset($_GET['chat_type']) && $_GET['chat_type'] == 'candidate' ? 'active' : '' ?>"
                            data-chat-type="candidate">Candidate</button>
                        <button
                            class="filter-tab <?= isset($_GET['chat_type']) && $_GET['chat_type'] == 'general' ? 'active' : '' ?>"
                            data-chat-type="general">General</button>
                    </div>

                    <!-- Conversations List -->
                    <div class="chat-list" style="flex: 1; overflow-y: auto;">
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

                        <?php if (empty($filtered_conversations)): ?>
                        <div class="text-center py-4">
                            <i class="fa fa-comments fa-2x mb-2" style="color: var(--text-muted);"></i>
                            <p style="color: var(--text-muted); font-size: 0.8rem;">
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
                        <?php foreach ($filtered_conversations as $conv): ?>
                        <a href="<?php echo site_url('/agency/chat/conversation/' .  $conv->uuid); ?>"
                            class="chat-item <?php echo (isset($conversation) && $conversation->uuid == $conv->uuid) ? 'active' : ''; ?>"
                            data-conversation-id="<?php echo $conv->uuid; ?>"
                            data-recruiter-id="<?php echo $conv->recruiter_id ?? 0; ?>"
                            data-recruiter-name="<?php echo htmlspecialchars($conv->recruiter_name); ?>"
                            onclick="return handleRecruiterSwitch(event, <?php echo $conv->recruiter_id ?? 0; ?>, '<?php echo htmlspecialchars($conv->recruiter_name); ?>')">

                            <div class="sidebar-avatar">
                                <?php echo substr(htmlspecialchars($conv->recruiter_name), 0, 1); ?>
                            </div>

                            <div class="chat-item-text">
                                <div class="chat-name"><?php echo htmlspecialchars($conv->recruiter_name); ?></div>
                                <div class="chat-last-msg">
                                    <?php echo htmlspecialchars($conv->last_message ?: 'No messages yet'); ?>
                                </div>
                            </div>

                            <div class="chat-meta">
                                <div><?php echo time_ago($conv->last_message_at ?: $conv->created_at); ?></div>
                                <?php if (isset($conv->unread_count) && $conv->unread_count > 0): ?>
                                <div class="chat-unread"><?php echo $conv->unread_count; ?></div>
                                <?php endif; ?>
                            </div>
                        </a>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Main Chat Area -->
            <div class="col-md-8 col-lg-9" style="height: 100%;">
                <?php if (isset($conversation)): ?>
                <!-- Instagram-style Recruiter Profiles Row (Separate section) -->
                <div class="recruiter-profiles-section">
                    <div class="profiles-header">
                        <h6>Recruiters</h6>
                        <small class="view-all" id="onlineCountDisplay">0 online</small>
                    </div>
                    <div class="profiles-container" id="recruiterProfilesContainer">
                        <?php if (!empty($available_recruiters)): ?>
                        <?php foreach ($available_recruiters as $recruiter): ?>
                        <?php 
                // Check if this recruiter is the current one (ACTIVE CHAT)
                $is_current = (isset($conversation->recruiter_id) && $conversation->recruiter_id == $recruiter->id);
                
                // Get conversation for this recruiter
                $recruiter_conversation = null;
                foreach ($all_conversations as $conv) {
                    if ($conv->recruiter_id == $recruiter->id) {
                        $recruiter_conversation = $conv;
                        break;
                    }
                }
                
                // Use conversation UUID if exists
                $conversation_uuid = $recruiter_conversation ? $recruiter_conversation->uuid : '';
                
                // If no conversation exists yet, create/get one
                if (empty($conversation_uuid)) {
                    $temp_conversation = $this->Model_chat_messages->get_or_create_conversation(
                        $agency_id, 
                        $recruiter->id
                    );
                    if ($temp_conversation) {
                        $conversation_uuid = $temp_conversation->uuid;
                    }
                }
                
                // Check if has unread messages
                $has_unread = isset($recruiter_conversation->unread_count) && $recruiter_conversation->unread_count > 0;
                
                // Generate DiceBear avatar URL
                $avatar_seed = urlencode($recruiter->first_name . ' ' . $recruiter->last_name . '-' . $recruiter->id);
                $styles = ['avataaars', 'micah', 'adventurer', 'big-ears', 'big-smile', 'bottts', 'croodles', 'miniavs'];
                $selected_style = $styles[$recruiter->id % count($styles)];
                $avatar_url = "https://api.dicebear.com/7.x/{$selected_style}/svg?seed={$avatar_seed}&radius=50&backgroundColor=22c55e&backgroundType=gradientLinear";
                ?>
                        <a href="<?php echo !empty($conversation_uuid) ? site_url('/agency/chat/conversation/' . $conversation_uuid) : '#'; ?>"
                            class="profile-item <?php echo $is_current ? 'active' : ''; ?>"
                            title="<?php echo htmlspecialchars($recruiter->first_name . ' ' . $recruiter->last_name); ?>"
                            data-recruiter-id="<?php echo $recruiter->id; ?>"
                            data-recruiter-name="<?php echo htmlspecialchars($recruiter->first_name); ?>"
                            onclick="return handleRecruiterSwitch(event, <?php echo $recruiter->id; ?>, '<?php echo htmlspecialchars($recruiter->first_name); ?>')"
                            id="recruiterProfile_<?php echo $recruiter->id; ?>">
                            <div class="profile-avatar-wrapper">
                                <div class="profile-avatar offline" data-avatar-seed="<?php echo $avatar_seed; ?>"
                                    data-avatar-style="<?php echo $selected_style; ?>"
                                    id="recruiterAvatar_<?php echo $recruiter->id; ?>">
                                    <img src="<?php echo $avatar_url; ?>"
                                        alt="<?php echo htmlspecialchars($recruiter->first_name); ?>" loading="lazy"
                                        onerror="this.onerror=null; this.src='https://api.dicebear.com/7.x/avataaars/svg?seed=<?php echo $avatar_seed; ?>&radius=50&backgroundColor=22c55e'">
                                    <?php if ($has_unread): ?>
                                    <span class="profile-unread-badge" id="unreadBadge_<?php echo $recruiter->id; ?>">
                                        <?php echo $recruiter_conversation->unread_count > 9 ? '9+' : $recruiter_conversation->unread_count; ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <div class="online-indicator" id="onlineIndicator_<?php echo $recruiter->id; ?>"
                                    style="display: none;"></div>
                            </div>
                            <div class="profile-name" id="profileName_<?php echo $recruiter->id; ?>">
                                <?php 
                        $name = htmlspecialchars($recruiter->first_name);
                        echo strlen($name) > 8 ? substr($name, 0, 8) . '...' : $name;
                        ?>
                            </div>
                        </a>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <div class="no-profiles-msg">
                            <small style="color: var(--text-muted);">No recruiters available</small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Main Chat Container -->
                <div style="height: calc(100% - 150px); display: flex; flex-direction: column;">
                    <!-- Chat Header -->
                    <div class="d-flex align-items-center justify-content-between p-3 border-bottom"
                        style="flex-shrink: 0;">
                        <!-- Left: Current Conversation Info -->
                        <div class="d-flex align-items-center">
                            <div class="sidebar-avatar mr-3" style="width: 40px; height: 40px; font-size: 1rem;">
                                <?php echo substr(htmlspecialchars($conversation->recruiter_name), 0, 1); ?>
                            </div>
                            <div>
                                <h5 style="margin: 0; color: var(--text-main); font-weight: 500; font-size: 1rem;">
                                    <?php echo htmlspecialchars($conversation->recruiter_name); ?>
                                </h5>
                                <small style="color: var(--text-muted); font-size: 0.75rem;">
                                    <?php echo $conversation->job_name ? 'Job: ' . htmlspecialchars($conversation->job_name) : 'General Chat'; ?>
                                    <?php 
                                    // Check if current recruiter is online
                                    $current_online = false;
                                    foreach ($available_recruiters as $recruiter) {
                                        if ($recruiter->id == $conversation->recruiter_id && !empty($recruiter->is_online)) {
                                            $current_online = true;
                                            break;
                                        }
                                    }
                                    ?>
                                    <!-- <?php if ($current_online): ?>
                                    <span style="color: var(--accent); margin-left: 8px;">• Online</span>
                                    <?php else: ?>
                                    <span style="color: var(--text-muted); margin-left: 8px;">• Offline</span>
                                    <?php endif; ?> -->
                                </small>
                            </div>
                        </div>

                        <!-- Right: Action Buttons -->
                        <div class="d-flex align-items-center gap-2">
                            <?php if (!empty($conversation->candidate_id)): ?>
                            <a href="<?php echo site_url('/agency/chat/switch_to_general/' . $conversation->uuid . '/' . $conversation->recruiter_id); ?>"
                                class="btn btn-sm"
                                style="background: var(--accent); color: white; border: none; border-radius: 6px; padding: 5px 10px; font-size: 0.7rem; display: flex; align-items: center; gap: 5px; text-decoration: none;">
                                <i class="fa fa-exchange-alt"></i> Switch to General
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Messages Area - Modern Design -->
                    <div id="chatMessages" style="flex: 1; overflow-y: auto;">
                        <?php if (!empty($messages)): ?>
                        <div id="messagesContainer">
                            <!-- Today's Date Separator -->
                            <div class="day-divider">Today</div>

                            <?php 
                            // Group messages by date if needed
                            $currentDate = null;
                            foreach ($messages as $message): 
                                $messageDate = date('Y-m-d', strtotime($message->created_at));
                                
                                // Add date separator if date changes
                                if ($currentDate !== $messageDate) {
                                    $currentDate = $messageDate;
                                    $displayDate = date('M j, Y', strtotime($message->created_at));
                                    if ($displayDate == date('M j, Y')) {
                                        $displayDate = 'Today';
                                    } elseif ($displayDate == date('M j, Y', strtotime('-1 day'))) {
                                        $displayDate = 'Yesterday';
                                    }
                                    ?>
                            <div class="day-divider"><?php echo $displayDate; ?></div>
                            <?php
                                }
                            ?>

                            <div class="message-row <?php echo $message->sender_type == 'agency' ? 'me' : 'them'; ?>"
                                data-message-id="<?php echo $message->id; ?>">
                                <div class="message-bubble">
                                    <?php 
                                    $messageContent = $message->message;
                                    if (strpos($messageContent, '<a ') !== false && strpos($messageContent, 'target="_blank"') !== false) {
                                        echo $messageContent;
                                    } else {
                                        echo nl2br(htmlspecialchars($messageContent));
                                    }
                                    ?>
                                    <div class="message-meta">
                                        <span class="message-time">
                                            <?php echo date('h:i A', strtotime($message->created_at)); ?>
                                        </span>
                                        <?php if ($message->sender_type == 'agency'): ?>
                                        <span class="message-status">
                                            <i class="fa fa-check<?php echo $message->is_read ? '-double' : ''; ?>"></i>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="no-messages"
                            style="display: flex; align-items: center; justify-content: center; height: 100%;">
                            <div class="text-center">
                                <i class="fa fa-comments fa-3x mb-3"></i>
                                <p style="font-size: 1rem; margin: 0;">No messages yet. Start the conversation!</p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Message Input - Modern Design -->
                    <div class="chat-input-area" style="flex-shrink: 0;">
                        <div class="chat-input-container">
                            <input type="text" class="chat-input" id="messageInput"
                                placeholder="Type a message and press Enter…">
                        </div>
                        <button class="send-btn" id="sendBtn" onclick="sendMessage()">
                            <span class="icon">➤</span>
                            <span>Send</span>
                        </button>
                    </div>
                </div>

                <!-- Hidden Fields -->
                <input type="hidden" id="conversationUuid" value="<?php echo $conversation->uuid; ?>">
                <input type="hidden" id="currentRecruiterId" value="<?php echo $conversation->recruiter_id; ?>">
                <input type="hidden" name="<?php echo $csrf_name; ?>" value="<?php echo $csrf_token; ?>">

                <?php else: ?>
                <!-- No Conversation Selected -->
                <div class="d-flex align-items-center justify-content-center h-100" style="flex: 1;">
                    <div class="text-center" style="color: var(--text-muted);">
                        <i class="fa fa-comments fa-4x mb-3"></i>
                        <h4>Welcome to Chat</h4>
                        <p>Select a conversation from the sidebar to start chatting,<br>or start a new chat with an
                            available recruiter.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<!-- Add this right before the closing </div> of #main-content -->
<div id="recruiterSwitchLoader" class="recruiter-switch-loader">
    <div class="loader-content">
        <div class="switch-loader-icon"></div>
        <div class="switch-loader-text">Switching to Recruiter...</div>
        <div class="switch-loader-subtext loading-pulse">
            Loading conversation, please wait
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
    currentConversationUuid: '<?php echo isset($conversation) ? $conversation->uuid : ""; ?>',
    displayedMessageIds: new Set(),
    pollInterval: null,
    conversationInterval: null,
    activePolling: true,
    currentRecruiterId: '<?php echo isset($conversation) ? $conversation->recruiter_id : ""; ?>'
};

// ===== ONLINE STATUS MANAGEMENT =====
let onlineStatusInterval = null;

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
    if (content.includes('<a ') && content.includes('target="_blank"')) {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = content;
        const links = tempDiv.querySelectorAll('a');
        links.forEach(link => {
            const href = link.getAttribute('href');
            if (href && href.startsWith('http')) {
                link.setAttribute('target', '_blank');
                link.setAttribute('rel', 'noopener noreferrer');
            }
        });
        return tempDiv.innerHTML;
    }
    const escaped = escapeHtml(content);
    return escaped.replace(/\n/g, '<br>');
}

// ===== MESSAGE DISPLAY FUNCTIONS =====
function createMessageElement(message, isAgency = false) {
    const messageRow = document.createElement('div');
    messageRow.className = `message-row ${isAgency ? 'me' : 'them'}`;
    messageRow.dataset.messageId = message.id;

    const messageTime = new Date(message.created_at);
    const timeString = messageTime.toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit'
    });

    const renderedContent = renderMessageContent(message.message || '');

    messageRow.innerHTML = `
        <div class="message-bubble">
            ${renderedContent}
            <div class="message-meta">
                <span class="message-time">${timeString}</span>
                ${isAgency ? `
                    <span class="message-status">
                        <i class="fa fa-check${message.is_read ? '-double' : ''}"></i>
                    </span>
                ` : ''}
            </div>
        </div>
    `;

    return messageRow;
}

function addMessageToDisplay(message, isAgency = false) {
    const chatMessages = document.getElementById('chatMessages');
    if (!chatMessages) return;

    if (chatState.displayedMessageIds.has(parseInt(message.id))) {
        return;
    }

    let messagesContainer = document.getElementById('messagesContainer');
    if (!messagesContainer) {
        const noMessages = chatMessages.querySelector('.no-messages');
        if (noMessages) noMessages.remove();
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

// ===== AJAX FUNCTIONS =====
async function makeAjaxRequest(endpoint, data = {}) {
    const baseUrl = window.location.origin + '/shoesmith/agency/chat/';
    let fullUrl = baseUrl + endpoint;

    const params = new URLSearchParams();
    const csrfToken = document.getElementById('csrf_rfid_token')?.value;
    if (csrfToken) {
        params.append('csrf_rfid_token', csrfToken);
    }

    Object.keys(data).forEach(key => {
        if (data[key] !== null && data[key] !== undefined) {
            params.append(key, data[key]);
        }
    });

    const queryString = params.toString();
    if (queryString) {
        fullUrl += '?' + queryString;
    }

    try {
        const response = await fetch(fullUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

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

// ===== MESSAGE SENDING =====
async function sendMessage() {
    const messageInput = document.getElementById('messageInput');
    const messageText = messageInput.value.trim();
    const conversationUuid = document.getElementById('conversationUuid')?.value;

    if (!messageText || chatState.isSending || !conversationUuid) {
        return;
    }

    chatState.isSending = true;

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
        const response = await makeAjaxRequest('ajax_send_message', {
            conversation_uuid: conversationUuid,
            message: messageText
        });

        if (response.success) {
            fetchChatNotifications();
            const tempMsg = document.querySelector(`[data-message-id="temp_${tempId}"]`);
            if (tempMsg) tempMsg.remove();

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
            }

            setTimeout(fetchUpdatedConversations, 500);
        } else {
            const tempMsg = document.querySelector(`[data-message-id="temp_${tempId}"]`);
            if (tempMsg) tempMsg.remove();
            messageInput.value = messageText;
            alert(response.message || "Failed to send message");
        }

    } catch (error) {
        console.error("Send fetch error:", error);
        const tempMsg = document.querySelector(`[data-message-id="temp_${tempId}"]`);
        if (tempMsg) tempMsg.remove();
        messageInput.value = messageText;
        alert("Network error. Please check your connection and try again.");
    } finally {
        chatState.isSending = false;
    }
}

// ===== ONLINE STATUS FUNCTIONS =====
async function checkRecruiterOnlineStatus() {
    try {
        // Remove the conversation_uuid parameter - we want ALL recruiters
        const response = await makeAjaxRequest('ajax_check_online_status', {});
        // Or just call the endpoint directly without parameters
        // const response = await fetch('/shoesmith/agency/chat/ajax_check_online_status');

        if (response.success && response.online_status) {
            updateOnlineStatusDisplay(response.online_status);
        }
    } catch (error) {
        console.error("Online status check failed:", error);
    }
}

function updateOnlineStatusDisplay(onlineData) {
    let onlineCount = 0;

    // Update each recruiter's online status
    Object.keys(onlineData).forEach(recruiterId => {
        const isOnline = onlineData[recruiterId];
        const profile = document.getElementById(`recruiterProfile_${recruiterId}`);
        const avatar = document.getElementById(`recruiterAvatar_${recruiterId}`);
        const indicator = document.getElementById(`onlineIndicator_${recruiterId}`);

        if (profile && avatar && indicator) {
            // Update data attribute
            profile.setAttribute('data-is-online', isOnline ? '1' : '0');

            // Update avatar class
            avatar.classList.remove('online', 'offline');
            avatar.classList.add(isOnline ? 'online' : 'offline');

            // Show/hide online indicator
            if (isOnline) {
                indicator.style.display = 'block';
                onlineCount++;
            } else {
                indicator.style.display = 'none';
            }
        }
    });

    // Update online count display
    const onlineDisplay = document.getElementById('onlineCountDisplay');
    if (onlineDisplay) {
        onlineDisplay.textContent = `${onlineCount} online`;
    }
}

// Replace your startOnlineStatusPolling function with this:
function startOnlineStatusPolling() {
    if (onlineStatusInterval) clearInterval(onlineStatusInterval);

    // Check immediately
    checkRecruiterOnlineStatus();

    // Then every 10 seconds for testing (change back to 30000 for production)
    onlineStatusInterval = setInterval(checkRecruiterOnlineStatus, 10000);
}

// Add this debug function to help:
function forceOnlineStatusRefresh() {
    console.log("Forcing online status refresh...");
    checkRecruiterOnlineStatus();
}

// Call this from console to force a refresh
window.forceOnlineStatusRefresh = forceOnlineStatusRefresh;

function stopOnlineStatusPolling() {
    if (onlineStatusInterval) {
        clearInterval(onlineStatusInterval);
        onlineStatusInterval = null;
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

        if (response.success && response.messages) {
            response.messages.forEach(message => {
                const isAgency = message.sender_type === 'agency';
                addMessageToDisplay(message, isAgency);
            });

            if (response.messages.length > 0) {
                const lastMsg = response.messages[response.messages.length - 1];
                chatState.lastMessageId = parseInt(lastMsg.id);
            }
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
            updateConversationBadges(response.conversations);
            if (response.total_unread_count !== undefined) {
                updateAllNotificationBadges(response.total_unread_count);
            }

            // Also update online status
            updateOnlineStatus(response.conversations);
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
}

function updateOnlineStatus(conversationsData) {
    let onlineCount = 0;

    conversationsData.forEach(conv => {
        const profileItem = document.querySelector(`.profile-item[data-recruiter-id="${conv.recruiter_id}"]`);
        if (profileItem) {
            const isOnline = conv.is_online || false;
            const avatar = profileItem.querySelector('.profile-avatar');
            const onlineIndicator = profileItem.querySelector('.online-indicator');

            // Update online/offline class
            if (avatar) {
                avatar.classList.remove('online', 'offline');
                avatar.classList.add(isOnline ? 'online' : 'offline');
            }

            // Update online indicator
            if (onlineIndicator) {
                if (isOnline) {
                    onlineIndicator.style.display = 'block';
                } else {
                    onlineIndicator.style.display = 'none';
                }
            }

            // Update data attribute
            profileItem.setAttribute('data-is-online', isOnline ? '1' : '0');

            // Count online
            if (isOnline) {
                onlineCount++;
            }
        }
    });

    // Update online count display
    const onlineCountElement = document.querySelector('.profiles-header .view-all');
    if (onlineCountElement) {
        onlineCountElement.textContent = onlineCount + ' online';
    }
}

function updateConversationBadges(conversationsData) {
    conversationsData.forEach(conv => {
        const conversationItem = document.querySelector(`.chat-item[data-conversation-id="${conv.uuid}"]`);
        if (conversationItem) {
            const unreadBadge = conversationItem.querySelector('.chat-unread');

            if (conv.unread_count > 0) {
                if (!unreadBadge) {
                    const metaDiv = conversationItem.querySelector('.chat-meta');
                    const newBadge = document.createElement('div');
                    newBadge.className = 'chat-unread';
                    metaDiv.appendChild(newBadge);
                }
                conversationItem.querySelector('.chat-unread').textContent = conv.unread_count;
                const preview = conversationItem.querySelector('.chat-last-msg');
                if (preview) {
                    preview.textContent = conv.last_message;
                    preview.style.fontWeight = '600';
                }
            } else {
                if (unreadBadge) unreadBadge.remove();
                const preview = conversationItem.querySelector('.chat-last-msg');
                if (preview) {
                    preview.textContent = conv.last_message || 'No messages yet';
                    preview.style.fontWeight = '400';
                }
            }
        }
    });
}

// ===== PROFILE MANAGEMENT =====
function updateActiveProfile(recruiterId = null) {
    // If no recruiterId provided, get from current conversation
    if (!recruiterId) {
        const currentRecruiterField = document.getElementById('currentRecruiterId');
        recruiterId = currentRecruiterField ? currentRecruiterField.value : null;
    }

    if (!recruiterId) return;

    // Remove active class from all profiles
    document.querySelectorAll('.profile-item').forEach(item => {
        item.classList.remove('active');
    });

    // Add active class to current recruiter's profile
    const currentProfile = document.querySelector(`.profile-item[data-recruiter-id="${recruiterId}"]`);
    if (currentProfile) {
        currentProfile.classList.add('active');
    }
}

// ===== AVATAR ANIMATION FUNCTIONS =====
function setupAvatarAnimations() {
    const profileAvatars = document.querySelectorAll('.profile-avatar');

    profileAvatars.forEach(avatar => {
        const originalSeed = avatar.getAttribute('data-avatar-seed');
        const originalStyle = avatar.getAttribute('data-avatar-style');
        const img = avatar.querySelector('img');

        if (!originalSeed || !img) return;

        // List of fun DiceBear styles to rotate through
        const styles = [
            'avataaars', 'micah', 'adventurer', 'big-ears',
            'big-smile', 'bottts', 'croodles', 'miniavs',
            'open-peeps', 'personas', 'pixel-art'
        ];

        let currentIndex = styles.indexOf(originalStyle);
        if (currentIndex === -1) currentIndex = 0;

        let animationInterval;

        avatar.addEventListener('mouseenter', function() {
            let counter = 0;
            const maxChanges = 8; // Number of style changes during hover

            animationInterval = setInterval(() => {
                currentIndex = (currentIndex + 1) % styles.length;
                const newStyle = styles[currentIndex];

                // Update the image with new style
                img.src =
                    `https://api.dicebear.com/7.x/${newStyle}/svg?seed=${originalSeed}&radius=50&backgroundColor=22c55e&backgroundType=gradientLinear`;

                counter++;
                if (counter >= maxChanges) {
                    clearInterval(animationInterval);
                    // Return to original style after animation
                    setTimeout(() => {
                        img.src =
                            `https://api.dicebear.com/7.x/${originalStyle}/svg?seed=${originalSeed}&radius=50&backgroundColor=22c55e&backgroundType=gradientLinear`;
                    }, 500);
                }
            }, 100); // Change every 100ms
        });

        avatar.addEventListener('mouseleave', function() {
            if (animationInterval) {
                clearInterval(animationInterval);
            }
            // Return to original style
            img.src =
                `https://api.dicebear.com/7.x/${originalStyle}/svg?seed=${originalSeed}&radius=50&backgroundColor=22c55e&backgroundType=gradientLinear`;
        });
    });
}

// ===== FIX FOR PROFILE ACTIVE STATE =====
function highlightClickedProfile(profileItem) {
    // Remove active class from all profiles
    document.querySelectorAll('.profile-item').forEach(item => {
        item.classList.remove('active');
    });

    // Add active class to clicked profile
    profileItem.classList.add('active');

    // Store in sessionStorage for consistency
    const recruiterId = profileItem.getAttribute('data-recruiter-id');
    if (recruiterId) {
        sessionStorage.setItem('lastActiveRecruiter', recruiterId);
    }
}

// ===== INITIALIZATION =====
function initializeDisplayedMessages() {
    const existingMessages = document.querySelectorAll('#messagesContainer .message-row');
    existingMessages.forEach(msg => {
        const messageId = msg.dataset.messageId;
        if (messageId) {
            chatState.displayedMessageIds.add(parseInt(messageId));
        }
    });
}

function startPolling() {
    if (!chatState.currentConversationUuid) return;

    if (chatState.pollInterval) clearInterval(chatState.pollInterval);
    if (chatState.conversationInterval) clearInterval(chatState.conversationInterval);

    chatState.pollInterval = setInterval(fetchNewMessages, 3000);
    chatState.conversationInterval = setInterval(fetchUpdatedConversations, 15000);

    setTimeout(fetchNewMessages, 500);
    setTimeout(fetchUpdatedConversations, 1000);
}

function stopPolling() {
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

function setupEventListeners() {
    const messageInput = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendBtn');

    if (messageInput) {
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        setTimeout(() => {
            messageInput.focus();
        }, 1000);
    }

    if (sendButton) {
        sendButton.addEventListener('click', sendMessage);
    }

    const filterTabs = document.querySelectorAll('.filter-tab');
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const filter = this.getAttribute('data-chat-type');
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            const currentUrl = new URL(window.location.href);
            if (filter === 'all') {
                currentUrl.searchParams.delete('chat_type');
            } else {
                currentUrl.searchParams.set('chat_type', filter);
            }

            window.location.href = currentUrl.toString();
        });
    });

    const searchInput = document.querySelector('.form-control[placeholder="Find or start a conversation"]');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const conversationItems = document.querySelectorAll('.chat-item');

            conversationItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(searchTerm) ? 'flex' : 'none';
            });
        });
    }

    // Profile click handlers for better UX
    document.querySelectorAll('.profile-item').forEach(item => {
        item.addEventListener('click', function() {
            const recruiterId = this.getAttribute('data-recruiter-id');
            if (recruiterId) {
                // Update current recruiter ID immediately
                const hiddenField = document.getElementById('currentRecruiterId');
                if (hiddenField) {
                    hiddenField.value = recruiterId;
                }

                // Update chat state
                chatState.currentRecruiterId = recruiterId;

                // Highlight the clicked profile
                highlightClickedProfile(this);
            }
        });
    });

    // Chat item click handlers
    document.querySelectorAll('.chat-item').forEach(item => {
        item.addEventListener('click', function() {
            const recruiterId = this.getAttribute('data-recruiter-id');
            if (recruiterId) {
                // Update current recruiter ID
                const hiddenField = document.getElementById('currentRecruiterId');
                if (hiddenField) {
                    hiddenField.value = recruiterId;
                }

                // Update chat state
                chatState.currentRecruiterId = recruiterId;

                // Update active profile after a short delay
                setTimeout(() => updateActiveProfile(recruiterId), 300);
            }
        });
    });
}

function initializeChatSystem() {
    if (chatState.currentConversationUuid) {
        initializeDisplayedMessages();
        setupEventListeners();
        startPolling();
        startOnlineStatusPolling();
        scrollToBottom();

        // Initialize active profile
        updateActiveProfile();

        // Initialize online count display with PHP value
        const onlineDisplay = document.getElementById('onlineCountDisplay');
        if (onlineDisplay) {
            // Use the PHP count initially
            onlineDisplay.textContent = '<?php echo $online_recruiters_count; ?> online';
        }
    }
}

// ===== MAIN INITIALIZATION =====
document.addEventListener('DOMContentLoaded', function() {
    // Initialize avatar animations
    setTimeout(setupAvatarAnimations, 1000);

    // Also setup for dynamically loaded avatars
    const observer = new MutationObserver(setupAvatarAnimations);
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    // Initialize chat system
    initializeChatSystem();
});

// ===== PAGE VISIBILITY HANDLERS =====
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        stopPolling();
        stopOnlineStatusPolling();
    } else {
        chatState.activePolling = true;
        startPolling();
        startOnlineStatusPolling();
    }
});
// ===== RECRUITER SWITCH LOADER FUNCTIONS =====
function showRecruiterSwitchLoader(recruiterName) {
    const loader = document.getElementById('recruiterSwitchLoader');
    const loaderText = loader.querySelector('.switch-loader-text');

    if (recruiterName) {
        loaderText.textContent = `Switching to ${recruiterName}...`;
    } else {
        loaderText.textContent = 'Switching to Recruiter...';
    }

    loader.classList.add('active');

    // Add to body to prevent scrolling
    document.body.style.overflow = 'hidden';
}

function hideRecruiterSwitchLoader() {
    const loader = document.getElementById('recruiterSwitchLoader');
    loader.classList.remove('active');

    // Restore scrolling
    document.body.style.overflow = '';
}

// Add this function to handle recruiter switching with loader
function handleRecruiterSwitch(event, recruiterId, recruiterName = '') {
    // Prevent default link behavior
    event.preventDefault();
    event.stopPropagation();

    // Show loader
    showRecruiterSwitchLoader(recruiterName);

    // Get the link URL
    const link = event.currentTarget;
    const url = link.getAttribute('href');

    // Navigate after a small delay to show the loader
    setTimeout(() => {
        window.location.href = url;
    }, 300);

    return false;
}

// Also handle browser back/forward buttons
window.addEventListener('pageshow', function(event) {
    // Hide loader when page is fully loaded (including from cache)
    if (event.persisted) {
        hideRecruiterSwitchLoader();
    }
});

window.addEventListener('load', function() {
    // Hide loader when page loads
    setTimeout(hideRecruiterSwitchLoader, 500);
});
</script>