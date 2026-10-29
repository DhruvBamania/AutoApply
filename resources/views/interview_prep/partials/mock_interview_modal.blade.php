{{-- Interactive Turn-by-Turn AI Mock Interview Simulator Modal --}}
<div id="mockInterviewModal" class="mock-modal-overlay" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="mockModalTitle">
    <div class="mock-modal-container">
        <!-- Top Status Bar & Header -->
        <div class="mock-modal-header">
            <div class="mock-header-left">
                <div class="mock-avatar-box">
                    <span class="mock-avatar-icon">🤖</span>
                    <span class="mock-online-pulse" title="AI Hiring Manager Active"></span>
                </div>
                <div>
                    <div class="mock-badge-row">
                        <span class="mock-persona-badge">AI Tech Lead & Hiring Manager</span>
                        <span class="mock-mode-badge">Realistic Strict Mode</span>
                    </div>
                    <h3 id="mockModalTitle" class="mock-role-title">Technical Interview</h3>
                </div>
            </div>
            <div class="mock-header-right">
                <div class="mock-progress-container">
                    <div class="mock-progress-label">
                        <span id="mockStepLabel">Question 1 of 5</span>
                        <span id="mockTimerLabel">⏱️ 00:00</span>
                    </div>
                    <div class="mock-progress-track">
                        <div id="mockProgressBar" class="mock-progress-fill" style="width: 20%;"></div>
                    </div>
                </div>
                <button type="button" class="mock-close-btn" onclick="confirmCloseMockInterview()" title="Exit Interview" aria-label="Exit Interview">
                    &times;
                </button>
            </div>
        </div>

        <!-- Chat Stream (Questions & Answers) -->
        <div id="mockChatStream" class="mock-chat-stream">
            <!-- Messages rendered dynamically here -->
            <div id="mockMessagesContainer" class="mock-messages-container"></div>

            <!-- Typing / Evaluating Wave -->
            <div id="mockTypingWave" class="mock-typing-wave" style="display: none;">
                <div class="mock-typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <span id="mockTypingText" class="mock-typing-text">Interviewer is evaluating your response...</span>
            </div>

            <!-- Comprehensive Final Evaluation Scorecard (Hidden until complete) -->
            <div id="mockEvaluationReport" class="mock-evaluation-report" style="display: none;">
                <!-- Rendered dynamically on completion -->
            </div>
        </div>

        <!-- Candidate Input Controls (Hidden when interview completes) -->
        <div id="mockInputSection" class="mock-input-section">
            <div class="mock-speech-status" id="mockSpeechStatus" style="display: none;">
                <span class="mock-recording-dot"></span>
                <span id="mockSpeechStatusText">Listening to your voice... Speak clearly</span>
                <button type="button" class="mock-speech-stop-btn" onclick="stopSpeechToText()">Done Speaking</button>
            </div>

            <form id="mockAnswerForm" onsubmit="handleMockAnswerSubmit(event)">
                <div class="mock-input-wrapper">
                    <textarea id="mockAnswerInput" 
                              rows="3" 
                              placeholder="Type your technical answer thoroughly, or click the microphone to speak..." 
                              maxlength="4000"
                              required></textarea>
                    
                    <div class="mock-action-toolbar">
                        <button type="button" 
                                id="mockMicBtn" 
                                class="mock-mic-btn" 
                                onclick="toggleSpeechToText()" 
                                title="Click to speak your answer (Voice-to-Text)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                                <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"></path>
                                <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                                <line x1="12" y1="19" x2="12" y2="22"></line>
                            </svg>
                            <span id="mockMicText">Voice Input</span>
                        </button>

                        <button type="submit" id="mockSubmitBtn" class="mock-submit-btn">
                            <span>Submit Answer</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                            </svg>
                        </button>
                    </div>
                </div>
            </form>
            <div class="mock-input-hint">
                <span>💡 <strong>Strict Evaluation Tip:</strong> Provide concrete architectural examples, trade-offs, and metrics. Questions advance upon submission.</span>
            </div>
        </div>
    </div>
</div>

<style>
/* ==========================================================================
   Interactive AI Mock Interviewer Simulator Styles
   ========================================================================== */
