{{-- Global AI Chat Assistant Floating Widget --}}
<div id="ai-chat-root" class="ai-chat-root" aria-live="polite">
    <!-- Floating Launcher Trigger Button -->
    <button type="button" 
            id="ai-chat-launcher" 
            class="ai-chat-launcher" 
            aria-label="Open AI Assistant Chat" 
            aria-expanded="false" 
            aria-controls="ai-chat-panel">
        <div class="ai-launcher-inner">
            <!-- Spark / Bot Icon -->
            <svg class="ai-icon-sparkle" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/>
                <path d="M5 3v4"/>
                <path d="M19 17v4"/>
                <path d="M3 5h4"/>
                <path d="M17 19h4"/>
            </svg>
            <!-- Close icon shown when open -->
            <svg class="ai-icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </div>
        <span class="ai-launcher-badge">AI</span>
        <span class="ai-launcher-pulse"></span>
    </button>

    <!-- Floating Chat Window Panel -->
    <div id="ai-chat-panel" class="ai-chat-panel" role="dialog" aria-modal="false" aria-label="AI Assistant" aria-hidden="true">
        <!-- Chat Header -->
        <div class="ai-chat-header">
            <div class="ai-header-info">
                <div class="ai-avatar-wrap">
                    <div class="ai-avatar-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/>
                        </svg>
                    </div>
                    <span class="ai-status-dot" title="Online & Ready"></span>
                </div>
                <div>
                    <h3 class="ai-chat-title">
                        AI Looping Assistant
                        <span class="ai-role-pill" id="ai-user-role-badge">
                            @if(auth()->check() && auth()->user()->isCompany())
                                Recruiter
                            @elseif(auth()->check())
                                Candidate
                            @else
                                Guide
                            @endif
                        </span>
                    </h3>
                    <p class="ai-chat-subtitle" id="ai-chat-status-text">Ask about jobs, features, or hiring</p>
                </div>
            </div>
            <div class="ai-header-actions">
                <button type="button" class="ai-header-btn" id="ai-clear-btn" title="Clear conversation" aria-label="Clear conversation">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                </button>
                <button type="button" class="ai-header-btn" id="ai-close-btn" title="Minimize chat" aria-label="Close chat window">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Chat Conversation Messages Body -->
        <div class="ai-chat-body" id="ai-chat-body">
            <!-- Messages rendered dynamically here via JS -->
            <div class="ai-messages-container" id="ai-messages-container"></div>

            <!-- Typing indicator -->
            <div class="ai-typing-indicator" id="ai-typing-indicator" style="display: none;">
                <div class="ai-typing-bubble">
                    <span class="ai-dot"></span>
                    <span class="ai-dot"></span>
                    <span class="ai-dot"></span>
                </div>
                <span class="ai-typing-text">AI is thinking...</span>
            </div>
        </div>

        <!-- Quick Suggestion Chips Container -->
        <div class="ai-suggestions-bar" id="ai-suggestions-bar">
            <!-- Chips rendered dynamically -->
        </div>

        <!-- Chat Input Footer -->
        <div class="ai-chat-footer">
            <form id="ai-chat-form" class="ai-chat-form" onsubmit="return false;">
                @csrf
                <div class="ai-input-wrapper">
                    <textarea id="ai-chat-input" 
                              rows="1" 
                              placeholder="Ask about jobs, skills, or features..." 
                              aria-label="Your message to AI Assistant"
                              maxlength="2000"></textarea>
                    <button type="submit" id="ai-send-btn" class="ai-send-btn" aria-label="Send Message" disabled>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>
                </div>
            </form>
            <div class="ai-footer-note">
                <span>Press <kbd>Enter</kbd> to send, <kbd>Shift + Enter</kbd> for new line</span>
            </div>
        </div>
    </div>
</div>

<style>
/* ==========================================================================
   AI Chat Floating Widget - Core Theme & Styles
   ========================================================================== */
