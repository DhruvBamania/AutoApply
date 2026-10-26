@extends('layouts.app')

@section('content')
    <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="margin-top: 0.5rem;">AI Resume Tailor</h1>
            <p class="subtitle" style="margin-bottom: 0;">Dynamically rebuild your resume for specific job descriptions.</p>
        </div>
    </div>

    <div class="grid-2">
        <!-- Input Form -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Job Details</div>
            </div>
            
            <form action="{{ route('resumes.generate') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Company Name (Optional)</label>
                    <input type="text" name="company_name" class="form-control" placeholder="e.g. Google">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Job Description / Requirements</label>
                    <textarea name="job_description" class="form-control" style="height: 250px; resize: vertical;" placeholder="Paste the job description here..." required></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    ✨ Tailor My Resume
                </button>
            </form>
        </div>

        <!-- History -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Tailored Resumes</div>
            </div>
            
            @if($resumes->isEmpty())
                <p style="color: var(--text-muted); text-align: center; margin-top: 2rem;">You haven't generated any tailored resumes yet.</p>
            @else
                <div class="lead-list">
                    @foreach($resumes as $resume)
                        <div class="lead-item">
                            <div class="lead-info">
                                <h4>{{ $resume->company_name ?: 'Unknown Company' }}</h4>
                                <p>Created {{ $resume->created_at->diffForHumans() }}</p>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="{{ route('resumes.show', $resume->id) }}" class="btn btn-secondary btn-sm">View</a>
                                <form action="{{ route('resumes.destroy', $resume->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--danger); border-color: #fecdd3;">Delete</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
