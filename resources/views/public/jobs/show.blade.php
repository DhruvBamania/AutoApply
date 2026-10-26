@extends('layouts.app')

@section('content')
<div style="max-width: 860px; margin: 0 auto; display: flex; flex-direction: column; gap: 2rem;">

    <!-- Top Banner -->
    <div class="card" style="background: linear-gradient(135deg, rgba(255,255,255,0.7) 0%, rgba(238,242,255,0.85) 100%); border-left: 6px solid #4c51bf;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
            <div>
                <div style="font-size: 0.9rem; color: #4338ca; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem;">
                    {{ $job->user->companyProfile->company_name ?? $job->user->name }} is hiring
                </div>
                <h1 style="font-size: 2rem; font-weight: 800; color: #1e1b4b; margin: 0 0 0.5rem 0;">
                    {{ $job->title }}
                </h1>
                <div style="display: flex; flex-wrap: wrap; gap: 1rem; font-size: 0.9rem; color: #4b5563;">
                    <span class="badge" style="background: #e0e7ff; color: #3730a3;">{{ $job->job_type }}</span>
                    @if($job->location) <span>📍 {{ $job->location }}</span> @endif
                    @if($job->salary_range) <span>💰 {{ $job->salary_range }}</span> @endif
                    @if($job->experience_required) <span>⏳ {{ $job->experience_required }}</span> @endif
                    <span>📅 Posted {{ $job->created_at->format('M d, Y') }}</span>
                </div>
            </div>

            @if($referralPlatform !== 'direct')
                <div style="font-size: 0.8rem; background: rgba(99, 102, 241, 0.1); color: #4338ca; padding: 0.35rem 0.75rem; border-radius: 20px; font-weight: 600;">
                    Via {{ ucfirst($referralPlatform) }} Post
                </div>
            @endif
        </div>
    </div>

    <!-- Attached Flyer Image if Present -->
    @if($job->image_path)
        <div class="card" style="padding: 1rem; display: flex; justify-content: center; background: white;">
            <img src="{{ asset('storage/' . $job->image_path) }}" alt="{{ $job->title }}" style="max-width: 100%; max-height: 420px; object-fit: contain; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        </div>
    @endif

    <!-- Job Description & Required Skills -->
    <div class="card">
        @if($job->skills)
            <div style="margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: #1f2937; margin-bottom: 0.6rem;">Required Skills</h3>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                    @foreach(explode(',', $job->skills) as $skill)
                        <span style="background: #eef2ff; color: #4338ca; padding: 0.35rem 0.85rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                            {{ trim($skill) }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        <div>
            <h3 style="font-size: 1.1rem; font-weight: 700; color: #1f2937; margin-bottom: 0.6rem;">Role Overview & Responsibilities</h3>
            <div style="background: rgba(255,255,255,0.7); border: 1px solid rgba(229,231,235,0.9); padding: 1.5rem; border-radius: 14px; white-space: pre-wrap; line-height: 1.6; color: #374151; font-size: 0.95rem;">
                {{ $job->description }}
            </div>
        </div>
    </div>

    <!-- Candidate Inquiry & Application Form -->
    <div class="card" style="border: 2px solid #818cf8; background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(245,247,255,0.95) 100%);" id="apply-section">
        <div style="margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.4rem; font-weight: 700; color: #1e1b4b; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span>💬</span> Have a Query or Want to Apply?
            </h2>
            <p style="color: #6b7280; font-size: 0.9rem; margin: 0;">
                Submit your query, question, or application directly to <strong>{{ $job->user->companyProfile->company_name ?? $job->user->name }}</strong>'s recruiting team.
            </p>
        </div>

        <form action="{{ route('jobs.public.inquire', $job->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="platform" value="{{ $referralPlatform }}">

            <!-- Type Selection -->
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label class="form-label">Submission Type</label>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer; font-size: 0.9rem; font-weight: 500;">
                        <input type="radio" name="type" value="query" checked style="accent-color: #4c51bf;">
                        <span>❓ Question / Inquiry</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer; font-size: 0.9rem; font-weight: 500;">
                        <input type="radio" name="type" value="application" style="accent-color: #4c51bf;">
                        <span>📄 Formal Application</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer; font-size: 0.9rem; font-weight: 500;">
                        <input type="radio" name="type" value="comment" style="accent-color: #4c51bf;">
                        <span>💬 General Comment</span>
                    </label>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;" class="grid-2">
                <div class="form-group">
                    <label class="form-label">Your Full Name <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="candidate_name" class="form-control" placeholder="e.g. Alex Johnson" required value="{{ old('candidate_name') }}">
                    @error('candidate_name') <span style="color: #ef4444; font-size: 0.8rem;">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Your Email Address <span style="color: #ef4444;">*</span></label>
                    <input type="email" name="candidate_email" class="form-control" placeholder="alex@example.com" required value="{{ old('candidate_email') }}">
                    @error('candidate_email') <span style="color: #ef4444; font-size: 0.8rem;">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Professional Headline / Current Role / Profile Link</label>
                <input type="text" name="candidate_headline" class="form-control" placeholder="e.g. Senior Backend Engineer / linkedin.com/in/alex" value="{{ old('candidate_headline') }}">
            </div>

            <div class="form-group">
                <label class="form-label">Your Query / Message / Cover Note <span style="color: #ef4444;">*</span></label>
                <textarea name="message" class="form-control" rows="4" placeholder="Enter your question regarding the role, compensation, tech stack, or why you'd be a great fit..." required>{{ old('message') }}</textarea>
                @error('message') <span style="color: #ef4444; font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Attach Resume / CV <span style="color: #6b7280; font-weight: normal;">(Optional, PDF or DOCX)</span></label>
                <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx" style="background: white;">
                @error('resume') <span style="color: #ef4444; font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; padding: 0.85rem; font-size: 1rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                <span>🚀</span> Send Query / Application to Hiring Team
            </button>
        </form>
    </div>

</div>
@endsection