.ai-chat-root {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 1040;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}

/* Floating Launcher Button */
.ai-chat-launcher {
    width: 60px;
    height: 60px;
    border-radius: 9999px;
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: #ffffff;
    border: none;
    outline: none;
    cursor: pointer;
    box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.5), 0 8px 10px -6px rgba(124, 58, 237, 0.4);
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease;
}

.ai-chat-launcher:hover {
    transform: scale(1.08) translateY(-2px);
    box-shadow: 0 16px 32px -6px rgba(79, 70, 229, 0.6), 0 10px 14px -6px rgba(124, 58, 237, 0.5);
}

.ai-chat-launcher:active {
    transform: scale(0.96);
}

.ai-launcher-inner {
    position: relative;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ai-icon-sparkle,
.ai-icon-close {
    width: 26px;
    height: 26px;
    transition: opacity 0.22s ease, transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    position: absolute;
}

.ai-icon-sparkle {
    opacity: 1;
    transform: rotate(0deg) scale(1);
}

.ai-icon-close {
    opacity: 0;
    transform: rotate(-90deg) scale(0.6);
}

.ai-chat-launcher.is-open .ai-icon-sparkle {
    opacity: 0;
    transform: rotate(90deg) scale(0.6);
}

.ai-chat-launcher.is-open .ai-icon-close {
    opacity: 1;
    transform: rotate(0deg) scale(1);
}

.ai-launcher-badge {
    position: absolute;
    top: -3px;
    right: -3px;
    background: #10b981;
    color: #ffffff;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.5px;
    padding: 2px 6px;
    border-radius: 9999px;
    border: 2px solid #ffffff;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

.ai-launcher-pulse {
    position: absolute;
    inset: -4px;
    border-radius: 9999px;
    border: 2px solid #818cf8;
    opacity: 0;
    pointer-events: none;
    animation: ai-pulse 3s infinite;
}

@keyframes ai-pulse {
    0% { transform: scale(1); opacity: 0.6; }
    50% { transform: scale(1.18); opacity: 0; }
    100% { transform: scale(1.18); opacity: 0; }
}

/* Chat Window Panel */
.ai-chat-panel {
    position: absolute;
    bottom: 74px;
    right: 0;
    width: 400px;
    height: 590px;
    max-height: calc(100vh - 110px);
    background: #0f172a;
    color: #f1f5f9;
    border-radius: 20px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.55), 0 0 0 1px rgba(255, 255, 255, 0.1);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    opacity: 0;
    visibility: hidden;
    transform: translateY(18px) scale(0.96);
    transform-origin: bottom right;
    transition: opacity 0.24s cubic-bezier(0.16, 1, 0.3, 1), 
                transform 0.24s cubic-bezier(0.16, 1, 0.3, 1), 
                visibility 0.24s;
}

.ai-chat-panel.is-open {
    opacity: 1;
    visibility: visible;
    transform: translateY(0) scale(1);
}

/* Header */
.ai-chat-header {
    background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%);
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    padding: 14px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.ai-header-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.ai-avatar-wrap {
    position: relative;
}

.ai-avatar-icon {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);
}

.ai-avatar-icon svg {
    width: 20px;
    height: 20px;
}

.ai-status-dot {
    position: absolute;
    bottom: -1px;
    right: -1px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #10b981;
    border: 2px solid #0f172a;
}

.ai-chat-title {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #ffffff;
    display: flex;
    align-items: center;
    gap: 8px;
    line-height: 1.2;
}