.mock-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 1050;
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(6px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: clamp(0.75rem, 2vw, 1.5rem);
    animation: mockFadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes mockFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.mock-modal-container {
    background: #0f172a;
    color: #f8fafc;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 24px;
    width: 100%;
    max-width: 920px;
    height: 88vh;
    max-height: 840px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(99, 102, 241, 0.2);
    animation: mockSlideUp 0.24s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes mockSlideUp {
    from { transform: translateY(20px) scale(0.98); opacity: 0; }
    to { transform: translateY(0) scale(1); opacity: 1; }
}

/* Header */
.mock-modal-header {
    background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding: 1.1rem 1.6rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.mock-header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.mock-avatar-box {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
    position: relative;
    flex-shrink: 0;
}

.mock-online-pulse {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #10b981;
    border: 2px solid #0f172a;
    box-shadow: 0 0 8px #10b981;
}

.mock-badge-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 2px;
}

.mock-persona-badge {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #a5b4fc;
}

.mock-mode-badge {
    font-size: 0.72rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 999px;
    background: rgba(239, 68, 68, 0.18);
    color: #fca5a5;
    border: 1px solid rgba(239, 68, 68, 0.35);
}

.mock-role-title {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 700;
    color: #ffffff;
}

.mock-header-right {
    display: flex;
    align-items: center;
    gap: 1.25rem;
}

.mock-progress-container {
    min-width: 170px;
}

.mock-progress-label {
    display: flex;
    justify-content: space-between;
    font-size: 0.78rem;
    font-weight: 600;
    color: #cbd5e1;
    margin-bottom: 6px;
}

.mock-progress-track {
    width: 100%;
    height: 6px;
    background: rgba(255, 255, 255, 0.12);
    border-radius: 999px;
    overflow: hidden;
}

.mock-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #4f46e5, #06b6d4);
    border-radius: 999px;
    transition: width 0.35s ease;
}

.mock-close-btn {
    background: transparent;
    border: none;
    color: #94a3b8;
    font-size: 1.8rem;
    line-height: 1;
    cursor: pointer;
    border-radius: 8px;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.15s, background 0.15s;
}

.mock-close-btn:hover {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.1);
}

/* Chat Stream */
.mock-chat-stream {
    flex: 1;
    overflow-y: auto;
    padding: 1.8rem;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    scroll-behavior: smooth;
    background: #0b1120;
}

.mock-chat-stream::-webkit-scrollbar {
    width: 6px;
}
.mock-chat-stream::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.16);
    border-radius: 10px;
}

.mock-messages-container {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.mock-msg {
    display: flex;
    gap: 12px;
    max-width: 90%;
    animation: mockMsgIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes mockMsgIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}

.mock-msg.interviewer {
    align-self: flex-start;
}

.mock-msg.candidate {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.mock-msg-bubble {
    padding: 1rem 1.25rem;
    border-radius: 18px;
    font-size: 0.95rem;
    line-height: 1.6;
    word-break: break-word;
}

.mock-msg.interviewer .mock-msg-bubble {
    background: #1e293b;
    color: #e2e8f0;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-bottom-left-radius: 4px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.mock-msg.interviewer .mock-msg-bubble strong {
    color: #ffffff;
    font-weight: 700;
}

.mock-msg.candidate .mock-msg-bubble {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    color: #ffffff;
    border-bottom-right-radius: 4px;
    box-shadow: 0 4px 15px rgba(79, 70, 229, 0.35);
}

/* Typing wave */
.mock-typing-wave {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #1e293b;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 14px;
    padding: 10px 16px;
    width: fit-content;
    align-self: flex-start;
}

.mock-typing-dots {
    display: flex;
    gap: 4px;
}

.mock-typing-dots span {
    width: 6px;
    height: 6px;
    background: #818cf8;
    border-radius: 50%;
    animation: mockBounce 1.2s infinite ease-in-out;
}

.mock-typing-dots span:nth-child(2) { animation-delay: 0.18s; }
.mock-typing-dots span:nth-child(3) { animation-delay: 0.36s; }

@keyframes mockBounce {
    0%, 80%, 100% { transform: translateY(0); opacity: 0.4; }
    40% { transform: translateY(-5px); opacity: 1; }
}

.mock-typing-text {
    font-size: 0.85rem;
    color: #94a3b8;
}

/* Input Section */
.mock-input-section {
    background: #111827;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding: 1.1rem 1.6rem;
}

.mock-speech-status {
    background: rgba(239, 68, 68, 0.15);
    border: 1px solid rgba(239, 68, 68, 0.35);
    color: #fca5a5;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 0.65rem;
}

.mock-recording-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #ef4444;
    animation: mockPulse 1s infinite;
}

@keyframes mockPulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.3); opacity: 0.5; }
    100% { transform: scale(1); opacity: 1; }
}

