@extends('layouts.app')

@section('content')
<div style="max-width: 860px; width: 100%; margin: 0 auto; display: flex; flex-direction: column; gap: 2rem;">

    <!-- Breadcrumb / Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <a href="{{ route('company.jobs.index') }}" style="color: #4c51bf; font-weight: 600; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.3rem; margin-bottom: 0.5rem;">
                ← Back to All Job Postings
            </a>
            <h1 style="font-size: 1.8rem; font-weight: 700; color: #1e1b4b; margin: 0;">Post a New Job Role</h1>
            <p style="color: #6b7280; font-size: 0.9rem; margin: 0.25rem 0 0 0;">
                Publishing for: <strong>{{ $company->company_name ?? auth()->user()->name }}</strong>
            </p>
        </div>
    </div>

    <!-- Job Creation Form -->
    <div class="card">
        <form action="{{ route('company.jobs.store') }}" method="POST" enctype="multipart/form-data" id="job-post-form">
            @csrf

            <!-- Section 1: Basic Role Details -->
            <div style="margin-bottom: 2rem;">
                <div style="font-size: 1.1rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; border-bottom: 1px solid rgba(0,0,0,0.06); padding-bottom: 0.5rem;">
                    <span>📌</span> Role & Position Details
                </div>

                <div class="form-group">
                    <label class="form-label">Job Title <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="e.g. Senior PHP Laravel Developer" autofocus>
                    @error('title')
                        <span style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;" class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Employment Type <span style="color: #ef4444;">*</span></label>
                        <select name="job_type" class="form-control" required>
                            <option value="Full-time" {{ old('job_type') === 'Full-time' ? 'selected' : '' }}>Full-time</option>
                            <option value="Part-time" {{ old('job_type') === 'Part-time' ? 'selected' : '' }}>Part-time</option>
                            <option value="Contract" {{ old('job_type') === 'Contract' ? 'selected' : '' }}>Contract</option>
                            <option value="Remote" {{ old('job_type') === 'Remote' ? 'selected' : '' }}>Remote</option>
                            <option value="Internship" {{ old('job_type') === 'Internship' ? 'selected' : '' }}>Internship</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Job Location</label>
                        <input type="text" name="location" class="form-control" value="{{ old('location', $company->location ?? '') }}" placeholder="e.g. Remote / San Francisco, CA">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;" class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Experience Required</label>
                        <input type="text" name="experience_required" class="form-control" value="{{ old('experience_required') }}" placeholder="e.g. 2 - 4 Years">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Salary / Compensation Range</label>
                        <input type="text" name="salary_range" class="form-control" value="{{ old('salary_range') }}" placeholder="e.g. $80,000 - $110,000 or ₹12 - 16 LPA">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Key Required Skills / Tech Stack</label>
                    <input type="text" name="skills" class="form-control" value="{{ old('skills') }}" placeholder="e.g. PHP, Laravel, MySQL, REST API, Redis, Git">
                </div>

                <div class="form-group">
                    <label class="form-label">Job Description & Responsibilities <span style="color: #ef4444;">*</span></label>
                    <textarea name="description" class="form-control" rows="6" required placeholder="Outline key responsibilities, qualifications, and benefits...">{{ old('description') }}</textarea>
                    @error('description')
                        <span style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Image / Flyer / Banner Upload -->
                <div class="form-group" style="background: rgba(243, 244, 246, 0.6); border: 2px dashed #cbd5e1; border-radius: 14px; padding: 1.25rem;">
                    <label class="form-label" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.25rem;">
                        <span style="font-weight: 700; color: #1f2937; display: flex; align-items: center; gap: 0.4rem;">
                            <span>🖼️</span> Hiring Banner / Flyer Graphic <span style="font-size: 0.8rem; color: #6b7280; font-weight: normal;">(Optional)</span>
                        </span>
                        <span style="font-size: 0.75rem; color: #6b7280;">PNG, JPG, WEBP (Max 5MB)</span>
                    </label>
                    <p style="font-size: 0.8rem; color: #6b7280; margin: 0 0 0.75rem 0;">
                        Attach a hiring banner or role graphic. It will be published directly with your LinkedIn post and candidate openings.
                    </p>
                    <input type="file" name="image" id="job-image-input" class="form-control" accept="image/png,image/jpeg,image/webp,image/gif" onchange="previewJobImage(event)" style="background: white;">
                    @error('image')
                        <span style="color: #ef4444; font-size: 0.85rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
                    @enderror

                    <!-- Live Image Preview -->
                    <div id="image-preview-container" style="display: none; margin-top: 1rem; position: relative; max-width: 320px;">
                        <img id="image-preview" src="#" alt="Job Banner Preview" style="width: 100%; max-height: 200px; object-fit: cover; border-radius: 10px; border: 1px solid #e5e7eb; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                        <button type="button" onclick="removeJobImage()" style="position: absolute; top: 8px; right: 8px; background: rgba(0,0,0,0.6); color: white; border: none; border-radius: 50%; width: 26px; height: 26px; cursor: pointer; font-size: 14px; line-height: 26px; text-align: center;">✕</button>
                    </div>
                </div>
            </div>

            <!-- Section 2: 1-Click Multi-Platform Selection -->
            <div style="background: linear-gradient(135deg, rgba(238,242,255,0.7) 0%, rgba(245,243,255,0.85) 100%); border: 2px dashed #818cf8; border-radius: 20px; padding: 1.5rem; margin-bottom: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem;">
                    <div>
                        <div style="font-size: 1.15rem; font-weight: 700; color: #312e81; display: flex; align-items: center; gap: 0.5rem;">
                            <span>🚀</span> 1-Click Multi-Platform Distribution
                        </div>
                        <p style="font-size: 0.85rem; color: #4338ca; margin: 0.2rem 0 0 0;">
                            Actual live posts will be published to your connected platforms simultaneously.
                        </p>
                    </div>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <a href="{{ route('company.integrations') }}" target="_blank" style="font-size: 0.8rem; color: #4338ca; font-weight: 600; text-decoration: underline;">
                            ⚙️ Manage Integrations
                        </a>
                        <button type="button" onclick="toggleAllPlatforms(true)" style="background: white; border: 1px solid #c7d2fe; color: #4338ca; font-size: 0.8rem; font-weight: 600; padding: 0.35rem 0.75rem; border-radius: 8px; cursor: pointer;">
                            Select All
                        </button>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 220px), 1fr)); gap: 1rem;">
                    
                    <!-- LinkedIn Card -->
                    @php $linkedInConn = isset($connectedAccounts['linkedin']); @endphp
                    <label style="background: white; border: 2px solid {{ $linkedInConn ? '#0077b5' : '#cbd5e1' }}; border-radius: 14px; padding: 1rem; display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer; transition: all 0.2s ease;">
                        <input type="checkbox" name="platforms[]" value="linkedin" class="platform-checkbox" {{ $linkedInConn ? 'checked' : '' }} style="width: 18px; height: 18px; margin-top: 0.2rem; accent-color: #0077b5;">
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div style="font-weight: 700; font-size: 0.95rem; color: #0077b5; display: flex; align-items: center; gap: 0.4rem;">
                                    <span>💼</span> LinkedIn Feed
                                </div>
                                @if($linkedInConn)
                                    <span style="font-size: 0.7rem; background: #dcfce7; color: #15803d; padding: 0.15rem 0.4rem; border-radius: 4px; font-weight: 600;">🟢 Linked</span>
                                @else
                                    <a href="{{ route('company.integrations') }}" style="font-size: 0.7rem; background: #fee2e2; color: #b91c1c; padding: 0.15rem 0.4rem; border-radius: 4px; font-weight: 600; text-decoration: none;">Connect ↗</a>
                                @endif
                            </div>
                            <div style="font-size: 0.78rem; color: #6b7280; margin-top: 0.3rem;">
                                Posts live hiring announcement across professional network.
                            </div>
                            @if($linkedInConn)
                                <div style="font-size: 0.72rem; color: #0077b5; margin-top: 0.3rem; word-break: break-all;">
                                    ✓ {{ Str::limit($connectedAccounts['linkedin']->account_identifier, 25) }}
                                </div>
                            @endif
                        </div>
                    </label>

                    <!-- Naukri Card -->
                    @php $naukriConn = isset($connectedAccounts['naukri']); @endphp
                    <label style="background: white; border: 2px solid {{ $naukriConn ? '#2563eb' : '#cbd5e1' }}; border-radius: 14px; padding: 1rem; display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer; transition: all 0.2s ease;">
                        <input type="checkbox" name="platforms[]" value="naukri" class="platform-checkbox" {{ $naukriConn ? 'checked' : '' }} style="width: 18px; height: 18px; margin-top: 0.2rem; accent-color: #2563eb;">
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div style="font-weight: 700; font-size: 0.95rem; color: #2563eb; display: flex; align-items: center; gap: 0.4rem;">
                                    <span>🔵</span> Naukri.com
                                </div>
                                @if($naukriConn)
                                    <span style="font-size: 0.7rem; background: #dcfce7; color: #15803d; padding: 0.15rem 0.4rem; border-radius: 4px; font-weight: 600;">🟢 Linked</span>
                                @else
                                    <a href="{{ route('company.integrations') }}" style="font-size: 0.7rem; background: #fee2e2; color: #b91c1c; padding: 0.15rem 0.4rem; border-radius: 4px; font-weight: 600; text-decoration: none;">Connect ↗</a>
                                @endif
                            </div>
                            <div style="font-size: 0.78rem; color: #6b7280; margin-top: 0.3rem;">
                                Publishes job listing to Naukri Recruiter network.
                            </div>
                            @if($naukriConn)
                                <div style="font-size: 0.72rem; color: #2563eb; margin-top: 0.3rem; word-break: break-all;">
                                    ✓ {{ Str::limit($connectedAccounts['naukri']->account_identifier, 25) }}
                                </div>
                            @endif
                        </div>
                    </label>

                    <!-- Indeed Card -->
                    @php $indeedConn = isset($connectedAccounts['indeed']); @endphp
                    <label style="background: white; border: 2px solid {{ $indeedConn ? '#0a66c2' : '#cbd5e1' }}; border-radius: 14px; padding: 1rem; display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer; transition: all 0.2s ease;">
                        <input type="checkbox" name="platforms[]" value="indeed" class="platform-checkbox" {{ $indeedConn ? 'checked' : '' }} style="width: 18px; height: 18px; margin-top: 0.2rem; accent-color: #0a66c2;">
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div style="font-weight: 700; font-size: 0.95rem; color: #0a66c2; display: flex; align-items: center; gap: 0.4rem;">
                                    <span>🔷</span> Indeed Employer
                                </div>
                                @if($indeedConn)
                                    <span style="font-size: 0.7rem; background: #dcfce7; color: #15803d; padding: 0.15rem 0.4rem; border-radius: 4px; font-weight: 600;">🟢 Linked</span>
                                @else
                                    <a href="{{ route('company.integrations') }}" style="font-size: 0.7rem; background: #fee2e2; color: #b91c1c; padding: 0.15rem 0.4rem; border-radius: 4px; font-weight: 600; text-decoration: none;">Connect ↗</a>
                                @endif
                            </div>
                            <div style="font-size: 0.78rem; color: #6b7280; margin-top: 0.3rem;">
                                Publishes via Employer API / ATS XML Feed index.
                            </div>
                            @if($indeedConn)
                                <div style="font-size: 0.72rem; color: #0a66c2; margin-top: 0.3rem; word-break: break-all;">
                                    ✓ {{ Str::limit($connectedAccounts['indeed']->account_identifier, 25) }}
                                </div>
                            @endif
                        </div>
                    </label>

                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" id="job-submit-btn" class="btn-primary" style="width: 100%; padding: 0.9rem; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; gap: 0.6rem; transition: all 0.2s;">
                <span>🚀</span> Publish & Post to Selected Platforms (1-Click)
            </button>
        </form>
    </div>

