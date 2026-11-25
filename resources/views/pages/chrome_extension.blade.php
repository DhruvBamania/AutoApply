@extends('layouts.app')

@section('content')
<div class="extension-page-wrapper">
    <!-- Hero Section -->
    <div class="glass-card extension-hero-card">
        <div class="brand-icon-box" style="width: 60px; height: 60px; border-radius: 18px; margin-bottom: 1.25rem; box-shadow: 0 10px 25px rgba(99, 102, 241, 0.45); display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #ec4899 100%);">
            <svg style="width: 32px; height: 32px; color: white;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
            </svg>
        </div>

        <div class="hero-badge-pill">
            <span class="sparkle-icon">✨</span>
            <span>MANIFEST V3 EXTENSION</span>
            <span class="hero-version-pill">v1.0.0</span>
        </div>

        <h1 class="hero-title">AutoApply AI Chrome Companion</h1>
        <p class="hero-subtitle">
            1-Click ATS Job Matching, AI Screening Question Answers, and Form AutoFill across 
            <strong>LinkedIn, Indeed, Glassdoor, Lever, Greenhouse, Workday & Internshala</strong>.
        </p>

        <div class="hero-actions-group">
            <a href="{{ route('chrome_extension.download') }}" class="btn btn-primary btn-hero-download" id="downloadZipBtn">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.3">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Download Extension (.ZIP)</span>
            </a>
            @auth
                <a href="{{ route('candidate.applications.index') }}" class="btn btn-secondary">
                    <span>📊</span> View Application Tracker
                </a>
            @else
                <a href="{{ route('register') }}" class="btn btn-secondary">
                    <span>🚀</span> Create Candidate Account
                </a>
            @endauth
        </div>

        <div class="supported-portals-strip">
            <span class="strip-label">Supported Portals:</span>
            <span class="portal-chip">LinkedIn</span>
            <span class="portal-chip">Indeed</span>
            <span class="portal-chip">Glassdoor</span>
            <span class="portal-chip">Internshala</span>
            <span class="portal-chip">Lever.co</span>
            <span class="portal-chip">Greenhouse</span>
            <span class="portal-chip">Workday</span>
            <span class="portal-chip">+ Universal Portals</span>
        </div>
    </div>

    <!-- Features Grid -->
    <div class="features-grid">
        <div class="glass-card feature-card">
            <div class="feature-icon-box gradient-indigo">🎯</div>
            <h3>Real-Time ATS Match Scoring</h3>
            <p>Our autonomous AI engine parses the live Job Description directly on your browser tab and scores your resume alignment in seconds, revealing missing keywords and recommended resume additions.</p>
        </div>

        <div class="glass-card feature-card">
            <div class="feature-icon-box gradient-emerald">⚡</div>
            <h3>1-Click Portal Form AutoFill</h3>
            <p>Tired of typing your contact info, portfolio URLs, and previous company history into repetitive application forms? AutoFill populates all standard inputs with synthetic reactive events for Vue & React compatibility.</p>
        </div>

        <div class="glass-card feature-card">
            <div class="feature-icon-box gradient-purple">✨</div>
            <h3>AI Screening Question Assistant</h3>
            <p>Get instant, tailored answers for tricky open-ended questions like <em>"Why do you want to work here?"</em> or <em>"Describe your experience with tech stack X"</em> inserted straight into the active form field.</p>
        </div>

        <div class="glass-card feature-card">
            <div class="feature-icon-box gradient-amber">📊</div>
            <h3>Multi-Platform Application Tracker</h3>
            <p>Save external jobs with one click directly into your unified AutoApply dashboard. Track statuses from <em>Bookmarked</em> to <em>Applied</em>, <em>Interviewing</em>, and <em>Offered</em> in one centralized place.</p>
        </div>

        <div class="glass-card feature-card">
            <div class="feature-icon-box" style="background: linear-gradient(135deg, #4f46e5 0%, #ec4899 100%); color: white;">⚡</div>
            <h3>Persistent Web Session Sync</h3>
            <p>No need to log in repeatedly across tabs! Your active candidate session automatically syncs in the background, keeping you connected seamlessly whether you open a new tab or restart Chrome.</p>
        </div>
    </div>

    <!-- Step-by-Step Installation Guide -->
    <div class="glass-card guide-card">
        <div class="card-header" style="border: none; margin-bottom: 1.5rem;">
            <div class="guide-header-text">
                <h2>How to Install in Chrome, Edge, or Brave</h2>
                <p class="text-muted">Follow these 5 simple steps to load the unpacked extension in developer mode (takes less than 60 seconds).</p>
            </div>
            <a href="{{ route('chrome_extension.download') }}" class="btn btn-sm btn-primary">
                Download .ZIP
            </a>
        </div>

        <div class="steps-flow">
            <!-- Step 1 -->
            <div class="step-card">
                <div class="step-number">1</div>
                <div class="step-details">
                    <h4>Download & Unzip Archive</h4>
                    <p>Click the <strong>Download Extension (.ZIP)</strong> button above. Once downloaded, extract or unzip <code>autoapply-ai-companion.zip</code> to a folder on your computer.</p>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="step-card">
                <div class="step-number">2</div>
                <div class="step-details">
                    <h4>Open Extensions Management</h4>
                    <p>Open a new browser tab and navigate to:
                        <code class="browser-url-pill">chrome://extensions</code> (or <code class="browser-url-pill">edge://extensions</code> on Microsoft Edge / Brave).
                    </p>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="step-card">
                <div class="step-number">3</div>
                <div class="step-details">
                    <h4>Enable Developer Mode</h4>
                    <p>In the top-right corner of the Extensions page, toggle the <strong>Developer mode</strong> switch to <strong>ON</strong>.</p>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="step-card">
                <div class="step-number">4</div>
                <div class="step-details">
                    <h4>Click "Load Unpacked"</h4>
                    <p>Click the <strong>Load unpacked</strong> button in the top-left toolbar, then select the extracted <code>chrome_extension</code> directory.</p>
                </div>
            </div>

            <!-- Step 5 -->
            <div class="step-card">
                <div class="step-number">5</div>
                <div class="step-details">
                    <h4>Sign In & Start Hunting!</h4>
                    <p>Pin the <strong>AutoApply</strong> icon to your browser toolbar. Click the icon, log in with your candidate email & password, and navigate to any job on LinkedIn or Indeed to experience real-time AI assistance!</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Troubleshooting & Privacy Card -->
    <div class="glass-card" style="margin-top: 2rem;">
        <h3 style="margin-bottom: 0.75rem;">🔒 Privacy & Permissions</h3>
        <p class="text-muted" style="font-size: 0.92rem; line-height: 1.6;">
            AutoApply AI Companion operates strictly on job description and application form pages. It does not track private browsing history or access any personal data outside of candidate application forms you explicitly ask it to autofill. All communication with our AI backend is secured with authenticated Sanctum Bearer tokens.
        </p>
    </div>
