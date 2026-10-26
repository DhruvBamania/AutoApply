@extends('layouts.app')

@section('content')
    <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
        <div>
            <a href="/dashboard" style="color: var(--primary-color); text-decoration: none; font-size: 0.9rem;">← Back to Dashboard</a>
            <h1 style="margin-top: 0.5rem;">{{ $campaign->title ?? 'Campaign Details' }}</h1>
            <p class="subtitle" style="margin-bottom: 0;">Targeting: <strong>{{ $campaign->job_title_target ?? 'Target Title' }}</strong> | Status: <span class="badge badge-{{ $campaign->status ?? 'paused' }}">{{ $campaign->status ?? 'paused' }}</span></p>
        </div>
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <form action="{{ route('campaigns.stop', $campaign->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-secondary">
                    {{ $campaign->status === 'paused' ? 'Resume Campaign' : 'Stop Campaign' }}
                </button>
            </form>
            <form action="{{ route('campaigns.generate', $campaign->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">⚡ Generate More Leads</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Discovered Leads & AI Drafts</div>
            <div>
                <span class="badge badge-pending">Pending: {{ $pendingCount ?? 0 }}</span>
                <span class="badge badge-drafted">Drafted: {{ $draftedCount ?? 0 }}</span>
                <span class="badge badge-emailed">Emailed: {{ $emailedCount ?? 0 }}</span>
            </div>
        </div>

        <div class="lead-list" style="margin-top: 2rem;">
            @forelse($leads ?? [] as $lead)
                <div class="lead-item" style="flex-direction: column; align-items: stretch; gap: 1.5rem;">
                    
                    <!-- Lead Header -->
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="lead-info">
                            <h4>{{ $lead->company_name }}</h4>
                            <p>Founder: {{ $lead->founder_name }} ({{ $lead->founder_email }})</p>
                        </div>
                        <span class="badge badge-{{ $lead->status }}">{{ $lead->status }}</span>
                    </div>

                    <!-- AI Draft Section -->
                    @if($lead->emailDrafts->isNotEmpty())
                        @php $draft = $lead->emailDrafts->last(); @endphp
                        <div style="background: #f8fafc; border-radius: var(--radius-md); padding: 1.5rem; border: 1px solid var(--surface-border);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                <div class="form-label" style="color: var(--primary-color);">AI Generated Draft</div>
                                @if($draft->is_approved || $lead->status === 'emailed')
                                    <span style="color: var(--success); font-size: 0.9rem; font-weight: 500;">✓ Sent</span>
                                @endif
                            </div>
                            
                            <div style="margin-bottom: 1rem;">
                                <span style="color: var(--text-muted); font-size: 0.9rem;">Subject:</span> 
                                <span style="font-family: monospace;">{{ $draft->subject }}</span>
                            </div>

                            @if(!$draft->is_approved && $lead->status !== 'emailed')
                                <!-- Editable Form -->
                                <form action="{{ route('drafts.update', $draft->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <textarea name="body" class="form-control" style="font-family: monospace; height: 250px; margin-bottom: 1rem; width: 100%;">{{ $draft->body }}</textarea>
                                    
                                    <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                                        <button type="submit" class="btn btn-secondary btn-sm">Save Edits</button>
                                    </div>
                                </form>
                                
                                <form action="{{ route('drafts.send', $draft->id) }}" method="POST" style="margin-top: -30px;">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm">✓ Approve & Send Email</button>
                                </form>
                            @else
                                <!-- Read-only View -->
                                <div class="draft-box" style="margin-top: 0; margin-bottom: 1.5rem; font-family: inherit;">
                                    {{ $draft->body }}
                                </div>
                            @endif
                        </div>
                    @else
                        <!-- No draft yet -->
                        <div style="padding: 1rem; text-align: center; background: #f8fafc; border-radius: var(--radius-md); border: 1px solid var(--surface-border);">
                            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1rem;">AI is currently analyzing the job description and drafting an email...</p>
                            <button class="btn btn-secondary btn-sm" disabled>Processing via Groq...</button>
                        </div>
                    @endif

                </div>
            @empty
                <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🤖</div>
                    <p>No leads have been generated yet.</p>
                    <form action="{{ route('campaigns.generate', $campaign->id) }}" method="POST" style="margin-top: 1rem;">
                        @csrf
                        <button type="submit" class="btn btn-primary">Start Generation</button>
                    </form>
                </div>
            @endforelse
        </div>
    </div>
@endsection