.mock-speech-stop-btn {
    margin-left: auto;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    cursor: pointer;
}

.mock-input-wrapper {
    display: flex;
    flex-direction: column;
    gap: 8px;
    background: #1f2937;
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 16px;
    padding: 10px 12px;
    transition: border-color 0.15s, box-shadow 0.15s;
}

.mock-input-wrapper:focus-within {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
}

#mockAnswerInput {
    width: 100%;
    background: transparent;
    border: none;
    outline: none;
    color: #ffffff;
    font-size: 0.95rem;
    line-height: 1.5;
    resize: none;
    font-family: inherit;
    box-sizing: border-box;
}

#mockAnswerInput::placeholder {
    color: #64748b;
}

.mock-action-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    padding-top: 8px;
}

.mock-mic-btn {
    background: rgba(255, 255, 255, 0.08);
    color: #cbd5e1;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 10px;
    padding: 6px 12px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.15s ease;
}

.mock-mic-btn:hover {
    background: rgba(255, 255, 255, 0.16);
    color: #ffffff;
}

.mock-mic-btn.is-recording {
    background: #ef4444 !important;
    border-color: #dc2626 !important;
    color: #ffffff !important;
    box-shadow: 0 0 12px rgba(239, 68, 68, 0.5);
    animation: mockMicThrob 1.2s infinite;
}

@keyframes mockMicThrob {
    0% { transform: scale(1); }
    50% { transform: scale(1.04); }
    100% { transform: scale(1); }
}

.mock-submit-btn {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: #ffffff;
    border: none;
    border-radius: 10px;
    padding: 8px 18px;
    font-size: 0.9rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: transform 0.15s, box-shadow 0.15s, opacity 0.15s;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);
}

.mock-submit-btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(79, 70, 229, 0.45);
}

.mock-submit-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.mock-input-hint {
    margin-top: 8px;
    font-size: 0.78rem;
    color: #94a3b8;
}

/* ==========================================================================
   Comprehensive Final Scorecard & Evaluation Report Styles
   ========================================================================== */
.mock-scorecard-card {
    background: #111827;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 20px;
    padding: 1.8rem;
    margin-top: 1rem;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4);
}

.mock-score-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    flex-wrap: wrap;
    padding-bottom: 1.4rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.mock-score-circle {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: conic-gradient(#10b981 var(--score-pct), rgba(255, 255, 255, 0.1) 0);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 20px rgba(16, 185, 129, 0.25);
    flex-shrink: 0;
}

.mock-score-inner {
    width: 82px;
    height: 82px;
    border-radius: 50%;
    background: #0f172a;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.mock-score-number {
    font-size: 1.7rem;
    font-weight: 900;
    color: #ffffff;
    line-height: 1;
}

.mock-score-unit {
    font-size: 0.65rem;
    text-transform: uppercase;
    font-weight: 700;
    color: #94a3b8;
}

.mock-verdict-pill {
    display: inline-block;
    padding: 4px 14px;
    border-radius: 999px;
    font-weight: 800;
    font-size: 0.88rem;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-bottom: 6px;
}

.mock-verdict-pill.strong_hire,
.mock-verdict-pill.hire {
    background: rgba(16, 185, 129, 0.2);
    color: #34d399;
    border: 1px solid rgba(16, 185, 129, 0.35);
}

.mock-verdict-pill.lean_hire {
    background: rgba(245, 158, 11, 0.2);
    color: #fbbf24;
    border: 1px solid rgba(245, 158, 11, 0.35);
}

.mock-verdict-pill.needs_prep {
    background: rgba(239, 68, 68, 0.2);
    color: #f87171;
    border: 1px solid rgba(239, 68, 68, 0.35);
}

.mock-summary-text {
    font-size: 0.95rem;
    color: #cbd5e1;
    line-height: 1.6;
    margin: 1.2rem 0;
}

.mock-columns-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}

