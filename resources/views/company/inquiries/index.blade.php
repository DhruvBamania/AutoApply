@extends('layouts.app')

@section('content')
<div style="max-width: 1100px; width: 100%; margin: 0 auto; display: flex; flex-direction: column; gap: 2rem;">

    <!-- Header Banner -->
    <div class="card" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; background: linear-gradient(135deg, rgba(255,255,255,0.7) 0%, rgba(238,242,255,0.85) 100%); border-left: 6px solid #4c51bf;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span style="font-size: 2rem;">💬</span>
                <h1 style="font-size: 1.8rem; font-weight: 700; color: #1e1b4b; margin: 0;">Job Comments & Candidate Inquiries</h1>
            </div>
            <p style="color: #4b5563; font-size: 0.95rem; margin-top: 0.35rem; margin-bottom: 0;">
                All comments, queries, and applications from <strong>LinkedIn, Naukri, Indeed</strong> and direct submissions organized <strong>job role wise</strong>.
            </p>
        </div>
        
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <button type="button" onclick="document.getElementById('companyWhatsappModal').style.display='flex'" class="btn" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.2rem; font-size: 0.9rem; border: 1.5px solid #25D366; color: #15803d; background: #f0fdf4; font-weight: 700; cursor: pointer; border-radius: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.772.82 2.791.82 3.181 0 5.767-2.586 5.768-5.766 0-3.18-2.586-5.766-5.768-5.766zm9.969 5.828c0 5.514-4.486 10-10 10-1.802 0-3.486-.481-4.945-1.321l-5.055 1.321 1.353-4.939c-.896-1.492-1.353-3.21-1.353-5.061 0-5.514 4.486-10 10-10s10 4.486 10 10z"/></svg>
                <span>WhatsApp: {{ Auth::user()->companyProfile->whatsapp_number ?? 'Not Set' }}</span>
            </button>

            <button type="button" onclick="toggleManualLogForm()" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.2rem; font-size: 0.9rem; border: 1px solid #0a66c2; color: #0a66c2; background: white; font-weight: 600; cursor: pointer; border-radius: 8px;">
                <span>➕</span> Log LinkedIn Comment
            </button>

            <form action="{{ route('company.inquiries.seed') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="btn" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.2rem; font-size: 0.9rem; border: 1px dashed #6366f1; background: #eef2ff; color: #4338ca; font-weight: 600; cursor: pointer; border-radius: 8px;">
                    <span>🧪</span> Load Sample Queries
                </button>
            </form>

            <form action="{{ route('company.inquiries.sync') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.4rem; font-size: 0.95rem;">
                    <span>🔄</span> Sync All from Platforms
                </button>
            </form>
        </div>
    </div>

    @if(session('linkedin_sync_notice'))
        <div style="background: #fffbeb; border: 1px solid #fde68a; border-left: 5px solid #d97706; padding: 1rem 1.25rem; border-radius: 10px; display: flex; align-items: flex-start; gap: 0.75rem;">
            <span style="font-size: 1.4rem;">⚠️</span>
            <div>
                <strong style="color: #92400e; font-size: 0.95rem;">LinkedIn API Permission Notice:</strong>
                <p style="color: #78350f; font-size: 0.88rem; margin: 0.25rem 0 0 0; line-height: 1.45;">
                    {{ session('linkedin_sync_notice') }}
                </p>
            </div>
        </div>
    @endif

    <!-- Manual Log Comment / Query Card (Collapsible) -->
    <div id="manual-comment-card" class="card" style="display: none; background: white; border: 2px solid #0a66c2; border-radius: 12px; padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid #f3f4f6; padding-bottom: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 1.3rem;">💼</span>
                <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #1e1b4b;">Log Comment or External Inquiry</h3>
            </div>
            <button type="button" onclick="toggleManualLogForm()" style="background: transparent; border: none; font-size: 1.2rem; cursor: pointer; color: #6b7280;">✕</button>
        </div>
        <p style="font-size: 0.85rem; color: #6b7280; margin-top: -0.5rem; margin-bottom: 1rem;">
            Quickly import a comment or message from LinkedIn, Naukri, or Indeed to organize, track, and reply to it role-wise.
        </p>

        <form action="{{ route('company.inquiries.manual') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 220px), 1fr)); gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-size: 0.85rem;">Job Role *</label>
                    <select name="job_posting_id" class="form-control" required>
                        @foreach($allJobs as $job)
                            <option value="{{ $job->id }}">{{ $job->title }} ({{ $job->location ?? 'Remote' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-size: 0.85rem;">Source Platform *</label>
                    <select name="platform" class="form-control" required>
                        <option value="linkedin">💼 LinkedIn Post Comment</option>
                        <option value="whatsapp">📱 WhatsApp Candidate Message</option>
                        <option value="naukri">🔵 Naukri Recruiter Message</option>
                        <option value="indeed">🔷 Indeed Candidate Query</option>
                        <option value="direct">✉️ Direct Candidate Message</option>
                    </select>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-size: 0.85rem;">Commenter / Candidate Name *</label>
                    <input type="text" name="candidate_name" class="form-control" placeholder="e.g. Dhruv Bamania" required>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-size: 0.85rem;">Candidate Phone / WhatsApp (Optional)</label>
                    <input type="tel" name="candidate_phone" class="form-control" placeholder="+91 98765 43210">
                </div>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-size: 0.85rem;">Candidate Headline / Title (Optional)</label>
                    <input type="text" name="candidate_headline" class="form-control" placeholder="e.g. PHP Developer">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" style="font-size: 0.85rem;">Comment / Inquiry Message *</label>
                <textarea name="message" class="form-control" rows="3" placeholder="Paste the comment or question here..." required></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; flex-wrap: wrap;">
                <button type="button" onclick="toggleManualLogForm()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary" style="background: #0a66c2;">✓ Save & Log Comment</button>
            </div>
        </form>
    </div>

    <!-- Quick Stats -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 200px), 1fr)); gap: 1rem;">
        <div class="card" style="background: white; padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #e0e7ff; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                📬
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #1e1b4b;">{{ $totalInquiries }}</div>
                <div style="font-size: 0.8rem; color: #6b7280; font-weight: 600;">Total Queries & Comments</div>
            </div>
        </div>

        <div class="card" style="background: white; padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #fef3c7; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                🔔
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #b45309;">{{ $newInquiriesCount }}</div>
                <div style="font-size: 0.8rem; color: #6b7280; font-weight: 600;">New / Awaiting Review</div>
            </div>
        </div>

        <div class="card" style="background: white; padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #dcfce7; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                ✅
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #15803d;">{{ $repliedCount }}</div>
                <div style="font-size: 0.8rem; color: #6b7280; font-weight: 600;">Answered / Replied</div>
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="card" style="background: white; padding: 1.25rem;">
        <form method="GET" action="{{ route('company.inquiries.index') }}" style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end;">
            
            <!-- Job Role Filter -->
            <div style="flex: 1 1 220px;">
                <label class="form-label" style="font-size: 0.8rem; margin-bottom: 0.25rem;">Filter by Job Role</label>
                <select name="job_id" class="form-control" onchange="this.form.submit()" style="font-size: 0.85rem;">
                    <option value="">All Job Roles ({{ $allJobs->count() }})</option>
                    @foreach($allJobs as $job)
                        <option value="{{ $job->id }}" {{ $selectedJobId == $job->id ? 'selected' : '' }}>
                            {{ $job->title }} ({{ $job->inquiries->count() }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Platform Filter -->
            <div style="flex: 1 1 150px;">
                <label class="form-label" style="font-size: 0.8rem; margin-bottom: 0.25rem;">Platform</label>
                <select name="platform" class="form-control" onchange="this.form.submit()" style="font-size: 0.85rem;">
                    <option value="">All Platforms</option>
                    <option value="linkedin" {{ $selectedPlatform === 'linkedin' ? 'selected' : '' }}>💼 LinkedIn</option>
                    <option value="naukri" {{ $selectedPlatform === 'naukri' ? 'selected' : '' }}>🔵 Naukri</option>
                    <option value="indeed" {{ $selectedPlatform === 'indeed' ? 'selected' : '' }}>🔷 Indeed</option>
                    <option value="direct" {{ $selectedPlatform === 'direct' ? 'selected' : '' }}>🌐 Direct Application</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div style="flex: 1 1 150px;">
                <label class="form-label" style="font-size: 0.8rem; margin-bottom: 0.25rem;">Status</label>
                <select name="status" class="form-control" onchange="this.form.submit()" style="font-size: 0.85rem;">
                    <option value="">All Statuses</option>
                    <option value="new" {{ $selectedStatus === 'new' ? 'selected' : '' }}>🟡 New</option>
                    <option value="under_review" {{ $selectedStatus === 'under_review' ? 'selected' : '' }}>🔵 Under Review</option>
                    <option value="replied" {{ $selectedStatus === 'replied' ? 'selected' : '' }}>🟢 Replied</option>
                    <option value="shortlisted" {{ $selectedStatus === 'shortlisted' ? 'selected' : '' }}>⭐ Shortlisted</option>
                    <option value="rejected" {{ $selectedStatus === 'rejected' ? 'selected' : '' }}>❌ Rejected</option>
                </select>
            </div>

            <!-- Type Filter -->
            <div style="flex: 1 1 140px;">
                <label class="form-label" style="font-size: 0.8rem; margin-bottom: 0.25rem;">Type</label>
                <select name="type" class="form-control" onchange="this.form.submit()" style="font-size: 0.85rem;">
                    <option value="">All Types</option>
                    <option value="comment" {{ $selectedType === 'comment' ? 'selected' : '' }}>💬 Comment</option>
                    <option value="query" {{ $selectedType === 'query' ? 'selected' : '' }}>❓ Query</option>
                    <option value="application" {{ $selectedType === 'application' ? 'selected' : '' }}>📄 Application</option>
                </select>
            </div>

            @if($selectedJobId || $selectedPlatform || $selectedStatus || $selectedType)
                <div>
                    <a href="{{ route('company.inquiries.index') }}" class="btn-secondary" style="font-size: 0.85rem; padding: 0.55rem 1rem;">
                        Reset Filters
                    </a>
                </div>
            @endif
        </form>
    </div>

    <!-- Job Role Wise Inquiries Display -->
    @if($allJobs->isEmpty())
        <div class="card" style="text-align: center; padding: 3rem 1rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📢</div>
            <h3 style="font-size: 1.2rem; font-weight: 700; color: #1f2937;">No Job Postings Yet</h3>
            <p style="color: #6b7280; font-size: 0.9rem; max-width: 450px; margin: 0.5rem auto 1.5rem auto;">
                Once you post a job opening to LinkedIn, Naukri, or Indeed, all candidate comments and questions will appear here organized role by role.
            </p>
            <a href="{{ route('company.jobs.create') }}" class="btn-primary" style="padding: 0.75rem 1.5rem;">
                ➕ Post a Job Role
            </a>
        </div>
    @else
        @php
            $displayJobs = $selectedJobId 
                ? $allJobs->where('id', $selectedJobId) 
                : $allJobs;
        @endphp

        <div style="display: flex; flex-direction: column; gap: 2rem;">
            @foreach($displayJobs as $jobRole)
                @php
                    $roleInquiries = $groupedInquiries->get($jobRole->id, collect());
                @endphp

                <div class="card" style="padding: 0; overflow: hidden; border: 1px solid rgba(229,231,235,0.9); box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
                    
                    <!-- Role Header -->
                    <div style="background: linear-gradient(135deg, rgba(248,250,252,0.9) 0%, rgba(241,245,249,0.9) 100%); padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <span style="font-size: 1.3rem;">💼</span>
                                <h2 style="font-size: 1.25rem; font-weight: 700; color: #1e1b4b; margin: 0;">
                                    <a href="{{ route('company.jobs.show', $jobRole->id) }}" style="color: inherit; text-decoration: none;">
                                        {{ $jobRole->title }}
                                    </a>
                                </h2>
                                <span class="badge" style="background: #e0e7ff; color: #3730a3; font-size: 0.75rem;">
                                    {{ $jobRole->job_type }}
                                </span>
                            </div>

                            <div style="display: flex; align-items: center; gap: 1rem; font-size: 0.85rem; color: #64748b; margin-top: 0.35rem; flex-wrap: wrap;">
                                @if($jobRole->location) <span>📍 {{ $jobRole->location }}</span> @endif
                                <span>📅 Posted {{ $jobRole->created_at->diffForHumans() }}</span>
                                <span style="color: #4338ca; font-weight: 600;">
                                    • {{ $roleInquiries->count() }} inquiry / comment(s)
                                </span>
                            </div>
                        </div>

                        <!-- Role Actions -->
                        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                            <a href="{{ route('jobs.public.show', $jobRole->id) }}" target="_blank" class="btn-secondary" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;" title="View public candidate application and query page">
                                🔗 Public Link
                            </a>

                            <!-- Sync for this specific role -->
                            <form action="{{ route('company.inquiries.sync') }}" method="POST" style="margin: 0;">
                                @csrf
                                <input type="hidden" name="job_id" value="{{ $jobRole->id }}">
                                <button type="submit" class="btn-secondary" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;">
                                    🔄 Sync Role
                                </button>
                            </form>

                            <a href="{{ route('company.jobs.show', $jobRole->id) }}" class="btn-primary" style="font-size: 0.8rem; padding: 0.4rem 0.9rem;">
                                Job Details →
                            </a>
                        </div>
                    </div>

                    <!-- Role Inquiries List -->
                    <div style="padding: 1.5rem;">
                        @if($roleInquiries->isEmpty())
                            <div style="text-align: center; padding: 2rem 1rem; background: #f8fafc; border-radius: 12px; border: 1px dashed #cbd5e1;">
                                <div style="font-size: 2rem; margin-bottom: 0.5rem;">📭</div>
                                <div style="font-weight: 600; color: #475569; font-size: 0.95rem;">No comments or queries for this role yet</div>
                                <p style="font-size: 0.85rem; color: #94a3b8; margin: 0.25rem 0 0 0;">
                                    When candidates comment on LinkedIn or submit queries from Naukri, Indeed, or your job link, they will appear right here.
                                </p>
                            </div>
                        @else
                            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                                @foreach($roleInquiries as $inquiry)
                                    <div style="background: {{ $inquiry->is_pinned ? 'linear-gradient(135deg, #ffffff 0%, #faf5ff 100%)' : 'white' }}; border: {{ $inquiry->is_pinned ? '2px solid #8b5cf6' : '1px solid #e2e8f0' }}; border-radius: 14px; padding: 1.25rem; box-shadow: {{ $inquiry->is_pinned ? '0 4px 15px rgba(139,92,246,0.12)' : '0 2px 8px rgba(0,0,0,0.02)' }}; transition: all 0.2s ease; position: relative;">
                                        
                                        <!-- Inquiry Header: Candidate Info & Badges -->
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 0.75rem;">
                                            <div>
                                                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                                    <span style="font-weight: 700; color: #1e1b4b; font-size: 1.05rem;">
                                                        {{ $inquiry->candidate_name }}
                                                    </span>

                                                    @if($inquiry->is_pinned)
                                                        <span class="badge" style="background: #ede9fe; color: #6d28d9; border: 1px solid #c4b5fd; font-weight: 700; font-size: 0.72rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                                            📌 Pinned Query
                                                        </span>
                                                    @endif

                                                    @if($inquiry->ai_match_score !== null)
                                                        <span class="badge" style="background: {{ $inquiry->ai_match_score >= 80 ? '#dcfce7' : ($inquiry->ai_match_score >= 60 ? '#e0e7ff' : '#fef3c7') }}; color: {{ $inquiry->ai_match_score >= 80 ? '#15803d' : ($inquiry->ai_match_score >= 60 ? '#4338ca' : '#b45309') }}; border: 1px solid {{ $inquiry->ai_match_score >= 80 ? '#bbf7d0' : ($inquiry->ai_match_score >= 60 ? '#c7d2fe' : '#fde68a') }}; font-weight: 700; font-size: 0.72rem;">
                                                            🤖 Rank #{{ $inquiry->ai_rank }} • {{ $inquiry->ai_match_score }}% Match
                                                        </span>
                                                    @endif

                                                    @if($inquiry->candidate_email)
                                                        <span style="font-size: 0.8rem; color: #64748b;">
                                                            &lt;{{ $inquiry->candidate_email }}&gt;
                                                        </span>
                                                    @endif

                                                    @if($inquiry->candidate_phone)
                                                        <span class="badge" style="background: #f0fdf4; color: #166534; border: 1px solid #86efac; font-size: 0.72rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                                            📱 {{ $inquiry->candidate_phone }}
                                                        </span>
                                                    @endif

                                                    <!-- Platform Pill -->
                                                    @if($inquiry->platform === 'linkedin')
                                                        <span class="badge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-size: 0.72rem;">
                                                            💼 LinkedIn
                                                        </span>
                                                    @elseif($inquiry->platform === 'whatsapp')
                                                        <span class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-size: 0.72rem; display: inline-flex; align-items: center; gap: 0.25rem; font-weight: 700;">
                                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.772.82 2.791.82 3.181 0 5.767-2.586 5.768-5.766 0-3.18-2.586-5.766-5.768-5.766zm9.969 5.828c0 5.514-4.486 10-10 10-1.802 0-3.486-.481-4.945-1.321l-5.055 1.321 1.353-4.939c-.896-1.492-1.353-3.21-1.353-5.061 0-5.514 4.486-10 10-10s10 4.486 10 10z"/></svg>
                                                            WhatsApp
                                                        </span>
                                                    @elseif($inquiry->platform === 'naukri')
                                                        <span class="badge" style="background: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 0.72rem;">
                                                            🔵 Naukri
                                                        </span>
                                                    @elseif($inquiry->platform === 'indeed')
                                                        <span class="badge" style="background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; font-size: 0.72rem;">
                                                            🔷 Indeed
                                                        </span>
                                                    @else
                                                        <span class="badge" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-size: 0.72rem;">
                                                            🌐 Direct Link
                                                        </span>
                                                    @endif

                                                    <!-- Type Pill -->
                                                    <span class="badge" style="background: #f3f4f6; color: #4b5563; font-size: 0.72rem;">
                                                        {{ ucfirst($inquiry->type) }}
                                                    </span>
                                                </div>

                                                @if($inquiry->candidate_headline)
                                                    <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.2rem;">
                                                        {{ $inquiry->candidate_headline }}
                                                    </div>
                                                @endif
                                            </div>

                                            <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
                                                <span style="font-size: 0.78rem; color: #94a3b8;">
                                                    {{ $inquiry->created_at->diffForHumans() }}
                                                </span>

                                                <!-- Pin / Unpin Button Form -->
                                                <form action="{{ route('company.inquiries.pin', $inquiry->id) }}" method="POST" style="margin: 0;">
                                                    @csrf
                                                    <button type="submit" title="{{ $inquiry->is_pinned ? 'Unpin this inquiry' : 'Pin inquiry to top of this job role' }}" style="font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.65rem; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease; border: {{ $inquiry->is_pinned ? '1px solid #c4b5fd' : '1px solid #cbd5e1' }}; background: {{ $inquiry->is_pinned ? '#ede9fe' : '#ffffff' }}; color: {{ $inquiry->is_pinned ? '#6d28d9' : '#475569' }};">
                                                        <span>📌</span>
                                                        <span>{{ $inquiry->is_pinned ? 'Unpin' : 'Pin to Top' }}</span>
                                                    </button>
                                                </form>

                                                <!-- Status Update Form -->
                                                <form action="{{ route('company.inquiries.status', $inquiry->id) }}" method="POST" style="margin: 0;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="status" data-prev-status="{{ $inquiry->status }}" onchange="handleStatusChange(this, {{ $inquiry->id }}, '{{ addslashes($inquiry->candidate_name) }}', '{{ addslashes($inquiry->candidate_email ?? '') }}', '{{ addslashes($inquiry->jobPosting->title ?? 'Position') }}', '{{ $inquiry->interview_scheduled_at ? $inquiry->interview_scheduled_at->format('Y-m-d\TH:i') : '' }}', '{{ $inquiry->meeting_type ?? 'google_meet' }}', '{{ addslashes($inquiry->meeting_link_or_location ?? '') }}', '{{ addslashes($inquiry->interview_notes ?? '') }}')" style="font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.6rem; border-radius: 8px; border: 1px solid #cbd5e1; background: 
                                                        {{ $inquiry->status === 'new' ? '#fef3c7' : ($inquiry->status === 'replied' ? '#dcfce7' : ($inquiry->status === 'shortlisted' ? '#fef9c3' : '#f1f5f9')) }}; 
                                                        color: {{ $inquiry->status === 'new' ? '#92400e' : ($inquiry->status === 'replied' ? '#166534' : ($inquiry->status === 'shortlisted' ? '#854d0e' : '#334155')) }};">
                                                        <option value="new" {{ $inquiry->status === 'new' ? 'selected' : '' }}>🟡 New</option>
                                                        <option value="under_review" {{ $inquiry->status === 'under_review' ? 'selected' : '' }}>🔵 Under Review</option>
                                                        <option value="replied" {{ $inquiry->status === 'replied' ? 'selected' : '' }}>🟢 Replied</option>
                                                        <option value="shortlisted" {{ $inquiry->status === 'shortlisted' ? 'selected' : '' }}>⭐ Shortlisted</option>
                                                        <option value="rejected" {{ $inquiry->status === 'rejected' ? 'selected' : '' }}>❌ Rejected</option>
                                                    </select>
                                                </form>
                                            </div>
                                        </div>

                                        @if($inquiry->status === 'shortlisted' && $inquiry->interview_scheduled_at)
                                            <div style="background: linear-gradient(135deg, #fefce8 0%, #fffbeb 100%); border: 1px solid #fde047; border-left: 4px solid #ca8a04; border-radius: 8px; padding: 0.6rem 0.85rem; margin-bottom: 0.75rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
                                                <div style="font-size: 0.82rem; color: #854d0e;">
                                                    <span style="font-weight: 700;">📅 Interview:</span> {{ $inquiry->interview_scheduled_at->format('D, M j, Y \a\t g:i A') }}
                                                    <span style="display: inline-block; background: #fef08a; color: #713f12; padding: 1px 7px; border-radius: 5px; font-weight: 700; font-size: 0.72rem; margin-left: 6px;">
                                                        @if($inquiry->meeting_type === 'in_person') 🏢 In-Person
                                                        @elseif($inquiry->meeting_type === 'phone') 📞 Phone
                                                        @elseif($inquiry->meeting_type === 'zoom') 🎥 Zoom
                                                        @else 💻 Google Meet
                                                        @endif
                                                    </span>
                                                    @if($inquiry->meeting_link_or_location)
                                                        <span style="margin-left: 6px; color: #475569;">
                                                            @if(Str::startsWith($inquiry->meeting_link_or_location, ['http://', 'https://']))
                                                                <a href="{{ $inquiry->meeting_link_or_location }}" target="_blank" style="color: #4f46e5; font-weight: 600; text-decoration: underline;">Link ↗</a>
                                                            @else
                                                                • {{ Str::limit($inquiry->meeting_link_or_location, 35) }}
                                                            @endif
                                                        </span>
                                                    @endif
                                                </div>
                                                <button type="button" onclick="openShortlistModal({{ $inquiry->id }}, '{{ addslashes($inquiry->candidate_name) }}', '{{ addslashes($inquiry->candidate_email ?? '') }}', '{{ addslashes($inquiry->jobPosting->title ?? 'Position') }}', '{{ $inquiry->interview_scheduled_at ? $inquiry->interview_scheduled_at->format('Y-m-d\TH:i') : '' }}', '{{ $inquiry->meeting_type ?? 'google_meet' }}', '{{ addslashes($inquiry->meeting_link_or_location ?? '') }}', '{{ addslashes($inquiry->interview_notes ?? '') }}')" style="background: #ffffff; border: 1px solid #eab308; color: #854d0e; font-size: 0.72rem; font-weight: 700; padding: 0.25rem 0.6rem; border-radius: 6px; cursor: pointer;">
                                                    ✏️ Reschedule
                                                </button>
                                            </div>
                                        @endif

                                        <!-- Message / Query Content -->
                                        <div style="background: #f8fafc; border-left: 3px solid #818cf8; padding: 0.85rem 1rem; border-radius: 8px; color: #334155; font-size: 0.92rem; line-height: 1.5; margin-bottom: 0.75rem;">
                                            {{ $inquiry->message }}
                                        </div>

                                        <!-- Resume attachment if uploaded -->
                                        @if($inquiry->resume_path)
                                            <div style="margin-bottom: 0.75rem;">
                                                <a href="{{ asset('storage/' . $inquiry->resume_path) }}" target="_blank" style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; background: #e0e7ff; color: #4338ca; padding: 0.3rem 0.75rem; border-radius: 8px; text-decoration: none; font-weight: 600;">
                                                    <span>📄</span> View Candidate Resume Attachment ↗
                                                </a>
                                            </div>
                                        @endif

                                        <!-- Existing Company Reply -->
                                        @if($inquiry->company_reply)
                                            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 0.75rem 1rem; margin-top: 0.75rem; font-size: 0.85rem;">
                                                <div style="font-weight: 700; color: #166534; display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.25rem;">
                                                    <span>💬 Your Company Reply:</span>
                                                    @if($inquiry->replied_at)
                                                        <span style="font-weight: normal; font-size: 0.75rem; color: #15803d;">{{ $inquiry->replied_at->diffForHumans() }}</span>
                                                    @endif
                                                </div>
                                                <div style="color: #14532d; line-height: 1.5;">
                                                    {{ $inquiry->company_reply }}
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Reply Form Accordion / Box -->
                                        <div style="margin-top: 0.75rem; display: flex; flex-direction: column; gap: 0.5rem;">
                                            <details style="font-size: 0.85rem;">
                                                <summary style="cursor: pointer; color: #4c51bf; font-weight: 600; padding: 0.25rem 0;">
                                                    {{ $inquiry->company_reply ? '✏️ Edit / Send Standard Reply' : '↩️ Reply to Candidate (Standard / Email)' }}
                                                </summary>
                                                <form action="{{ route('company.inquiries.reply', $inquiry->id) }}" method="POST" style="margin-top: 0.75rem;">
                                                    @csrf
                                                    <div class="form-group" style="margin-bottom: 0.5rem;">
                                                        <textarea name="company_reply" rows="3" class="form-control" placeholder="Write your response, clarification, or next interview step..." required style="font-size: 0.85rem;">{{ $inquiry->company_reply }}</textarea>
                                                    </div>
                                                    <div style="display: flex; justify-content: flex-end;">
                                                        <button type="submit" class="btn-primary" style="font-size: 0.8rem; padding: 0.4rem 1rem;">
                                                            Send Reply
                                                        </button>
                                                    </div>
                                                </form>
                                            </details>

                                            <!-- WhatsApp Direct Reply Box -->
                                            <details style="font-size: 0.85rem;">
                                                <summary style="cursor: pointer; color: #15803d; font-weight: 700; padding: 0.25rem 0; display: inline-flex; align-items: center; gap: 0.35rem;">
                                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="#25D366"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.772.82 2.791.82 3.181 0 5.767-2.586 5.768-5.766 0-3.18-2.586-5.766-5.768-5.766zm9.969 5.828c0 5.514-4.486 10-10 10-1.802 0-3.486-.481-4.945-1.321l-5.055 1.321 1.353-4.939c-.896-1.492-1.353-3.21-1.353-5.061 0-5.514 4.486-10 10-10s10 4.486 10 10z"/></svg>
                                                    <span>Reply directly via WhatsApp</span>
                                                </summary>
                                                <form action="{{ route('company.inquiries.whatsapp_reply', $inquiry->id) }}" method="POST" style="margin-top: 0.5rem; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 10px; padding: 1rem;">
                                                    @csrf
                                                    <div style="font-size: 0.82rem; color: #166534; font-weight: 600; margin-bottom: 0.5rem;">
                                                        Deliver an instant WhatsApp reply directly to candidate's mobile number:
                                                    </div>
                                                    <div style="margin-bottom: 0.6rem;">
                                                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #15803d; margin-bottom: 0.25rem;">Candidate Phone Number *</label>
                                                        <input type="tel" name="candidate_phone" value="{{ $inquiry->candidate_phone }}" required placeholder="+91 98765 43210" class="form-control" style="font-size: 0.85rem; padding: 0.45rem 0.65rem; background: white;">
                                                    </div>
                                                    <div style="margin-bottom: 0.6rem;">
                                                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #15803d; margin-bottom: 0.25rem;">WhatsApp Message Content *</label>
                                                        <textarea name="company_reply" rows="2" class="form-control" placeholder="Hi {{ $inquiry->candidate_name }}, thanks for your interest in our {{ $jobRole->title }} opening..." required style="font-size: 0.85rem; background: white;"></textarea>
                                                    </div>
                                                    <div style="display: flex; justify-content: flex-end;">
                                                        <button type="submit" style="background: #25D366; color: white; border: none; font-size: 0.82rem; font-weight: 700; padding: 0.45rem 1.1rem; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; box-shadow: 0 2px 8px rgba(37,211,102,0.3);">
                                                            <span>🚀</span> Send WhatsApp 
                                                        </button>
                                                    </div>
                                                </form>
                                            </details>
                                        </div>

                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                </div>
            @endforeach
        </div>
    @endif

</div>

<script>
    function toggleManualLogForm() {
        const el = document.getElementById('manual-comment-card');
        if (el) {
            el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
            if (el.style.display === 'block') {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    }
</script>

@include('company.partials.shortlist_modal')

<!-- Company WhatsApp Configuration Modal -->
<div id="companyWhatsappModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(5px); align-items: center; justify-content: center; padding: 1.5rem;">
    <div style="background: white; border-radius: 18px; max-width: 480px; width: 100%; box-shadow: 0 20px 40px rgba(0,0,0,0.25); overflow: hidden;">
        <div style="background: linear-gradient(135deg, #25D366 0%, #128C7E 100%); padding: 1.25rem 1.5rem; color: white; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.772.82 2.791.82 3.181 0 5.767-2.586 5.768-5.766 0-3.18-2.586-5.766-5.768-5.766zm9.969 5.828c0 5.514-4.486 10-10 10-1.802 0-3.486-.481-4.945-1.321l-5.055 1.321 1.353-4.939c-.896-1.492-1.353-3.21-1.353-5.061 0-5.514 4.486-10 10-10s10 4.486 10 10z"/></svg>
                <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700;">Company WhatsApp Line</h3>
            </div>
            <button type="button" onclick="document.getElementById('companyWhatsappModal').style.display='none'" style="background: rgba(255,255,255,0.2); border: none; color: white; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; font-size: 1.1rem; line-height: 1;">&times;</button>
        </div>
        <form action="{{ route('company.profile.whatsapp') }}" method="POST" style="padding: 1.5rem;">
            @csrf
            <p style="font-size: 0.88rem; color: #4b5563; margin-top: 0; margin-bottom: 1rem; line-height: 1.45;">
                Specify your company's official WhatsApp phone number where candidate inquiries from job postings will be forwarded.
            </p>
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label class="form-label" style="font-size: 0.85rem; font-weight: 700;">WhatsApp Number (with country code) *</label>
                <input type="tel" name="whatsapp_number" value="{{ Auth::user()->companyProfile->whatsapp_number ?? '' }}" required placeholder="e.g. +91 98765 43210" class="form-control" style="font-size: 0.95rem; padding: 0.65rem 0.85rem;">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" onclick="document.getElementById('companyWhatsappModal').style.display='none'" class="btn-secondary" style="padding: 0.55rem 1rem;">Cancel</button>
                <button type="submit" style="background: #25D366; color: white; border: none; font-weight: 700; padding: 0.55rem 1.2rem; border-radius: 8px; cursor: pointer;">Save Number</button>
            </div>
        </form>
    </div>
</div>
@endsection
