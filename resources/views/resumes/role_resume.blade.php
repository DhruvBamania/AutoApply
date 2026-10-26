@extends('layouts.app')

@section('content')
<div style="max-width: 800px; margin: 0 auto; padding-top: 2rem;">
    
    <div style="text-align: center; margin-bottom: 3rem;">
        <h1 style="font-size: 3rem; margin-bottom: 1rem;">Role-Specific Resume</h1>
        <p class="subtitle" style="font-size: 1.1rem;">
            Generate an ATS-optimized resume tailored strictly for a specific job role (e.g. Frontend Developer). 
            Our AI will rewrite your default resume and inject role-specific keywords.
        </p>
    </div>

    @if(!auth()->user()->resume_text)
        <div class="card" style="text-align: center; border-color: #fca5a5;">
            <div style="margin-bottom: 1.5rem;">
                <h3 style="color: #ef4444; margin-bottom: 0.5rem;">Base Resume Required</h3>
                <p class="text-muted">You must upload your default resume first so our AI has a base to work from.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="btn-primary" style="text-decoration: none;">Go to Dashboard</a>
        </div>
    @else
        <div class="card">
            <div class="card-header">
                <h2>Define Your Target Role</h2>
                <span class="badge badge-posted" style="font-size: 0.85rem;">AI Powered</span>
            </div>
            
            <form action="{{ route('role_resume.generate') }}" method="POST" id="role-form">
                @csrf
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label class="form-label" style="font-weight: 600;">Target Job Role</label>
                    <input type="text" name="job_role" class="form-control" placeholder="e.g. Senior Laravel Developer" required style="font-size: 1.1rem; padding: 1rem;">
                    <small class="text-muted" style="display: block; margin-top: 0.5rem;">Enter the generic job title you want to target. We'll optimize your skills and experience to match.</small>
                </div>

                <div style="display: flex; gap: 1rem; align-items: center; justify-content: space-between; flex-wrap: wrap;">
                    <p style="font-size: 0.9rem; color: var(--text-muted); max-width: 400px; margin: 0;">
                        Generating takes about 10-20 seconds. You will be redirected to the Dynamic Editor to preview and download the result.
                    </p>
                    <button type="submit" class="btn-primary" id="generate-btn" style="min-width: 200px; font-size: 1.1rem; padding: 0.8rem 1.5rem;">
                        <span id="btn-text">✨ Generate Role Resume</span>
                        <span id="btn-loading" style="display: none;">⚙️ Processing AI...</span>
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>

<script>
    document.getElementById('role-form')?.addEventListener('submit', function() {
        const btnText = document.getElementById('btn-text');
        const btnLoading = document.getElementById('btn-loading');
        const btn = document.getElementById('generate-btn');
        
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline';
        btn.style.opacity = '0.7';
        btn.style.cursor = 'wait';
    });
</script>
@endsection