@media (max-width: 768px) {
    .mock-columns-grid {
        grid-template-columns: 1fr;
    }
}

.mock-col-box {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 14px;
    padding: 1.1rem;
}

.mock-col-box.strengths {
    border-left: 3px solid #10b981;
}

.mock-col-box.growth {
    border-left: 3px solid #f59e0b;
}

.mock-col-title {
    font-size: 0.88rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 6px;
    margin: 0 0 0.75rem 0;
}

.mock-col-box ul {
    margin: 0;
    padding-left: 1.2rem;
    font-size: 0.88rem;
    color: #94a3b8;
    line-height: 1.5;
}

.mock-col-box li {
    margin-bottom: 0.45rem;
}

/* Question Breakdown */
.mock-breakdown-title {
    font-size: 1rem;
    font-weight: 700;
    color: #ffffff;
    margin: 1.5rem 0 0.8rem 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.mock-q-item {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 12px;
    margin-bottom: 0.75rem;
    padding: 1rem;
}

.mock-q-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 0.4rem;
}

.mock-q-number {
    font-size: 0.82rem;
    font-weight: 700;
    color: #818cf8;
}

.mock-q-score {
    font-size: 0.82rem;
    font-weight: 800;
    color: #34d399;
    background: rgba(16, 185, 129, 0.15);
    padding: 2px 8px;
    border-radius: 6px;
}

.mock-q-feedback-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
    margin-top: 0.6rem;
    font-size: 0.82rem;
}

@media (max-width: 640px) {
    .mock-q-feedback-grid {
        grid-template-columns: 1fr;
    }
}

.mock-q-good {
    color: #86efac;
}

.mock-q-missing {
    color: #fde047;
}

.mock-scorecard-actions {
    display: flex;
    gap: 12px;
    align-items: center;
    justify-content: flex-end;
    margin-top: 1.5rem;
    padding-top: 1.25rem;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
}
</style>

<script>
/**
 * AI Turn-by-Turn Mock Interview Simulator Client
 * Includes Web Speech API (Voice-to-Text) & Strict 5-Question Evaluator
 */
let mockInterviewState = {
    sessionId: null,
    roleTitle: '',
    experienceLevel: '',
    techStack: '',
    currentQuestion: 1,
    totalQuestions: 5,
    status: 'in_progress',
    startTime: null,
    timerInterval: null,
    isRecording: false,
    speechRecognition: null,
    isEvaluating: false
};

// Initialize SpeechRecognition if supported by browser
(function initSpeechRecognition() {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (SpeechRecognition) {
        mockInterviewState.speechRecognition = new SpeechRecognition();
        mockInterviewState.speechRecognition.continuous = true;
        mockInterviewState.speechRecognition.interimResults = true;
        mockInterviewState.speechRecognition.lang = 'en-US';

        mockInterviewState.speechRecognition.onresult = function(event) {
            let interimTranscript = '';
            let finalTranscript = '';

            for (let i = event.resultIndex; i < event.results.length; ++i) {
                if (event.results[i].isFinal) {
                    finalTranscript += event.results[i][0].transcript;
                } else {
                    interimTranscript += event.results[i][0].transcript;
                }
            }

            const input = document.getElementById('mockAnswerInput');
            if (input && finalTranscript) {
                // Append text with spacing
                input.value = (input.value ? input.value.trim() + ' ' : '') + finalTranscript.trim();
                input.dispatchEvent(new Event('input'));
            }
        };

        mockInterviewState.speechRecognition.onerror = function(event) {
            console.warn('Speech recognition error:', event.error);
            stopSpeechToText();
        };

        mockInterviewState.speechRecognition.onend = function() {
            if (mockInterviewState.isRecording) {
                // If stopped unexpectedly while state is recording, reset button
                stopSpeechToText();
            }
        };
    }
})();

// Toggle Speech Recognition
function toggleSpeechToText() {
    if (!mockInterviewState.speechRecognition) {
        alert("Speech-to-text is not supported in this browser. Please use Google Chrome, Microsoft Edge, or Safari, or type your answer directly.");
        return;
    }

    if (mockInterviewState.isRecording) {
        stopSpeechToText();
    } else {
        startSpeechToText();
    }
}