</div>

<script>
    // Double-submit prevention & loading indicator
    const jobForm = document.getElementById('job-post-form');
    if (jobForm) {
        jobForm.addEventListener('submit', function (e) {
            const btn = document.getElementById('job-submit-btn');
            if (jobForm.dataset.submitting === 'true') {
                e.preventDefault();
                return false;
            }
            jobForm.dataset.submitting = 'true';
            setTimeout(() => {
                btn.disabled = true;
                btn.innerHTML = '<span>⏳</span> Publishing & Distributing to Platforms... Please wait...';
                btn.style.opacity = '0.75';
                btn.style.cursor = 'not-allowed';
            }, 10);
        });
    }

    function toggleAllPlatforms(checked) {
        document.querySelectorAll('.platform-checkbox').forEach(cb => cb.checked = checked);
    }

    function previewJobImage(event) {
        const file = event.target.files[0];
        const previewContainer = document.getElementById('image-preview-container');
        const previewImg = document.getElementById('image-preview');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewContainer.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            removeJobImage();
        }
    }

    function removeJobImage() {
        const input = document.getElementById('job-image-input');
        input.value = '';
        const previewContainer = document.getElementById('image-preview-container');
        previewContainer.style.display = 'none';
        document.getElementById('image-preview').src = '#';
    }
</script>
@endsection
