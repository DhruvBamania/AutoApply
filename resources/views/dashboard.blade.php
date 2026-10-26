@extends('layouts.app')

@section('content')
    <div style="margin-bottom: 2rem;">
        <h1>Welcome back, Developer.</h1>
        <p class="subtitle">Your autonomous AI agent is ready to hunt for leads.</p>
    </div>

    @php
        $pendingHeadhunterCount = auth()->user()?->headhunterInvitations()->where('status', 'invited')->count() ?? 0;
    @endphp

    @if($pendingHeadhunterCount > 0)
        <div style="background: linear-gradient(135deg, #e0f2fe 0%, #ede9fe 100%); border: 1.5px solid #7dd3fc; border-left: 5px solid #0284c7; border-radius: 14px; padding: 1.1rem 1.4rem; margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; box-shadow: 0 4px 15px rgba(2,132,199,0.1);">
            <div style="display: flex; align-items: center; gap: 0.85rem;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: #0284c7; color: white; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                    🎯
                </div>
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 700; color: #0c4a6e; margin: 0;">
                        You have {{ $pendingHeadhunterCount }} Scouted Job {{ Str::plural('Invitation', $pendingHeadhunterCount) }}!
                    </h3>
                    <p style="font-size: 0.85rem; color: #334155; margin: 0.2rem 0 0 0;">
                        Employers' AI Headhunters have scouted your tech profile and sent personalized opportunities.
                    </p>
                </div>
            </div>
            <a href="{{ route('candidate.headhunter.index') }}" class="btn-primary" style="background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%); font-size: 0.88rem; padding: 0.55rem 1.25rem;">
                View Scouted Invitations →
            </a>
        </div>
    @endif

    <div class="grid-2">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Your Campaigns</div>
            </div>

            <form action="{{ route('campaigns.store') }}" method="POST" style="margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--surface-border);">
                @csrf
                <div style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
                    <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                        <label class="form-label">Campaign Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Q3 Outreach" required>
                    </div>
                    <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                        <label class="form-label">Target Job Title</label>
                        <input type="text" name="job_title_target" class="form-control" placeholder="e.g. Senior Laravel Dev" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="flex: 1 1 140px; min-height: 42px;">+ Create</button>
                </div>
            </form>
            
            <div class="lead-list">
                @forelse($campaigns ?? [] as $campaign)
                    <div class="lead-item">
                        <div class="lead-info">
                            <h4>{{ $campaign->title }}</h4>
                            <p>Target: {{ $campaign->job_title_target }}</p>
                        </div>
                        <div style="display: flex; gap: 1rem; align-items: center;">
                            <span class="badge badge-{{ $campaign->status }}">{{ $campaign->status }}</span>
                            <a href="/campaigns/{{ $campaign->id }}" class="btn btn-secondary btn-sm">View Leads →</a>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                        <p>No active campaigns found.</p>
                        <p style="font-size: 0.9rem; margin-top: 0.5rem;">Create a campaign to let the AI start finding and emailing leads.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">Resume & Core Data</div>
            </div>
            
            <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Upload your latest PDF resume. The AI uses this to find matches and write hyper-personalized cold emails.</p>

            <form action="/resume/upload" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label class="form-label">Resume PDF File</label>
                    <input type="file" name="resume" class="form-control" accept=".pdf">
                </div>
                <button type="submit" class="btn btn-secondary">Upload Resume</button>
            </form>

            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--surface-border);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <span class="form-label" style="margin-bottom: 0;">Extracted Text Preview:</span>
                    @if(auth()->user()?->resume_text)
                        <span class="badge badge-running">Active</span>
                    @else
                        <span class="badge badge-paused">Missing</span>
                    @endif
                </div>
                <div class="draft-box" style="height: 150px; overflow-y: auto;">
                    {{ auth()->user()?->resume_text ?? 'No resume text available yet. Please upload a PDF.' }}
                </div>
            </div>
        </div>
    </div>
@endsection