function startSpeechToText() {
    try {
        mockInterviewState.speechRecognition.start();
        mockInterviewState.isRecording = true;
        const micBtn = document.getElementById('mockMicBtn');
        const micText = document.getElementById('mockMicText');
        const speechStatus = document.getElementById('mockSpeechStatus');

        if (micBtn) micBtn.classList.add('is-recording');
        if (micText) micText.textContent = 'Listening...';
        if (speechStatus) speechStatus.style.display = 'flex';
    } catch (e) {
        console.error('Speech recognition start failed', e);
    }
}

function stopSpeechToText() {
    if (mockInterviewState.speechRecognition && mockInterviewState.isRecording) {
        try {
            mockInterviewState.speechRecognition.stop();
        } catch (e) {}
    }
    mockInterviewState.isRecording = false;
    const micBtn = document.getElementById('mockMicBtn');
    const micText = document.getElementById('mockMicText');
    const speechStatus = document.getElementById('mockSpeechStatus');

    if (micBtn) micBtn.classList.remove('is-recording');
    if (micText) micText.textContent = 'Voice Input';
    if (speechStatus) speechStatus.style.display = 'none';
}

// Timer helper
function startMockTimer() {
    mockInterviewState.startTime = Date.now();
    if (mockInterviewState.timerInterval) clearInterval(mockInterviewState.timerInterval);

    mockInterviewState.timerInterval = setInterval(function() {
        const elapsedSec = Math.floor((Date.now() - mockInterviewState.startTime) / 1000);
        const mins = String(Math.floor(elapsedSec / 60)).padStart(2, '0');
        const secs = String(elapsedSec % 60).padStart(2, '0');
        const timerLabel = document.getElementById('mockTimerLabel');
        if (timerLabel) timerLabel.textContent = `⏱️ ${mins}:${secs}`;
    }, 1000);
}

function stopMockTimer() {
    if (mockInterviewState.timerInterval) {
        clearInterval(mockInterviewState.timerInterval);
        mockInterviewState.timerInterval = null;
    }
}

function getMockCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
        || document.querySelector('input[name="_token"]')?.value 
        || '{{ csrf_token() }}';
}

// Launch Interview Simulator
function launchMockInterview(roleTitle, experienceLevel, techStack, prepId) {
    // If not supplied, pick up from form or prompt
    const role = roleTitle || document.getElementById('role_title')?.value || 'Full Stack Software Engineer';
    const exp = experienceLevel || document.getElementById('experience_level')?.value || 'Mid-Level Engineer (3-5 yrs)';
    const tech = techStack || document.getElementById('tech_stack')?.value || 'PHP, Laravel, MySQL, REST APIs';

    mockInterviewState.roleTitle = role;
    mockInterviewState.experienceLevel = exp;
    mockInterviewState.techStack = tech;

    // Reset UI
    document.getElementById('mockModalTitle').textContent = role;
    document.getElementById('mockStepLabel').textContent = 'Question 1 of 5';
    document.getElementById('mockProgressBar').style.width = '20%';
    document.getElementById('mockMessagesContainer').innerHTML = '';
    document.getElementById('mockEvaluationReport').style.display = 'none';
    document.getElementById('mockInputSection').style.display = 'block';
    document.getElementById('mockAnswerInput').value = '';
    document.getElementById('mockSubmitBtn').disabled = false;

    // Open Modal
    const modal = document.getElementById('mockInterviewModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Show initial loading wave
    showTypingWave('AI Hiring Manager is reviewing your target role and preparing Question 1...');

    const csrfToken = getMockCsrfToken();

    // POST /interview-prep/mock/start
    fetch('{{ route("mock_interview.start") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            _token: csrfToken,
            role_title: role,
            experience_level: exp,
            tech_stack: tech,
            interview_preparation_id: prepId || null
        })
    })
    .then(async res => {
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            throw new Error(data.message || `Request failed with status ${res.status}`);
        }
        return data;
    })
    .then(data => {
        hideTypingWave();
        if (data.status === 'success' && data.session) {
            mockInterviewState.sessionId = data.session.id;
            mockInterviewState.currentQuestion = data.session.current_question_index;
            mockInterviewState.totalQuestions = data.session.total_questions;
            mockInterviewState.status = data.session.status;

            startMockTimer();

            // Render existing conversation messages
            if (data.session.conversation && data.session.conversation.length > 0) {
                data.session.conversation.forEach(msg => appendMessage(msg.role, msg.content));
            } else if (data.session.latest_question) {
                appendMessage('interviewer', data.session.latest_question);
            }

            document.getElementById('mockAnswerInput').focus();
        } else {
            alert(data.message || 'Unable to launch mock interview. Please try again.');
            closeMockInterviewModal();
        }
    })
    .catch(err => {
        hideTypingWave();
        console.error('Start interview error:', err);
        alert('Connection error launching simulator. Please retry.');
        closeMockInterviewModal();
    });
}

