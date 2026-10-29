@extends('layouts.app')

@section('content')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        --accent-gradient: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
        --surface-glass: rgba(255, 255, 255, 0.75);
        --surface-border: rgba(255, 255, 255, 0.85);
        --text-dark: #0f172a;
        --text-muted: #64748b;
    }

    .prep-hero {
        background: var(--primary-gradient);
        border-radius: 24px;
        padding: clamp(1.4rem, 3vw, 2.2rem) clamp(1.2rem, 3.5vw, 2.5rem);
        color: white;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 12px 35px rgba(79, 70, 229, 0.25);
        max-width: 100%;
        box-sizing: border-box;
    }
    .prep-hero::after {
        content: '';
        position: absolute;
        top: -60px;
        right: -60px;
        width: 220px;
        height: 220px;
        background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, rgba(255,255,255,0) 70%);
        border-radius: 50%;
    }
    .prep-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(8px);
        border-radius: 999px;
        font-size: 0.82rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        margin-bottom: 0.8rem;
    }
    .prep-hero h1 {
        color: white;
        font-size: clamp(1.6rem, 3.2vw, 2.3rem);
        margin-bottom: 0.5rem;
    }
    .prep-hero p {
        color: rgba(255, 255, 255, 0.88);
        font-size: 1.02rem;
        max-width: 680px;
        line-height: 1.5;
        margin: 0;
    }

    .prep-layout {
        display: grid;
        grid-template-columns: minmax(280px, 340px) minmax(0, 1fr);
        gap: 2rem;
        align-items: start;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
    .prep-sidebar, .prep-main {
        min-width: 0;
        max-width: 100%;
    }
    @media (max-width: 1080px) {
        .prep-layout {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        .prep-sidebar-card {
            position: static !important;
            top: auto !important;
        }
    }

    .prep-sidebar-card {
        background: var(--surface-glass);
        backdrop-filter: blur(16px);
        border: 1px solid var(--surface-border);
        border-radius: 20px;
        padding: 1.6rem;
        box-shadow: 0 8px 30px rgba(15, 23, 42, 0.05);
        position: sticky;
        top: 90px;
    }

    .form-label {
        display: block;
        font-weight: 600;
        font-size: 0.88rem;
        color: var(--text-dark);
        margin-bottom: 0.45rem;
    }
    .form-control, .form-select {
        width: 100%;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        border: 1.5px solid #e2e8f0;
        background: white;
        font-size: 0.92rem;
        color: var(--text-dark);
        transition: all 0.2s ease;
        box-sizing: border-box;
    }
    .form-control:focus, .form-select:focus {
        outline: none;
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
    }

    .chip-container {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 6px;
    }
    .quick-chip {
        background: rgba(79, 70, 229, 0.08);
        color: #4338ca;
        border: 1px solid rgba(79, 70, 229, 0.2);
        padding: 3px 9px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .quick-chip:hover {
        background: #4f46e5;
        color: white;
        transform: translateY(-1px);
    }

    .autofill-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        text-decoration: none;
        margin-bottom: 1.2rem;
    }
    .autofill-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(236, 72, 153, 0.35);
        color: white;
    }

    .btn-generate {
        background: var(--primary-gradient);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 0.85rem 1.4rem;
        font-weight: 700;
        font-size: 0.95rem;
        width: 100%;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.35);
        transition: all 0.25s ease;
    }
    .btn-generate:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(79, 70, 229, 0.45);
        color: white;
    }

    /* History Pill List */
    .history-card {
        margin-top: 1.5rem;
        border-top: 1px solid rgba(0,0,0,0.08);
        padding-top: 1.2rem;
    }
    .history-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.65rem 0.8rem;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.6);
        border: 1px solid rgba(0,0,0,0.04);
        margin-bottom: 0.5rem;
        text-decoration: none;
        color: var(--text-dark);
        transition: all 0.2s ease;
    }
    .history-item:hover, .history-item.active {
        background: white;
        border-color: #4f46e5;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.1);
        transform: translateX(3px);
    }
    .history-item.active {
        border-left: 3px solid #4f46e5;
    }

    /* Content Area */
    .prep-content-card {
        background: var(--surface-glass);
        backdrop-filter: blur(16px);
        border: 1px solid var(--surface-border);
        border-radius: 20px;
        padding: 1.8rem;
        box-shadow: 0 8px 30px rgba(15, 23, 42, 0.05);
    }

    .prep-header-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding-bottom: 1.2rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        margin-bottom: 1.5rem;
    }

    /* Topic Tabs */
    .topic-tabs {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 8px;
        margin-bottom: 1.4rem;
        scrollbar-width: thin;
        max-width: 100%;
        -webkit-overflow-scrolling: touch;
    }
    .topic-tab {
        padding: 0.5rem 1rem;
        border-radius: 12px;
        background: white;
        border: 1px solid #e2e8f0;
        color: var(--text-muted);
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .topic-tab:hover {
        border-color: #cbd5e1;
        color: var(--text-dark);
    }
    .topic-tab.active {
        background: #4f46e5;
        border-color: #4f46e5;
        color: white;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
    }

    /* Question Cards */
    .question-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        margin-bottom: 1.2rem;
        overflow: hidden;
        transition: all 0.25s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }
    .question-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
    }
    .question-header {
        padding: 1.2rem 1.4rem;
        cursor: pointer;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        user-select: none;
    }
    .q-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 30px;
        height: 30px;
        border-radius: 8px;
        background: #f1f5f9;
        color: #475569;
        font-weight: 700;
        font-size: 0.85rem;
    }
    .q-title {
        font-size: 1.02rem;
        font-weight: 700;
        color: var(--text-dark);
        line-height: 1.4;
        flex: 1;
        margin: 0;
    }
    .difficulty-badge {
        padding: 3px 9px;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .diff-Easy { background: #dcfce7; color: #15803d; }
    .diff-Medium { background: #fef3c7; color: #b45309; }
    .diff-Hard { background: #fee2e2; color: #b91c1c; }

    .q-actions {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .btn-icon {
        background: transparent;
        border: none;
        padding: 6px;
        border-radius: 8px;
        cursor: pointer;
        color: var(--text-muted);
        transition: all 0.2s ease;
    }
    .btn-icon:hover {
        background: #f1f5f9;
        color: var(--text-dark);
    }
    .btn-icon.bookmarked {
        color: #f59e0b;
    }

    /* Accordion Body */
    .answer-body {
        display: none;
        padding: 0 1.4rem 1.4rem 1.4rem;
        border-top: 1px solid #f1f5f9;
        background: #fafafa;
    }
    .answer-body.open {
        display: block;
    }
    .answer-content {
        padding-top: 1.2rem;
        font-size: 0.95rem;
        color: #334155;
        line-height: 1.65;
    }
    .key-points-box {
        margin-top: 1rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #4f46e5;
        border-radius: 10px;
        padding: 0.9rem 1.2rem;
    }
    .key-points-box h4 {
        margin: 0 0 0.5rem 0;
        font-size: 0.88rem;
        color: #1e1b4b;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .key-points-box ul {
        margin: 0;
        padding-left: 1.2rem;
        font-size: 0.88rem;
        color: #475569;
    }
    .key-points-box li {
        margin-bottom: 0.35rem;
    }

    .code-box {
        margin-top: 1rem;
        background: #0f172a;
        color: #f8fafc;
        border-radius: 12px;
        padding: 1rem 1.2rem;
        font-family: 'Fira Code', 'Courier New', monospace;
        font-size: 0.85rem;
        overflow-x: auto;
        position: relative;
        max-width: 100%;
        white-space: pre-wrap;
        word-break: break-word;
    }
    .copy-code-btn {
        position: absolute;
        top: 8px;
        right: 8px;
        background: rgba(255, 255, 255, 0.15);
        color: white;
        border: none;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.72rem;
        cursor: pointer;
        transition: background 0.2s ease;
    }
    .copy-code-btn:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    /* Print styles */
    @media print {
        .navbar, .prep-hero, .prep-sidebar-card, .topic-tabs, .btn-action, .q-actions {
            display: none !important;
        }
        .prep-layout {
            display: block !important;
        }
        .answer-body {
            display: block !important;
        }
        .question-card {
            border: 1px solid #ccc !important;
            page-break-inside: avoid;
        }
    }
</style>

<div class="prep-hero">
    <div class="prep-badge">
        <span>⚡ AI Interview Prep Studio</span>
    </div>
    <h1>Master Technical Interviews for Any Role</h1>
    <p>Generate targeted, topic-by-topic interview questions with comprehensive model answers, key verbal talking points, and production code scenarios tailored to your experience level.</p>
</div>

<div class="prep-layout">
    <!-- Left Sidebar: Generation Form & Library -->
    <div class="prep-sidebar">
        <div class="prep-sidebar-card">
            <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; color: var(--text-dark); display: flex; align-items: center; justify-content: space-between;">
                <span>🎯 Question Generator</span>
                <span style="font-size: 0.75rem; font-weight: 600; color: #4f46e5; background: rgba(79,70,229,0.1); padding: 3px 8px; border-radius: 6px;">AI Powered</span>
            </h3>

            <!-- 1-Click Autofill Button -->
            <button type="button" class="autofill-btn" id="btn-autofill" onclick="autoFillResume()">
                <span>⚡ Auto-fill from My Resume</span>
            </button>

            <form action="{{ route('interview_prep.generate') }}" method="POST" id="prep-form">
                @csrf

                <!-- Target Role -->
                <div style="margin-bottom: 1.1rem;">
                    <label class="form-label" for="role_title">Target Role / Job Title *</label>
                    <input type="text" name="role_title" id="role_title" class="form-control" placeholder="e.g. Full Stack Laravel & Vue Developer" value="{{ old('role_title', $activePrep->role_title ?? '') }}" required>
                    <div class="chip-container">
                        <span class="quick-chip" onclick="setRole('Full Stack Laravel Developer')">Laravel Dev</span>
                        <span class="quick-chip" onclick="setRole('Flutter Mobile Engineer')">Flutter Dev</span>
                        <span class="quick-chip" onclick="setRole('DevOps & Cloud Engineer')">DevOps</span>
                        <span class="quick-chip" onclick="setRole('Backend Node.js Architect')">Backend</span>
                    </div>
                </div>

                <!-- Experience Level -->
                <div style="margin-bottom: 1.1rem;">
                    <label class="form-label" for="experience_level">Candidate Experience Level *</label>
                    <select name="experience_level" id="experience_level" class="form-select" required>
                        <option value="Fresher / Entry Level (0-1 yrs)" {{ old('experience_level', $activePrep->experience_level ?? '') == 'Fresher / Entry Level (0-1 yrs)' ? 'selected' : '' }}>Fresher / Entry Level (0-1 yrs)</option>
                        <option value="Junior Engineer (1-3 yrs)" {{ old('experience_level', $activePrep->experience_level ?? '') == 'Junior Engineer (1-3 yrs)' ? 'selected' : '' }}>Junior Engineer (1-3 yrs)</option>
                        <option value="Mid-Level Engineer (3-5 yrs)" {{ old('experience_level', $activePrep->experience_level ?? 'Mid-Level Engineer (3-5 yrs)') == 'Mid-Level Engineer (3-5 yrs)' ? 'selected' : '' }}>Mid-Level Engineer (3-5 yrs)</option>
                        <option value="Senior Software Engineer (5-8 yrs)" {{ old('experience_level', $activePrep->experience_level ?? '') == 'Senior Software Engineer (5-8 yrs)' ? 'selected' : '' }}>Senior Software Engineer (5-8 yrs)</option>
                        <option value="Lead / Principal Architect (8+ yrs)" {{ old('experience_level', $activePrep->experience_level ?? '') == 'Lead / Principal Architect (8+ yrs)' ? 'selected' : '' }}>Lead / Principal Architect (8+ yrs)</option>
                    </select>
                </div>

                <!-- Tech Stack & Languages -->
                <div style="margin-bottom: 1.1rem;">
                    <label class="form-label" for="tech_stack">Tech Stack, Languages & Tools *</label>
                    <textarea name="tech_stack" id="tech_stack" rows="3" class="form-control" placeholder="e.g. PHP, Laravel, MySQL, Redis, Docker, REST APIs, Git" required>{{ old('tech_stack', $activePrep->tech_stack ?? '') }}</textarea>
                    <div class="chip-container">
                        <span class="quick-chip" onclick="addTech('Laravel')">+ Laravel</span>
                        <span class="quick-chip" onclick="addTech('PHP')">+ PHP</span>
                        <span class="quick-chip" onclick="addTech('Flutter')">+ Flutter</span>
                        <span class="quick-chip" onclick="addTech('MySQL')">+ MySQL</span>
                        <span class="quick-chip" onclick="addTech('Docker')">+ Docker</span>
                        <span class="quick-chip" onclick="addTech('System Design')">+ System Design</span>
                    </div>
                </div>

                <!-- Use Resume Context Toggle -->
                <div style="margin-bottom: 1.3rem; display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="use_resume" id="use_resume" value="1" checked style="accent-color: #4f46e5; width: 16px; height: 16px;">
                    <label for="use_resume" style="font-size: 0.85rem; color: var(--text-dark); cursor: pointer; user-select: none;">
                        Tailor answers to my uploaded resume projects
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-generate" id="btn-submit-generate">
                    <span id="gen-spinner" style="display: none;">⏳ Generating Questions...</span>
                    <span id="gen-text">🚀 Generate Interview Questions</span>
                </button>
            </form>

            <!-- Saved Preps Library -->
            @if($history->isNotEmpty())
                <div class="history-card">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.6rem;">
                        <span style="font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted);">
                            Saved Guides ({{ $history->count() }})
                        </span>
                    </div>
                    @foreach($history as $item)
                        <div class="history-item {{ ($activePrep && $activePrep->id === $item->id) ? 'active' : '' }}">
                            <a href="{{ route('interview_prep.index', ['prep_id' => $item->id]) }}" style="text-decoration: none; color: inherit; flex: 1; overflow: hidden;">
                                <div style="font-weight: 700; font-size: 0.88rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $item->role_title }}
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                                    {{ $item->total_questions }} Qs &bull; {{ $item->created_at->diffForHumans() }}
                                </div>
                            </a>
                            <form action="{{ route('interview_prep.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Delete this interview preparation guide?')" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-icon" title="Delete Guide" style="color: #ef4444;">
                                    &times;
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Past Mock Interviews Card -->
            @if(isset($mockSessions) && $mockSessions->isNotEmpty())
                <div class="history-card" style="margin-top: 1.25rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.6rem;">
                        <span style="font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted);">
                            🎙️ Past Mock Interviews ({{ $mockSessions->count() }})
                        </span>
                    </div>
                    @foreach($mockSessions as $session)
                        <div class="history-item" style="cursor: pointer;" onclick="loadPastMockInterview({{ $session->id }})">
                            <div style="flex: 1; overflow: hidden;">
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px;">
                                    <span style="font-weight: 700; font-size: 0.86rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $session->role_title }}
                                    </span>
                                    @if($session->evaluation_report && isset($session->evaluation_report['overall_score']))
                                        <span style="font-size: 0.72rem; font-weight: 800; padding: 1px 6px; border-radius: 4px; background: rgba(16, 185, 129, 0.15); color: #059669;">
                                            {{ $session->evaluation_report['overall_score'] }}%
                                        </span>
                                    @else
                                        <span style="font-size: 0.72rem; font-weight: 700; padding: 1px 6px; border-radius: 4px; background: #fef3c7; color: #b45309;">
                                            In Progress
                                        </span>
                                    @endif
                                </div>
                                <div style="font-size: 0.74rem; color: var(--text-muted); margin-top: 2px;">
                                    {{ $session->created_at->diffForHumans() }} &bull; {{ $session->evaluation_report['verdict'] ?? 'Scorecard' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Right Side: Questions & Study Hub -->
    <div class="prep-main">
        <div class="prep-content-card">
            @if($activePrep)
                <!-- Header Details -->
                <div class="prep-header-bar">
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                            <span class="difficulty-badge diff-Medium">{{ $activePrep->experience_level }}</span>
                            <span style="font-size: 0.82rem; color: var(--text-muted);">{{ $activePrep->total_questions }} Questions</span>
                        </div>
                        <h2 style="font-size: 1.45rem; font-weight: 800; color: var(--text-dark); margin: 0;">
                            {{ $activePrep->role_title }}
                        </h2>
                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">
                            <strong>Tech Stack:</strong> {{ $activePrep->tech_stack }}
                        </div>
                    </div>

                    <!-- Toolbar Actions -->
                    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                        <button type="button" class="btn-secondary btn-sm" onclick="toggleAllAnswers(true)">
                            📖 Expand All
                        </button>
                        <button type="button" class="btn-secondary btn-sm" onclick="toggleAllAnswers(false)">
                            📕 Collapse All
                        </button>
                        <button type="button" class="btn-secondary btn-sm" onclick="copyAllQuestions()">
                            📋 Copy All
                        </button>
                        <button type="button" class="btn-primary btn-sm" onclick="window.print()">
                            🖨️ Print / PDF
                        </button>
                    </div>
                </div>

                <!-- Live AI Mock Interview Simulator Launch Banner -->
                <div class="mock-simulator-banner" style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.08) 0%, rgba(124, 58, 237, 0.08) 100%); border: 1.5px solid rgba(99, 102, 241, 0.3); border-radius: 16px; padding: 1.25rem 1.4rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: 1.25rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.35); flex-shrink: 0;">
                            🎙️
                        </div>
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #4f46e5; background: rgba(79, 70, 229, 0.12); padding: 2px 8px; border-radius: 999px;">
                                    Interactive Simulator
                                </span>
                                <span style="font-size: 0.75rem; font-weight: 700; color: #dc2626;">
                                    ● 5-Question Strict Mode
                                </span>
                            </div>
                            <h4 style="margin: 3px 0 2px 0; font-size: 1.05rem; font-weight: 800; color: var(--text-dark);">
                                Live AI Mock Interviewer
                            </h4>
                            <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted); line-height: 1.4;">
                                Experience a realistic technical interview. Answer by voice or text. Get scored on architecture, problem solving, and behavioral STAR criteria.
                            </p>
                        </div>
                    </div>
                    <div>
                        <button type="button" 
                                onclick="launchMockInterview('{{ addslashes($activePrep->role_title) }}', '{{ addslashes($activePrep->experience_level) }}', '{{ addslashes($activePrep->tech_stack) }}', {{ $activePrep->id }})" 
                                class="btn-primary" 
                                style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 0.65rem 1.4rem; font-size: 0.9rem; font-weight: 700; border-radius: 12px; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.35); display: inline-flex; align-items: center; gap: 8px; cursor: pointer; white-space: nowrap;">
                            <span>🚀 Launch AI Mock Interview</span>
                        </button>
                    </div>
                </div>

                <!-- Topic Filtering Tabs -->
                <div class="topic-tabs" id="topic-tab-bar">
                    <button type="button" class="topic-tab active" onclick="filterTopic('all', this)">
                        All Questions ({{ $activePrep->total_questions }})
                    </button>
                    <button type="button" class="topic-tab" onclick="filterTopic('bookmarked', this)">
                        ⭐ Bookmarked (<span id="bookmark-count">{{ count($activePrep->bookmarked_ids ?? []) }}</span>)
                    </button>
                    @foreach($activePrep->topics_data ?? [] as $topic)
                        <button type="button" class="topic-tab" onclick="filterTopic('{{ Str::slug($topic['topic_name']) }}', this)">
                            {{ $topic['topic_name'] }} ({{ count($topic['questions'] ?? []) }})
                        </button>
                    @endforeach
                </div>

                <!-- Question List -->
                <div id="questions-list">
                    @php $qIndex = 1; @endphp
                    @foreach($activePrep->topics_data ?? [] as $topic)
                        @foreach($topic['questions'] ?? [] as $q)
                            @php
                                $qId = $q['id'] ?? ('q_' . $qIndex);
                                $isBookmarked = $activePrep->isQuestionBookmarked($qId);
                                $topicSlug = Str::slug($topic['topic_name']);
                            @endphp
                            <div class="question-card" data-topic="{{ $topicSlug }}" data-bookmarked="{{ $isBookmarked ? 'true' : 'false' }}" id="card-{{ $qId }}">
                                <div class="question-header" onclick="toggleAnswer('{{ $qId }}')">
                                    <span class="q-number">#{{ $qIndex }}</span>
                                    <div style="flex: 1;">
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                            <span style="font-size: 0.75rem; font-weight: 700; color: #4f46e5; background: #eef2ff; padding: 2px 8px; border-radius: 6px;">
                                                {{ $topic['topic_name'] }}
                                            </span>
                                            <span class="difficulty-badge diff-{{ $q['difficulty'] ?? 'Medium' }}">
                                                {{ $q['difficulty'] ?? 'Medium' }}
                                            </span>
                                        </div>
                                        <h3 class="q-title">{{ $q['question'] }}</h3>
                                    </div>
                                    <div class="q-actions" onclick="event.stopPropagation();">
                                        <button type="button" class="btn-icon {{ $isBookmarked ? 'bookmarked' : '' }}" id="star-{{ $qId }}" title="Bookmark this question" onclick="toggleBookmark('{{ $activePrep->id }}', '{{ $qId }}')">
                                            <span style="font-size: 1.15rem;">{{ $isBookmarked ? '★' : '☆' }}</span>
                                        </button>
                                        <button type="button" class="btn-icon" onclick="toggleAnswer('{{ $qId }}')">
                                            <span id="chevron-{{ $qId }}" style="font-size: 0.9rem;">▼</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Accordion Answer Body -->
                                <div class="answer-body" id="body-{{ $qId }}">
                                    <div class="answer-content">
                                        <div style="font-weight: 700; color: #1e1b4b; margin-bottom: 0.4rem; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                            💡 Model Answer
                                        </div>
                                        <p style="margin: 0; white-space: pre-line;">{{ $q['answer'] }}</p>

                                        <!-- Key Talking Points -->
                                        @if(!empty($q['key_points']) && is_array($q['key_points']))
                                            <div class="key-points-box">
                                                <h4>🎯 Verbal Talking Points (What Interviewers Want to Hear):</h4>
                                                <ul>
                                                    @foreach($q['key_points'] as $point)
                                                        <li>{{ $point }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        <!-- Code Snippet -->
                                        @if(!empty($q['code_snippet']))
                                            <div class="code-box">
                                                <button type="button" class="copy-code-btn" onclick="copySnippet(this)">Copy</button>
                                                <pre style="margin: 0; font-family: inherit;"><code>{{ $q['code_snippet'] }}</code></pre>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @php $qIndex++; @endphp
                        @endforeach
                    @endforeach
                </div>
            @else
                <!-- Empty State -->
                <div style="text-align: center; padding: 3.5rem 1.5rem;">
                    <div style="font-size: 3.5rem; margin-bottom: 1rem;">🎯</div>
                    <h3 style="font-size: 1.4rem; font-weight: 800; color: var(--text-dark); margin-bottom: 0.6rem;">
                        No Interview Questions Generated Yet
                    </h3>
                    <p style="color: var(--text-muted); max-width: 480px; margin: 0 auto 1.5rem auto; font-size: 0.95rem; line-height: 1.5;">
                        Fill in your target role and tech stack on the left or tap <strong>Auto-fill from My Resume</strong> to generate customized, high-yield interview questions instantly.
                    </p>
                    <div style="margin-top: 1.5rem;">
                        <button type="button" 
                                onclick="launchMockInterview()" 
                                class="btn-primary" 
                                style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 0.75rem 1.6rem; font-size: 0.95rem; font-weight: 700; border-radius: 12px; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.35); display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                            <span>🎙️ Launch Live AI Mock Interview</span>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    // Form submission animation
    document.getElementById('prep-form')?.addEventListener('submit', function() {
        const btn = document.getElementById('btn-submit-generate');
        const spinner = document.getElementById('gen-spinner');
        const text = document.getElementById('gen-text');
        btn.disabled = true;
        btn.style.opacity = '0.75';
        spinner.style.display = 'inline';
        text.style.display = 'none';
    });

    // Auto-fill from resume profile via AJAX
    function autoFillResume() {
        const btn = document.getElementById('btn-autofill');
        btn.innerHTML = '<span>⏳ Reading Resume...</span>';
        
        fetch('{{ route("interview_prep.profile") }}')
            .then(res => res.json())
            .then(data => {
                btn.innerHTML = '<span>⚡ Auto-fill from My Resume</span>';
                if (data.success && data.profile) {
                    const p = data.profile;
                    if (p.role_title) document.getElementById('role_title').value = p.role_title;
                    if (p.tech_stack) document.getElementById('tech_stack').value = p.tech_stack;
                    if (p.experience_level) document.getElementById('experience_level').value = p.experience_level;
                    
                    alert('Profile autofilled from your resume! Tap "Generate Interview Questions" to start.');
                } else {
                    alert('Could not find parsed resume data. Please enter role and skills manually.');
                }
            })
            .catch(() => {
                btn.innerHTML = '<span>⚡ Auto-fill from My Resume</span>';
                alert('Error fetching resume profile.');
            });
    }

    // Role quick-chip helper
    function setRole(title) {
        document.getElementById('role_title').value = title;
    }

    // Tech stack quick-chip helper
    function addTech(tech) {
        const input = document.getElementById('tech_stack');
        let current = input.value.trim();
        if (!current) {
            input.value = tech;
        } else {
            const list = current.split(',').map(s => s.trim());
            if (!list.includes(tech)) {
                input.value = current + ', ' + tech;
            }
        }
    }

    // Toggle Answer accordion
    function toggleAnswer(id) {
        const body = document.getElementById('body-' + id);
        const chevron = document.getElementById('chevron-' + id);
        if (body.classList.contains('open')) {
            body.classList.remove('open');
            chevron.textContent = '▼';
        } else {
            body.classList.add('open');
            chevron.textContent = '▲';
        }
    }

    // Expand / Collapse all answers
    function toggleAllAnswers(expand) {
        document.querySelectorAll('.answer-body').forEach(body => {
            const id = body.id.replace('body-', '');
            const chevron = document.getElementById('chevron-' + id);
            if (expand) {
                body.classList.add('open');
                if (chevron) chevron.textContent = '▲';
            } else {
                body.classList.remove('open');
                if (chevron) chevron.textContent = '▼';
            }
        });
    }

    // Topic filtering
    function filterTopic(topicSlug, btn) {
        document.querySelectorAll('.topic-tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');

        const cards = document.querySelectorAll('.question-card');
        cards.forEach(card => {
            if (topicSlug === 'all') {
                card.style.display = 'block';
            } else if (topicSlug === 'bookmarked') {
                card.style.display = (card.dataset.bookmarked === 'true') ? 'block' : 'none';
            } else {
                card.style.display = (card.dataset.topic === topicSlug) ? 'block' : 'none';
            }
        });
    }

    // Toggle Bookmark via AJAX
    function toggleBookmark(prepId, questionId) {
        const token = '{{ csrf_token() }}';
        const card = document.getElementById('card-' + questionId);
        const starBtn = document.getElementById('star-' + questionId);

        fetch(`/interview-prep/${prepId}/bookmark`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
            },
            body: JSON.stringify({ question_id: questionId }),
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const isBookmarked = data.is_bookmarked;
                card.dataset.bookmarked = isBookmarked ? 'true' : 'false';
                starBtn.classList.toggle('bookmarked', isBookmarked);
                starBtn.innerHTML = `<span style="font-size: 1.15rem;">${isBookmarked ? '★' : '☆'}</span>`;
                
                const countBadge = document.getElementById('bookmark-count');
                if (countBadge && data.bookmarked_ids) {
                    countBadge.textContent = data.bookmarked_ids.length;
                }
            }
        });
    }

    // Copy code snippet helper
    function copySnippet(btn) {
        const code = btn.nextElementSibling.querySelector('code').innerText;
        navigator.clipboard.writeText(code).then(() => {
            const original = btn.innerText;
            btn.innerText = 'Copied!';
            setTimeout(() => btn.innerText = original, 2000);
        });
    }

    // Copy all questions text
    function copyAllQuestions() {
        const titles = [];
        document.querySelectorAll('.q-title').forEach((el, idx) => {
            titles.push((idx + 1) + '. ' + el.innerText.trim());
        });
        navigator.clipboard.writeText(titles.join('\n\n')).then(() => {
            alert('All ' + titles.length + ' interview questions copied to clipboard!');
        });
    }
</script>

{{-- Interactive Turn-by-Turn AI Mock Interview Simulator Modal --}}
@include('interview_prep.partials.mock_interview_modal')
@endsection