.ai-role-pill {
    display: inline-block;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 9999px;
    background: rgba(99, 102, 241, 0.22);
    color: #a5b4fc;
    border: 1px solid rgba(129, 140, 248, 0.3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.ai-chat-subtitle {
    margin: 3px 0 0 0;
    font-size: 12px;
    color: #94a3b8;
    line-height: 1.2;
}

.ai-header-actions {
    display: flex;
    align-items: center;
    gap: 6px;
}

.ai-header-btn {
    background: transparent;
    border: none;
    color: #94a3b8;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease;
}

.ai-header-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff;
}

.ai-header-btn svg {
    width: 17px;
    height: 17px;
}

/* Chat Body */
.ai-chat-body {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    background: #0f172a;
    scroll-behavior: smooth;
}

.ai-chat-body::-webkit-scrollbar {
    width: 5px;
}

.ai-chat-body::-webkit-scrollbar-track {
    background: transparent;
}

.ai-chat-body::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 10px;
}

.ai-messages-container {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

/* Message Bubbles */
.ai-msg {
    display: flex;
    gap: 10px;
    max-width: 90%;
    animation: ai-slide-in 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes ai-slide-in {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}

.ai-msg.user {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.ai-msg.assistant {
    align-self: flex-start;
}

.ai-msg-bubble {
    padding: 11px 15px;
    border-radius: 16px;
    font-size: 13.5px;
    line-height: 1.5;
    word-break: break-word;
}

.ai-msg.user .ai-msg-bubble {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    color: #ffffff;
    border-bottom-right-radius: 4px;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.28);
}

.ai-msg.assistant .ai-msg-bubble {
    background: #1e293b;
    color: #e2e8f0;
    border-bottom-left-radius: 4px;
    border: 1px solid rgba(255, 255, 255, 0.08);
}

/* Markdown styling inside assistant bubble */
.ai-msg.assistant .ai-msg-bubble p {
    margin: 0 0 8px 0;
}

.ai-msg.assistant .ai-msg-bubble p:last-child {
    margin-bottom: 0;
}

.ai-msg.assistant .ai-msg-bubble ul,
.ai-msg.assistant .ai-msg-bubble ol {
    margin: 4px 0 8px 18px;
    padding: 0;
}

.ai-msg.assistant .ai-msg-bubble li {
    margin-bottom: 4px;
}

.ai-msg.assistant .ai-msg-bubble strong {
    color: #ffffff;
    font-weight: 600;
}

.ai-msg.assistant .ai-msg-bubble a {
    color: #818cf8;
    text-decoration: none;
    border-bottom: 1px dotted rgba(129, 140, 248, 0.5);
    font-weight: 600;
    transition: color 0.15s, border-color 0.15s;
}

.ai-msg.assistant .ai-msg-bubble a:hover {
    color: #a5b4fc;
    border-bottom-style: solid;
}

/* Job Cards Container */
.ai-jobs-grid {
    margin-top: 10px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.ai-job-card {
    background: rgba(15, 23, 42, 0.65);
    border: 1px solid rgba(99, 102, 241, 0.25);
    border-radius: 12px;
    padding: 10px 12px;
    display: flex;
    flex-direction: column;
    gap: 5px;
    transition: border-color 0.18s, transform 0.18s;
}

.ai-job-card:hover {
    border-color: #6366f1;
    transform: translateY(-1px);
}

.ai-job-card-title {
    font-size: 13.5px;
    font-weight: 700;
    color: #ffffff;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.ai-job-card-company {
    font-size: 12px;
    color: #94a3b8;
}

.ai-job-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin-top: 2px;
}

.ai-job-badge {
    font-size: 11px;
    padding: 2px 6px;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.08);
    color: #cbd5e1;
}

.ai-job-badge.salary {
    background: rgba(16, 185, 129, 0.15);
    color: #34d399;
}

.ai-job-action-btn {
    margin-top: 4px;
    align-self: flex-start;
    font-size: 12px;
    font-weight: 600;
    color: #a5b4fc;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    background: rgba(79, 70, 229, 0.15);
    border-radius: 6px;
    border: 1px solid rgba(99, 102, 241, 0.25);
    transition: background 0.15s, color 0.15s;
}

.ai-job-action-btn:hover {
    background: rgba(79, 70, 229, 0.35);
    color: #ffffff;
}

/* Typing Indicator */
.ai-typing-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: 4px;
}