// Append message to UI
function appendMessage(role, content) {
    const container = document.getElementById('mockMessagesContainer');
    if (!container) return;

    const msgEl = document.createElement('div');
    msgEl.className = 'mock-msg ' + role;

    const bubble = document.createElement('div');
    bubble.className = 'mock-msg-bubble';

    if (role === 'interviewer') {
        bubble.innerHTML = formatMarkdown(content);
    } else {
        bubble.textContent = content;
    }

    msgEl.appendChild(bubble);
    container.appendChild(msgEl);

    // Scroll stream to bottom
    const stream = document.getElementById('mockChatStream');
    if (stream) stream.scrollTop = stream.scrollHeight;
}

// Simple markdown formatter
function formatMarkdown(text) {
    if (!text) return '';
    let escaped = text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

    // Bold
    escaped = escaped.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
    // Italic
    escaped = escaped.replace(/\*([^*]+)\*/g, '<em>$1</em>');
    // Inline code
    escaped = escaped.replace(/`([^`]+)`/g, '<code style="background:rgba(255,255,255,0.1);padding:2px 5px;border-radius:4px;font-family:monospace;font-size:0.85em;">$1</code>');
    // Line breaks
    escaped = escaped.replace(/\n\n/g, '<p style="margin:0 0 10px 0;"></p>');
    escaped = escaped.replace(/\n/g, '<br>');

    return escaped;
}

function showTypingWave(text) {
    const wave = document.getElementById('mockTypingWave');
    const label = document.getElementById('mockTypingText');
    if (label && text) label.textContent = text;
    if (wave) wave.style.display = 'flex';

    const stream = document.getElementById('mockChatStream');
    if (stream) stream.scrollTop = stream.scrollHeight;
}

function hideTypingWave() {
    const wave = document.getElementById('mockTypingWave');
    if (wave) wave.style.display = 'none';
}

// Handle Candidate Answer Submission
function handleMockAnswerSubmit(e) {
    if (e) e.preventDefault();
    if (mockInterviewState.isEvaluating) return;

    const input = document.getElementById('mockAnswerInput');
    const answer = input ? input.value.trim() : '';

    if (!answer) {
        alert("Please provide an answer to the interviewer's question before submitting.");
        return;
    }

    stopSpeechToText();

    // Append candidate answer to stream
    appendMessage('candidate', answer);

    input.value = '';
    mockInterviewState.isEvaluating = true;
    document.getElementById('mockSubmitBtn').disabled = true;

    // Show evaluating message
    const isLastQ = mockInterviewState.currentQuestion >= mockInterviewState.totalQuestions;
    showTypingWave(isLastQ 
        ? 'Compiling your final evaluation report and performance scorecard...' 
        : 'Interviewer is evaluating your response...');

    const csrfToken = getMockCsrfToken();

    fetch(`/interview-prep/mock/${mockInterviewState.sessionId}/answer`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ 
            _token: csrfToken,
            answer: answer 
        })
    })
    .then(async res => {
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            throw new Error(data.message || `Request failed with status ${res.status}`);
        }
        return data;
    })
    .then(data => {
        hideTypingWave();
        mockInterviewState.isEvaluating = false;
        document.getElementById('mockSubmitBtn').disabled = false;

        if (data.status === 'success') {
            const turn = data.turn;

            if (turn.is_final) {
                // Interview Completed!
                renderFinalScorecard(turn.report);
            } else {
                // Next Question!
                mockInterviewState.currentQuestion = turn.question_number;
                document.getElementById('mockStepLabel').textContent = `Question ${turn.question_number} of ${turn.total_questions}`;
                const pct = (turn.question_number / turn.total_questions) * 100;
                document.getElementById('mockProgressBar').style.width = pct + '%';

                appendMessage('interviewer', turn.interviewer_message);
                document.getElementById('mockAnswerInput').focus();
            }
        } else {
            alert(data.message || 'An error occurred submitting your answer.');
        }
    })
    .catch(err => {
        hideTypingWave();
        mockInterviewState.isEvaluating = false;
        document.getElementById('mockSubmitBtn').disabled = false;
        console.error('Answer submit error:', err);
        alert('Connection error submitting answer. Please retry.');
    });
}

// Render Final Scorecard Report
function renderFinalScorecard(report) {
    stopMockTimer();

    // Hide input section
    document.getElementById('mockInputSection').style.display = 'none';

    // Update progress bar to 100%
    document.getElementById('mockProgressBar').style.width = '100%';
    document.getElementById('mockStepLabel').textContent = 'Interview Completed 🎉';

    const reportEl = document.getElementById('mockEvaluationReport');
    if (!reportEl || !report) return;

    const score = report.overall_score || 75;
    const verdict = report.verdict || 'Hire';
    let verdictClass = 'hire';
    if (verdict.toLowerCase().includes('strong')) verdictClass = 'strong_hire';
    else if (verdict.toLowerCase().includes('lean')) verdictClass = 'lean_hire';
    else if (verdict.toLowerCase().includes('prep') || verdict.toLowerCase().includes('needs')) verdictClass = 'needs_prep';

    let strengthsHtml = '';
    (report.strengths || []).forEach(s => {
        strengthsHtml += `<li>${escapeHtml(s)}</li>`;
    });

    let growthHtml = '';
    (report.growth_areas || []).forEach(g => {
        growthHtml += `<li>${escapeHtml(g)}</li>`;
    });

    let breakdownHtml = '';
    (report.question_reviews || []).forEach(q => {
        breakdownHtml += `
            <div class="mock-q-item">
                <div class="mock-q-head">
                    <span class="mock-q-number">Question ${q.question_number}: ${escapeHtml(q.topic || 'Technical Deep Dive')}</span>
                    <span class="mock-q-score">${q.score || 8} / 10</span>
                </div>
                <div class="mock-q-feedback-grid">
                    <div>
                        <strong class="mock-q-good">✓ What Went Well:</strong>
                        <p style="margin:2px 0 0 0;color:#cbd5e1;">${escapeHtml(q.strengths || 'Structured reasoning')}</p>
                    </div>
                    <div>
                        <strong class="mock-q-missing">⚠ Areas to Improve:</strong>
                        <p style="margin:2px 0 0 0;color:#cbd5e1;">${escapeHtml(q.missing || 'Could provide more production metrics')}</p>
                    </div>
                </div>
            </div>
        `;
    });

    reportEl.innerHTML = `
        <div class="mock-scorecard-card">
            <div class="mock-score-header">
                <div style="display:flex;align-items:center;gap:1.25rem;">
                    <div class="mock-score-circle" style="--score-pct: ${score}%;">
                        <div class="mock-score-inner">
                            <span class="mock-score-number">${score}</span>
                            <span class="mock-score-unit">Score</span>
                        </div>
                    </div>
                    <div>
                        <span class="mock-verdict-pill ${verdictClass}">${escapeHtml(verdict)}</span>
                        <h4 style="margin:0;font-size:1.15rem;color:#ffffff;">Technical Readiness Scorecard</h4>
                        <span style="font-size:0.82rem;color:#94a3b8;">Role: ${escapeHtml(mockInterviewState.roleTitle)} &bull; ${escapeHtml(report.readiness_level || mockInterviewState.experienceLevel)}</span>
                    </div>
                </div>

                <div>
                    <button type="button" class="btn-secondary" onclick="window.print()" style="font-size:0.82rem;padding:0.4rem 0.9rem;">
                        🖨️ Print Scorecard
                    </button>
                </div>
            </div>

            <div class="mock-summary-text">
                ${formatMarkdown(report.summary || '')}
            </div>

            <div class="mock-columns-grid">
                <div class="mock-col-box strengths">
                    <h5 class="mock-col-title" style="color:#34d399;">
                        <span>✓</span> Key Candidate Strengths
                    </h5>
                    <ul>${strengthsHtml || '<li>Solid technical explanations</li>'}</ul>
                </div>

                <div class="mock-col-box growth">
                    <h5 class="mock-col-title" style="color:#fbbf24;">
                        <span>⚠</span> Areas for Technical Growth
                    </h5>
                    <ul>${growthHtml || '<li>Elaborate on production scalability</li>'}</ul>
                </div>
            </div>

            <h5 class="mock-breakdown-title">
                <span>📋</span> Question-by-Question Evaluation Breakdown
            </h5>
            <div>
                ${breakdownHtml}
            </div>

            <div class="mock-scorecard-actions">
                <button type="button" class="btn-secondary" onclick="closeMockInterviewModal()" style="padding:0.55rem 1.1rem;font-size:0.88rem;">
                    Close Simulator
                </button>
                <button type="button" class="btn-primary" onclick="launchMockInterview(mockInterviewState.roleTitle, mockInterviewState.experienceLevel, mockInterviewState.techStack)" style="padding:0.55rem 1.3rem;font-size:0.88rem;background:linear-gradient(135deg,#4f46e5,#7c3aed);">
                    🔄 Retake Interview
                </button>
            </div>
        </div>
    `;

    reportEl.style.display = 'block';

    const stream = document.getElementById('mockChatStream');
    if (stream) stream.scrollTop = stream.scrollHeight;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close and exit
function confirmCloseMockInterview() {
    if (mockInterviewState.status === 'in_progress') {
        if (!confirm("Are you sure you want to exit this interview session? Your progress so far is saved in your account.")) {
            return;
        }
    }
    closeMockInterviewModal();
}

function closeMockInterviewModal() {
    stopMockTimer();
    stopSpeechToText();
    const modal = document.getElementById('mockInterviewModal');
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
}

// Load and display a past mock interview session
function loadPastMockInterview(sessionId) {
    const modal = document.getElementById('mockInterviewModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Reset UI
    document.getElementById('mockMessagesContainer').innerHTML = '';
    document.getElementById('mockEvaluationReport').style.display = 'none';
    showTypingWave('Loading interview session...');

    fetch(`/interview-prep/mock/${sessionId}`)
        .then(res => res.json())
        .then(data => {
            hideTypingWave();
            if (data.status === 'success' && data.session) {
                const s = data.session;
                mockInterviewState.sessionId = s.id;
                mockInterviewState.roleTitle = s.role_title;
                mockInterviewState.experienceLevel = s.experience_level;
                mockInterviewState.techStack = s.tech_stack;
                mockInterviewState.currentQuestion = s.current_question_index;
                mockInterviewState.totalQuestions = s.total_questions;
                mockInterviewState.status = s.status;

                document.getElementById('mockModalTitle').textContent = s.role_title;

                if (s.conversation && s.conversation.length > 0) {
                    s.conversation.forEach(msg => appendMessage(msg.role, msg.content));
                }

                if (s.status === 'completed' && s.evaluation_report) {
                    renderFinalScorecard(s.evaluation_report);
                } else {
                    document.getElementById('mockInputSection').style.display = 'block';
                    document.getElementById('mockStepLabel').textContent = `Question ${s.current_question_index} of ${s.total_questions}`;
                    const pct = (s.current_question_index / s.total_questions) * 100;
                    document.getElementById('mockProgressBar').style.width = pct + '%';
                    startMockTimer();
                    document.getElementById('mockAnswerInput').focus();
                }
            } else {
                alert('Unable to load mock interview.');
                closeMockInterviewModal();
            }
        })
        .catch(err => {
            hideTypingWave();
            console.error('Error loading past interview:', err);
            alert('Failed to load interview session.');
            closeMockInterviewModal();
        });
}

// Keyboard shortcuts inside textarea: Shift+Enter for newline, Enter to submit
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('mockAnswerInput');
    if (textarea) {
        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                handleMockAnswerSubmit();
            }
        });
    }
});
</script>