</div>

<style>
.extension-page-wrapper {
    display: flex;
    flex-direction: column;
    gap: 2rem;
    max-width: 1100px;
    margin: 0 auto;
    width: 100%;
}

.extension-hero-card {
    text-align: center;
    padding: 3rem 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.85) 0%, rgba(240, 230, 255, 0.7) 100%);
    border: 1px solid rgba(168, 85, 247, 0.25);
    box-shadow: 0 20px 40px -15px rgba(99, 102, 241, 0.15);
}

.hero-badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.35rem 0.9rem;
    background: rgba(99, 102, 241, 0.1);
    border: 1px solid rgba(99, 102, 241, 0.25);
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 700;
    color: #4f46e5;
    margin-bottom: 1.25rem;
    letter-spacing: 0.05em;
}

.hero-version-pill {
    background: #4f46e5;
    color: white;
    padding: 0.1rem 0.45rem;
    border-radius: 6px;
    font-size: 0.68rem;
}

.hero-title {
    font-size: clamp(1.8rem, 4vw, 2.8rem);
    font-weight: 800;
    color: #0f172a;
    line-height: 1.2;
    margin-bottom: 1rem;
    letter-spacing: -0.02em;
}

.hero-subtitle {
    font-size: clamp(1rem, 2vw, 1.2rem);
    color: #475569;
    max-width: 780px;
    line-height: 1.6;
    margin-bottom: 2rem;
}

.hero-actions-group {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    justify-content: center;
    margin-bottom: 2rem;
}

.btn-hero-download {
    padding: 0.75rem 1.75rem;
    font-size: 1.05rem;
    border-radius: 14px;
    box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
}

.supported-portals-strip {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(226, 232, 240, 0.8);
    width: 100%;
}

.strip-label {
    font-size: 0.8rem;
    font-weight: 700;
    color: #64748b;
    margin-right: 0.25rem;
}

.portal-chip {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.25rem 0.65rem;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    color: #334155;
}

/* Features Grid */
.features-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1.5rem;
}

@media (max-width: 768px) {
    .features-grid {
        grid-template-columns: 1fr;
    }
}

.feature-card {
    padding: 1.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.feature-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px -10px rgba(79, 70, 229, 0.12);
}

.feature-icon-box {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    color: white;
    margin-bottom: 0.25rem;
}

.gradient-indigo { background: linear-gradient(135deg, #4f46e5, #6366f1); box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35); }
.gradient-emerald { background: linear-gradient(135deg, #059669, #10b981); box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35); }
.gradient-purple { background: linear-gradient(135deg, #7c3aed, #a855f7); box-shadow: 0 4px 14px rgba(168, 85, 247, 0.35); }
.gradient-amber { background: linear-gradient(135deg, #d97706, #f59e0b); box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35); }

.feature-card h3 {
    font-size: 1.15rem;
    font-weight: 700;
    color: #0f172a;
}

.feature-card p {
    font-size: 0.92rem;
    color: #64748b;
    line-height: 1.55;
}

/* Steps Flow */
.guide-card {
    padding: 2.25rem;
}

.steps-flow {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.step-card {
    display: flex;
    align-items: flex-start;
    gap: 1.25rem;
    padding: 1.25rem;
    background: rgba(255, 255, 255, 0.7);
    border: 1px solid rgba(226, 232, 240, 0.9);
    border-radius: 16px;
    transition: background 0.2s ease;
}

.step-card:hover {
    background: rgba(255, 255, 255, 0.95);
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: white;
    font-size: 1.15rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.step-details h4 {
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0.35rem;
}

.step-details p {
    font-size: 0.9rem;
    color: #64748b;
    line-height: 1.5;
}

.browser-url-pill {
    background: #ede9fe;
    color: #5b21b6;
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
    font-family: monospace;
    font-size: 0.85rem;
    font-weight: 600;
}
</style>
@endsection