.ai-typing-bubble {
    background: #1e293b;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 14px;
    padding: 8px 12px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.ai-typing-bubble .ai-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #94a3b8;
    animation: ai-dot-bounce 1.3s infinite ease-in-out;
}

.ai-typing-bubble .ai-dot:nth-child(2) {
    animation-delay: 0.18s;
}

.ai-typing-bubble .ai-dot:nth-child(3) {
    animation-delay: 0.36s;
}

@keyframes ai-dot-bounce {
    0%, 80%, 100% { transform: translateY(0); opacity: 0.4; }
    40% { transform: translateY(-5px); opacity: 1; }
}

.ai-typing-text {
    font-size: 11px;
    color: #64748b;
}

/* Suggestion Chips */
.ai-suggestions-bar {
    padding: 8px 14px 10px 14px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    background: #0f172a;
}

.ai-chip {
    font-size: 11.5px;
    font-weight: 500;
    color: #cbd5e1;
    background: #1e293b;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 9999px;
    padding: 5px 11px;
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s, color 0.15s, transform 0.15s;
    user-select: none;
}

.ai-chip:hover {
    background: #334155;
    border-color: #6366f1;
    color: #ffffff;
    transform: translateY(-1px);
}

/* Footer & Input */
.ai-chat-footer {
    background: #111827;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    padding: 12px 14px;
}

.ai-chat-form {
    margin: 0;
}

.ai-input-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #1f2937;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 12px;
    padding: 6px 10px;
    transition: border-color 0.18s, box-shadow 0.18s;
}

.ai-input-wrapper:focus-within {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
}

#ai-chat-input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    color: #ffffff;
    font-size: 13.5px;
    resize: none;
    max-height: 100px;
    line-height: 1.4;
    padding: 4px 0;
    font-family: inherit;
}

#ai-chat-input::placeholder {
    color: #64748b;
}

.ai-send-btn {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #4f46e5;
    color: #ffffff;
    border: none;
    outline: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.15s, opacity 0.15s, transform 0.15s;
    flex-shrink: 0;
}

.ai-send-btn:hover:not(:disabled) {
    background: #4338ca;
    transform: scale(1.05);
}

.ai-send-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    background: #374151;
}

.ai-send-btn svg {
    width: 15px;
    height: 15px;
}

.ai-footer-note {
    margin-top: 6px;
    font-size: 10.5px;
    color: #64748b;
    text-align: center;
}

.ai-footer-note kbd {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
    padding: 1px 4px;
    font-size: 9.5px;
    color: #94a3b8;
}

/* ==========================================================================
   Responsive Adaptations
   ========================================================================== */
@media (max-width: 640px) {
    .ai-chat-root {
        bottom: 16px;
        right: 16px;
    }

    .ai-chat-launcher {
        width: 52px;
        height: 52px;
    }

    .ai-launcher-inner {
        width: 24px;
        height: 24px;
    }

    .ai-icon-sparkle,
    .ai-icon-close {
        width: 22px;
        height: 22px;
    }

    .ai-chat-panel {
        position: fixed;
        top: 12px;
        bottom: 12px;
        left: 12px;
        right: 12px;
        width: auto;
        height: auto;
        max-height: none;
        border-radius: 18px;
        transform-origin: bottom right;
    }
}
</style>

<script>
/**
 * AI Looping Global AI Chat Widget Controller
 * Pure Vanilla JavaScript with LocalStorage Persistence
 */
