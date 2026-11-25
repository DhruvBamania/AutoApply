@extends('layouts.app')

@section('content')
<div class="tracker-page-wrapper">
    <!-- Header Section -->
    <div class="card-header" style="border: none; padding-bottom: 0; margin-bottom: 1.5rem;">
        <div>
            <h1>📊 Application Tracker</h1>
            <p class="subtitle">Track and organize job applications captured via the AutoApply AI Chrome Companion & web platform.</p>
        </div>
        <div class="header-action-btns">
            <a href="{{ route('chrome_extension.index') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                <span class="brand-icon-box sm" style="width: 20px; height: 20px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg style="width: 12px; height: 12px; color: white;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                        <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
                    </svg>
                </span>
                <span>Chrome Extension</span>
            </a>
            <a href="{{ route('jobs.index') }}" class="btn btn-primary">
                <span>💼</span> Browse Jobs
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            ✓ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    <!-- Statistics Strip -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-number">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Tracked</div>
        </div>
        <div class="stat-card">
            <div class="stat-number text-amber">{{ $stats['bookmarked'] }}</div>
            <div class="stat-label">Bookmarked</div>
        </div>
        <div class="stat-card">
            <div class="stat-number text-indigo">{{ $stats['applied'] }}</div>
            <div class="stat-label">Applied</div>
        </div>
        <div class="stat-card">
            <div class="stat-number text-purple">{{ $stats['interviewing'] }}</div>
            <div class="stat-label">Interviewing</div>
        </div>
        <div class="stat-card">
            <div class="stat-number text-emerald">{{ $stats['offered'] }}</div>
            <div class="stat-label">Offered</div>
        </div>
        <div class="stat-card">
            <div class="stat-number text-rose">{{ $stats['rejected'] }}</div>
            <div class="stat-label">Rejected</div>
        </div>
    </div>

    <!-- Filter & Search Controls -->
    <div class="glass-card filter-card">
        <!-- Status Tabs -->
        <div class="status-tabs-container">
            @php $currentStatus = request('status', 'all'); @endphp
            <a href="{{ route('candidate.applications.index', array_merge(request()->query(), ['status' => 'all'])) }}" 
               class="status-tab {{ $currentStatus === 'all' ? 'active' : '' }}">
                All ({{ $stats['total'] }})
            </a>
            <a href="{{ route('candidate.applications.index', array_merge(request()->query(), ['status' => 'bookmarked'])) }}" 
               class="status-tab {{ $currentStatus === 'bookmarked' ? 'active' : '' }}">
                Bookmarked ({{ $stats['bookmarked'] }})
            </a>
            <a href="{{ route('candidate.applications.index', array_merge(request()->query(), ['status' => 'applied'])) }}" 
               class="status-tab {{ $currentStatus === 'applied' ? 'active' : '' }}">
                Applied ({{ $stats['applied'] }})
            </a>
            <a href="{{ route('candidate.applications.index', array_merge(request()->query(), ['status' => 'interviewing'])) }}" 
               class="status-tab {{ $currentStatus === 'interviewing' ? 'active' : '' }}">
                Interviewing ({{ $stats['interviewing'] }})
            </a>
            <a href="{{ route('candidate.applications.index', array_merge(request()->query(), ['status' => 'offered'])) }}" 
               class="status-tab {{ $currentStatus === 'offered' ? 'active' : '' }}">
                Offered ({{ $stats['offered'] }})
            </a>
            <a href="{{ route('candidate.applications.index', array_merge(request()->query(), ['status' => 'rejected'])) }}" 
               class="status-tab {{ $currentStatus === 'rejected' ? 'active' : '' }}">
                Rejected ({{ $stats['rejected'] }})
            </a>
        </div>

        <!-- Search & Platform Filter Form -->
        <form method="GET" action="{{ route('candidate.applications.index') }}" class="search-filter-form">
            <input type="hidden" name="status" value="{{ $currentStatus }}">
            <div class="search-input-group">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by job title, company, or location..." class="form-control">
            </div>
            <div class="platform-select-group">
                <select name="platform" class="form-control" onchange="this.form.submit()">
                    <option value="all">All Portals</option>
                    <option value="linkedin" {{ request('platform') === 'linkedin' ? 'selected' : '' }}>LinkedIn</option>
                    <option value="indeed" {{ request('platform') === 'indeed' ? 'selected' : '' }}>Indeed</option>
                    <option value="glassdoor" {{ request('platform') === 'glassdoor' ? 'selected' : '' }}>Glassdoor</option>
                    <option value="internshala" {{ request('platform') === 'internshala' ? 'selected' : '' }}>Internshala</option>
                    <option value="lever" {{ request('platform') === 'lever' ? 'selected' : '' }}>Lever.co</option>
                    <option value="greenhouse" {{ request('platform') === 'greenhouse' ? 'selected' : '' }}>Greenhouse</option>
                    <option value="workday" {{ request('platform') === 'workday' ? 'selected' : '' }}>Workday</option>
                    <option value="universal" {{ request('platform') === 'universal' ? 'selected' : '' }}>Universal Portals</option>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary btn-sm" style="height: 42px;">Filter</button>
            @if(request('search') || (request('platform') && request('platform') !== 'all') || ($currentStatus !== 'all'))
                <a href="{{ route('candidate.applications.index') }}" class="btn btn-sm btn-secondary" style="height: 42px;" title="Reset filters">Reset</a>
            @endif
        </form>
    </div>

    <!-- Applications List -->
    @if($applications->count() > 0)
        <div class="applications-grid">
            @foreach($applications as $app)
                <div class="glass-card app-item-card">
                    <div class="app-card-top">
                        <div class="platform-meta">
                            <span class="platform-tag platform-{{ strtolower($app->platform) }}">
                                {{ ucfirst($app->platform) }}
                            </span>
                            @if($app->match_score)
                                @php
                                    $scoreClass = $app->match_score >= 75 ? 'score-high' : ($app->match_score >= 50 ? 'score-mid' : 'score-low');
                                @endphp
                                <span class="score-pill {{ $scoreClass }}" title="ATS Match Score calculated by Gemini/Groq">
                                    🎯 {{ $app->match_score }}% Match
                                </span>
                            @endif
                        </div>
                        <span class="date-meta">{{ $app->created_at->diffForHumans() }}</span>
                    </div>

                    <div class="app-card-main">
                        <h3 class="app-job-title">{{ $app->job_title }}</h3>
                        <div class="app-company-name">🏢 {{ $app->company_name }}</div>
                        @if($app->location)
                            <div class="app-location">📍 {{ $app->location }}</div>
                        @endif
                        @if($app->salary)
                            <div class="app-salary">💰 {{ $app->salary }}</div>
                        @endif
                    </div>

                    <!-- Status Change Form -->
                    <div class="app-status-bar">
                        <form method="POST" action="{{ route('candidate.applications.status', $app->id) }}" class="status-change-form">
                            @csrf
                            @method('PATCH')
                            <label for="status_{{ $app->id }}" class="status-label">Status:</label>
                            <select name="status" id="status_{{ $app->id }}" class="status-select status-{{ $app->status }}" onchange="this.form.submit()">
                                <option value="bookmarked" {{ $app->status === 'bookmarked' ? 'selected' : '' }}>📌 Bookmarked</option>
                                <option value="applied" {{ $app->status === 'applied' ? 'selected' : '' }}>📤 Applied</option>
                                <option value="interviewing" {{ $app->status === 'interviewing' ? 'selected' : '' }}>💬 Interviewing</option>
                                <option value="offered" {{ $app->status === 'offered' ? 'selected' : '' }}>🎉 Offered</option>
                                <option value="rejected" {{ $app->status === 'rejected' ? 'selected' : '' }}>❌ Rejected</option>
                            </select>
                        </form>
                    </div>

                    <!-- Footer Actions -->
                    <div class="app-card-footer">
                        @if($app->job_url)
                            <a href="{{ $app->job_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-secondary" style="flex: 1;">
                                <span>🔗</span> View Portal Job
                            </a>
                        @endif
                        <form method="POST" action="{{ route('candidate.applications.destroy', $app->id) }}" onsubmit="return confirm('Remove this application from tracker?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-secondary btn-icon-only text-rose" title="Delete application">
                                🗑️
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div style="margin-top: 2rem;">
            {{ $applications->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="glass-card empty-state-card">
            <div class="empty-icon">📁</div>
            <h2>No Applications Found</h2>
            <p class="text-muted">
                @if(request('search') || request('status') || request('platform'))
                    No applications match your active filters. Try resetting the search or filter criteria.
                @else
                    You haven't tracked any job applications yet! Install our AI Chrome Extension to save jobs with 1-click directly from LinkedIn, Indeed, and company career sites.
                @endif
            </p>
            <div class="empty-actions">
                <a href="{{ route('chrome_extension.index') }}" class="btn btn-primary">
                    <span>🧩</span> Get Chrome Extension
                </a>
                <a href="{{ route('jobs.index') }}" class="btn btn-secondary">
                    <span>💼</span> Explore In-House Openings
                </a>
            </div>
        </div>
    @endif
</div>

<style>
.tracker-page-wrapper {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    max-width: 1200px;
    margin: 0 auto;
    width: 100%;
}

.header-action-btns {
    display: flex;
    gap: 0.75rem;
}

/* Stats Row */
.stats-row {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 1rem;
}

@media (max-width: 900px) {
    .stats-row {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 480px) {
    .stats-row {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

.stat-card {
    background: rgba(255, 255, 255, 0.85);
    border: 1px solid rgba(226, 232, 240, 0.9);
    border-radius: 16px;
    padding: 1rem 0.75rem;
    text-align: center;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.2;
}

.stat-label {
    font-size: 0.75rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-top: 0.2rem;
}

.text-amber { color: #d97706; }
.text-indigo { color: #4f46e5; }
.text-purple { color: #7c3aed; }
.text-emerald { color: #059669; }
.text-rose { color: #e11d48; }

/* Filter Card */
.filter-card {
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.status-tabs-container {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 0.75rem;
}

.status-tab {
    padding: 0.4rem 0.85rem;
    border-radius: 10px;
    font-size: 0.82rem;
    font-weight: 600;
    text-decoration: none;
    color: #475569;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    transition: all 0.18s ease;
}

.status-tab:hover {
    background: #f1f5f9;
    color: #4f46e5;
}

.status-tab.active {
    background: #4f46e5;
    color: white;
    border-color: #4f46e5;
    box-shadow: 0 2px 8px rgba(79, 70, 229, 0.3);
}

.search-filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    align-items: center;
}

.search-input-group {
    flex: 1;
    min-width: 240px;
}

.platform-select-group {
    width: 170px;
}

/* Applications Grid */
.applications-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1.25rem;
}

@media (max-width: 1040px) {
    .applications-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 640px) {
    .applications-grid {
        grid-template-columns: 1fr;
    }
}

.app-item-card {
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    justify-content: space-between;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.app-item-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -8px rgba(79, 70, 229, 0.12);
}

.app-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
}

.platform-meta {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    flex-wrap: wrap;
}

.platform-tag {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    padding: 0.15rem 0.5rem;
    border-radius: 6px;
    background: #ede9fe;
    color: #6d28d9;
}

.platform-linkedin { background: #e0f2fe; color: #0369a1; }
.platform-indeed { background: #fef3c7; color: #b45309; }
.platform-lever { background: #fee2e2; color: #b91c1c; }
.platform-greenhouse { background: #dcfce7; color: #15803d; }
.platform-workday { background: #f3e8ff; color: #7e22ce; }

.score-pill {
    font-size: 0.7rem;
    font-weight: 800;
    padding: 0.15rem 0.5rem;
    border-radius: 6px;
}

.score-high { background: #dcfce7; color: #15803d; }
.score-mid { background: #fef3c7; color: #b45309; }
.score-low { background: #fee2e2; color: #b91c1c; }

.date-meta {
    font-size: 0.72rem;
    color: #94a3b8;
    white-space: nowrap;
}

.app-card-main {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.app-job-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.3;
}

.app-company-name {
    font-size: 0.85rem;
    font-weight: 600;
    color: #475569;
}

.app-location, .app-salary {
    font-size: 0.78rem;
    color: #64748b;
}

.app-status-bar {
    background: #f8fafc;
    border-radius: 10px;
    padding: 0.4rem 0.65rem;
    border: 1px solid #f1f5f9;
}

.status-change-form {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.status-label {
    font-size: 0.72rem;
    font-weight: 700;
    color: #64748b;
    margin: 0;
}

.status-select {
    padding: 0.25rem 0.5rem;
    font-size: 0.78rem;
    font-weight: 700;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background: white;
    cursor: pointer;
    flex: 1;
}

.status-bookmarked { color: #d97706; }
.status-applied { color: #4f46e5; }
.status-interviewing { color: #7c3aed; }
.status-offered { color: #059669; }
.status-rejected { color: #e11d48; }

.app-card-footer {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding-top: 0.6rem;
    border-top: 1px solid #f1f5f9;
}

.btn-icon-only {
    width: 36px;
    height: 34px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* Empty State */
.empty-state-card {
    text-align: center;
    padding: 3.5rem 1.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.empty-icon {
    font-size: 3rem;
}

.empty-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    justify-content: center;
    margin-top: 0.5rem;
}
</style>
@endsection