(function() {
    'use strict';

    const STORAGE_KEY = 'ai_looping_chat_history_v1';
    const MAX_LOCAL_HISTORY = 20;

    let chatState = {
        isOpen: false,
        isLoading: false,
        messages: [],
        suggestions: [],
        role: 'guest',
        userName: 'Guest'
    };

    // DOM Elements
    const root = document.getElementById('ai-chat-root');
    const launcher = document.getElementById('ai-chat-launcher');
    const panel = document.getElementById('ai-chat-panel');
    const closeBtn = document.getElementById('ai-close-btn');
    const clearBtn = document.getElementById('ai-clear-btn');
    const messagesContainer = document.getElementById('ai-messages-container');
    const chatBody = document.getElementById('ai-chat-body');
    const suggestionsBar = document.getElementById('ai-suggestions-bar');
    const input = document.getElementById('ai-chat-input');
    const sendBtn = document.getElementById('ai-send-btn');
    const chatForm = document.getElementById('ai-chat-form');
    const typingIndicator = document.getElementById('ai-typing-indicator');
    const statusText = document.getElementById('ai-chat-status-text');

    // Simple Markdown parsing helper (handles bold, italics, links, lists, code, line breaks)
    function parseMarkdown(text) {
        if (!text) return '';
        let escaped = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        // Links: [text](url)
        escaped = escaped.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_self">$1</a>');

        // Bold: **text**
        escaped = escaped.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');

        // Italic: *text*
        escaped = escaped.replace(/\*([^*]+)\*/g, '<em>$1</em>');

        // Inline code: `code`
        escaped = escaped.replace(/`([^`]+)`/g, '<code style="background:rgba(255,255,255,0.1);padding:1px 4px;border-radius:4px;font-size:12px;">$1</code>');

        // Bullet lists
        let lines = escaped.split('\n');
        let inList = false;
        let html = '';

        lines.forEach(function(line) {
            let trimmed = line.trim();
            if (trimmed.startsWith('• ') || trimmed.startsWith('- ') || trimmed.startsWith('* ')) {
                if (!inList) {
                    html += '<ul style="margin:4px 0 8px 16px;padding:0;">';
                    inList = true;
                }
                html += '<li>' + trimmed.substring(2) + '</li>';
            } else {
                if (inList) {
                    html += '</ul>';
                    inList = false;
                }
                if (trimmed.length > 0) {
                    html += '<p style="margin:0 0 6px 0;">' + line + '</p>';
                }
            }
        });

        if (inList) {
            html += '</ul>';
        }

        return html;
    }

    // Scroll chat body to bottom
    function scrollToBottom() {
        if (chatBody) {
            chatBody.scrollTop = chatBody.scrollHeight;
        }
    }

    // Toggle Chat Window
    function toggleChat(open) {
        chatState.isOpen = (open !== undefined) ? open : !chatState.isOpen;
        if (chatState.isOpen) {
            panel.classList.add('is-open');
            launcher.classList.add('is-open');
            panel.setAttribute('aria-hidden', 'false');
            launcher.setAttribute('aria-expanded', 'true');
            scrollToBottom();
            setTimeout(function() {
                input.focus();
            }, 100);
        } else {
            panel.classList.remove('is-open');
            launcher.classList.remove('is-open');
            panel.setAttribute('aria-hidden', 'true');
            launcher.setAttribute('aria-expanded', 'false');
        }
    }

    // Save history to localStorage
    function saveHistory() {
        try {
            const dataToSave = chatState.messages.slice(-MAX_LOCAL_HISTORY);
            localStorage.setItem(STORAGE_KEY, JSON.stringify(dataToSave));
        } catch (e) {
            console.warn('AI Chat: Unable to save history in localStorage', e);
        }
    }

    // Load history from localStorage or fetch context
    function loadHistoryOrInit() {
        try {
            const stored = localStorage.getItem(STORAGE_KEY);
            if (stored) {
                const parsed = JSON.parse(stored);
                if (Array.isArray(parsed) && parsed.length > 0) {
                    chatState.messages = parsed;
                    renderMessages();
                    fetchContextOnly(); // Get updated suggestion chips
                    return;
                }
            }
        } catch (e) {
            console.warn('AI Chat: Error loading localStorage history', e);
        }

        // If no stored history, initialize fresh with context
        fetchContextAndInit();
    }

    // Fetch context and suggestion chips
    function fetchContextOnly() {
        fetch('{{ route("ai_chat.context") }}', {
            headers: { 'Accept': 'application/json' }
        })
        .then(function(res) { return res.json(); })
        .then(function(res) {
            if (res.status === 'success' && res.data) {
                chatState.suggestions = res.data.suggestions || [];
                renderSuggestions();
            }
        })
        .catch(function(err) {
            console.warn('AI Chat context fetch failed', err);
        });
    }

    // Fetch initial context and seed welcome message
    function fetchContextAndInit() {
        fetch('{{ route("ai_chat.context") }}', {
            headers: { 'Accept': 'application/json' }
        })
        .then(function(res) { return res.json(); })
        .then(function(res) {
            if (res.status === 'success' && res.data) {
                chatState.role = res.data.role;
                chatState.userName = res.data.user_name;
                chatState.suggestions = res.data.suggestions || [];

                // Push initial greeting
                chatState.messages = [{
                    role: 'assistant',
                    content: res.data.welcome_message,
                    timestamp: Date.now()
                }];

                saveHistory();
                renderMessages();
                renderSuggestions();
            }
        })
        .catch(function(err) {
            console.warn('AI Chat initial context failed', err);
            chatState.messages = [{
                role: 'assistant',
                content: "Hello! 👋 I'm your **AI Looping Assistant**. Ask me anything about finding jobs, tailoring your resume, or platform features!",
                timestamp: Date.now()
            }];
            renderMessages();
        });
    }

    // Render Messages to DOM
    function renderMessages() {
        if (!messagesContainer) return;
        messagesContainer.innerHTML = '';

        chatState.messages.forEach(function(msg) {
            const msgEl = document.createElement('div');
            msgEl.className = 'ai-msg ' + (msg.role === 'user' ? 'user' : 'assistant');

            const bubble = document.createElement('div');
            bubble.className = 'ai-msg-bubble';

            if (msg.role === 'user') {
                bubble.textContent = msg.content;
            } else {
                bubble.innerHTML = parseMarkdown(msg.content);

                // Render job cards if available
                if (msg.jobs && Array.isArray(msg.jobs) && msg.jobs.length > 0) {
                    const jobsWrap = document.createElement('div');
                    jobsWrap.className = 'ai-jobs-grid';

                    msg.jobs.forEach(function(job) {
                        const card = document.createElement('div');
                        card.className = 'ai-job-card';

                        card.innerHTML = `
                            <div class="ai-job-card-title">
                                <span>${escapeHtml(job.title)}</span>
                            </div>
                            <div class="ai-job-card-company">${escapeHtml(job.company)}</div>
                            <div class="ai-job-badges">
                                <span class="ai-job-badge">${escapeHtml(job.location)}</span>
                                <span class="ai-job-badge">${escapeHtml(job.job_type)}</span>
                                ${job.salary_range ? `<span class="ai-job-badge salary">${escapeHtml(job.salary_range)}</span>` : ''}
                            </div>
                            <a href="${job.url}" class="ai-job-action-btn">
                                View Job Details &rarr;
                            </a>
                        `;
                        jobsWrap.appendChild(card);
                    });

                    bubble.appendChild(jobsWrap);
                }
            }

            msgEl.appendChild(bubble);
            messagesContainer.appendChild(msgEl);
        });

        scrollToBottom();
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Render suggestion chips
    function renderSuggestions() {
        if (!suggestionsBar) return;
        suggestionsBar.innerHTML = '';

        if (!chatState.suggestions || chatState.suggestions.length === 0) {
            suggestionsBar.style.display = 'none';
            return;
        }

        suggestionsBar.style.display = 'flex';
        chatState.suggestions.forEach(function(prompt) {
            const chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'ai-chip';
            chip.textContent = prompt;
            chip.addEventListener('click', function() {
                // Remove emoji prefix if any for cleaner prompt
                const cleanPrompt = prompt.replace(/^[\u{1F300}-\u{1F9FF}\u{2600}-\u{26FF}\u{2700}-\u{27BF}\s]+/u, '').trim();
                sendMessage(cleanPrompt || prompt);
            });
            suggestionsBar.appendChild(chip);
        });
    }

    // Send Message Handler
    function sendMessage(textToSend) {
        const message = (textToSend || input.value || '').trim();
        if (!message || chatState.isLoading) return;

        // Add user message to state
        chatState.messages.push({
            role: 'user',
            content: message,
            timestamp: Date.now()
        });

        // Clear input and adjust height
        input.value = '';
        input.style.height = 'auto';
        sendBtn.disabled = true;

        saveHistory();
        renderMessages();

        // Show typing indicator
        chatState.isLoading = true;
        typingIndicator.style.display = 'flex';
        statusText.textContent = 'Thinking...';
        scrollToBottom();

        // Prepare conversational history payload (last 6 turns, role & content only)
        const historyPayload = chatState.messages.slice(-7, -1).map(function(m) {
            return {
                role: m.role,
                content: m.content
            };
        });

        const csrfToken = document.querySelector('meta[name="csrf-token"]') 
            ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
            : (document.querySelector('input[name="_token"]') ? document.querySelector('input[name="_token"]').value : '');

        fetch('{{ route("ai_chat.send") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                message: message,
                history: historyPayload
            })
        })
        .then(function(res) {
            if (!res.ok) {
                throw new Error('HTTP error ' + res.status);
            }
            return res.json();
        })
        .then(function(data) {
            typingIndicator.style.display = 'none';
            chatState.isLoading = false;
            statusText.textContent = 'Ask about jobs, features, or hiring';

            if (data.status === 'success' && data.reply) {
                chatState.messages.push({
                    role: 'assistant',
                    content: data.reply,
                    jobs: data.jobs || [],
                    timestamp: Date.now()
                });
                saveHistory();
                renderMessages();
            } else {
                showError("I received an unexpected response. Please try asking again.");
            }
        })
        .catch(function(err) {
            console.error('AI Chat Send Error:', err);
            typingIndicator.style.display = 'none';
            chatState.isLoading = false;
            statusText.textContent = 'Ask about jobs, features, or hiring';

            chatState.messages.push({
                role: 'assistant',
                content: "I'm sorry, I encountered a temporary connection issue. You can check out active roles directly on our **[Jobs Board](/jobs)** or try rephrasing your message.",
                timestamp: Date.now()
            });
            saveHistory();
            renderMessages();
        });
    }

    function showError(errText) {
        chatState.messages.push({
            role: 'assistant',
            content: errText,
            timestamp: Date.now()
        });
        saveHistory();
        renderMessages();
    }

    // Clear Conversation
    function clearChat() {
        if (confirm("Clear this conversation history?")) {
            localStorage.removeItem(STORAGE_KEY);
            chatState.messages = [];
            fetchContextAndInit();
        }
    }

    // Input event listeners
    if (input) {
        input.addEventListener('input', function() {
            sendBtn.disabled = this.value.trim().length === 0 || chatState.isLoading;
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (!sendBtn.disabled) {
                    sendMessage();
                }
            }
        });
    }

    if (chatForm) {
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            sendMessage();
        });
    }

    if (launcher) {
        launcher.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleChat();
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleChat(false);
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            clearChat();
        });
    }

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && chatState.isOpen) {
            toggleChat(false);
        }
    });

    // Close on clicking outside panel
    document.addEventListener('click', function(e) {
        if (chatState.isOpen && !panel.contains(e.target) && !launcher.contains(e.target)) {
            toggleChat(false);
        }
    });

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadHistoryOrInit);
    } else {
        loadHistoryOrInit();
    }
})();
</script>

