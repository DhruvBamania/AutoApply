@extends('layouts.app')

@section('content')
<div style="max-width: 880px; margin: 0 auto; display: flex; flex-direction: column; gap: 2rem;">

    <!-- Top Banner -->
    <div class="card" style="background: linear-gradient(135deg, rgba(255,255,255,0.85) 0%, rgba(238,242,255,0.95) 100%); border-left: 6px solid #4c51bf;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1.25rem;">
            <div style="flex: 1; min-width: 280px;">
                <div style="font-size: 0.88rem; color: #4338ca; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span>🏢</span> {{ $job->user->companyProfile->company_name ?? $job->user->name }} is hiring
                </div>
                <h1 style="font-size: 2.1rem; font-weight: 800; color: #1e1b4b; margin: 0 0 0.6rem 0; line-height: 1.25;">
                    {{ $job->title }}
                </h1>
                <div style="display: flex; flex-wrap: wrap; gap: 0.85rem; font-size: 0.9rem; color: #4b5563;">
                    <span class="badge" style="background: #e0e7ff; color: #3730a3; font-weight: 600;">{{ $job->job_type }}</span>
                    @if($job->location) <span>📍 {{ $job->location }}</span> @endif
                    @if($job->salary_range) <span>💰 {{ $job->salary_range }}</span> @endif
                    @if($job->experience_required) <span>⏳ {{ $job->experience_required }}</span> @endif
                    <span>📅 Posted {{ $job->created_at->format('M d, Y') }}</span>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.75rem;">
                <!-- WhatsApp Direct Message Hero Button -->
                <button type="button" onclick="openWhatsAppModal()" class="btn-whatsapp" style="display: inline-flex; align-items: center; gap: 0.6rem; background: linear-gradient(135deg, #25D366 0%, #128C7E 100%); color: white; border: none; padding: 0.75rem 1.35rem; border-radius: 12px; font-weight: 700; font-size: 0.95rem; box-shadow: 0 4px 16px rgba(37,211,102,0.35); cursor: pointer; transition: all 0.2s ease;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.772.82 2.791.82 3.181 0 5.767-2.586 5.768-5.766 0-3.18-2.586-5.766-5.768-5.766zm9.969 5.828c0 5.514-4.486 10-10 10-1.802 0-3.486-.481-4.945-1.321l-5.055 1.321 1.353-4.939c-.896-1.492-1.353-3.21-1.353-5.061 0-5.514 4.486-10 10-10s10 4.486 10 10z"/>
                    </svg>
                    <span>Message on WhatsApp</span>
                </button>

                @if($referralPlatform !== 'direct')
                    <div style="font-size: 0.8rem; background: rgba(99, 102, 241, 0.1); color: #4338ca; padding: 0.35rem 0.75rem; border-radius: 20px; font-weight: 600;">
                        Via {{ ucfirst($referralPlatform) }} Post
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- WhatsApp Instant Direct Hiring Line Card -->
    <div class="card" style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border: 1.5px solid #86efac; border-radius: 16px; padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="width: 50px; height: 50px; border-radius: 14px; background: #25D366; display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 4px 12px rgba(37,211,102,0.3); flex-shrink: 0;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.772.82 2.791.82 3.181 0 5.767-2.586 5.768-5.766 0-3.18-2.586-5.766-5.768-5.766zm9.969 5.828c0 5.514-4.486 10-10 10-1.802 0-3.486-.481-4.945-1.321l-5.055 1.321 1.353-4.939c-.896-1.492-1.353-3.21-1.353-5.061 0-5.514 4.486-10 10-10s10 4.486 10 10z"/>
                </svg>
            </div>
            <div>
                <h4 style="margin: 0 0 0.2rem 0; font-size: 1.05rem; font-weight: 700; color: #14532d;">
                    Direct WhatsApp Hiring Line with {{ $job->user->companyProfile->company_name ?? $job->user->name }}
                </h4>
                <p style="margin: 0; font-size: 0.88rem; color: #166534;">
                    Have questions about the role, salary, or culture? Send an instant message directly to the hiring manager via WhatsApp.
                </p>
            </div>
        </div>
        <button type="button" onclick="openWhatsAppModal()" style="background: #15803d; color: white; border: none; padding: 0.65rem 1.25rem; border-radius: 10px; font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#166534'" onmouseout="this.style.background='#15803d'">
            Start WhatsApp Chat &rarr;
        </button>
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

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;" class="grid-2">
                <div class="form-group">
                    <label class="form-label">Phone / WhatsApp Number <span style="color: #6b7280; font-weight: normal;">(Optional for WhatsApp updates)</span></label>
                    <input type="tel" name="candidate_phone" class="form-control" placeholder="+91 98765 43210" value="{{ old('candidate_phone') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Professional Headline / Current Role / Profile Link</label>
                    <input type="text" name="candidate_headline" class="form-control" placeholder="e.g. Senior Backend Engineer / linkedin.com/in/alex" value="{{ old('candidate_headline') }}">
                </div>
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

<!-- Interactive WhatsApp Candidate Modal -->
<div id="whatsappModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(6px); align-items: center; justify-content: center; padding: 1.5rem;">
    <div style="background: white; border-radius: 20px; max-width: 520px; width: 100%; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; animation: popIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);">
        
        <!-- Modal Header -->
        <div style="background: linear-gradient(135deg, #25D366 0%, #128C7E 100%); padding: 1.5rem; color: white; position: relative;">
            <button type="button" onclick="closeWhatsAppModal()" style="position: absolute; top: 1.25rem; right: 1.25rem; background: rgba(255,255,255,0.2); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1.2rem; line-height: 1;">&times;</button>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="background: rgba(255,255,255,0.25); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.772.82 2.791.82 3.181 0 5.767-2.586 5.768-5.766 0-3.18-2.586-5.766-5.768-5.766zm9.969 5.828c0 5.514-4.486 10-10 10-1.802 0-3.486-.481-4.945-1.321l-5.055 1.321 1.353-4.939c-.896-1.492-1.353-3.21-1.353-5.061 0-5.514 4.486-10 10-10s10 4.486 10 10z"/>
                    </svg>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.2rem; font-weight: 700;">Chat via WhatsApp</h3>
                    <p style="margin: 0.2rem 0 0 0; font-size: 0.85rem; opacity: 0.95;">
                        To: <strong>{{ $job->user->companyProfile->company_name ?? $job->user->name }}</strong> ({{ $job->title }})
                    </p>
                </div>
            </div>
        </div>

        <!-- Modal Body / Form -->
        <div style="padding: 1.5rem;">
            <div id="waSuccessBanner" style="display: none; background: #ecfdf5; border: 1px solid #86efac; border-radius: 12px; padding: 1rem; margin-bottom: 1.25rem; color: #166534; font-size: 0.9rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 700;">
                    <span>✅</span> Message Dispatched Successfully!
                </div>
                <div id="waSuccessText" style="margin-top: 0.35rem; font-size: 0.85rem;"></div>
            </div>

            <div id="waErrorBanner" style="display: none; background: #fef2f2; border: 1px solid #fca5a5; border-radius: 12px; padding: 1rem; margin-bottom: 1.25rem; color: #991b1b; font-size: 0.88rem;"></div>

            <form id="waForm" onsubmit="submitWhatsAppMessage(event)">
                @csrf
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; margin-bottom: 0.85rem;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.35rem;">
                            Your Name <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" id="waCandidateName" name="candidate_name" required placeholder="Alex Johnson" class="form-control" style="font-size: 0.9rem; padding: 0.6rem 0.85rem;" value="{{ auth()->user()?->name ?? '' }}">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.35rem;">
                            Your WhatsApp Phone <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="tel" id="waCandidatePhone" name="candidate_phone" required placeholder="+91 98765 43210" class="form-control" style="font-size: 0.9rem; padding: 0.6rem 0.85rem;">
                    </div>
                </div>

                <div style="margin-bottom: 0.85rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.35rem;">
                        Your Email <span style="color: #6b7280; font-weight: normal;">(Optional)</span>
                    </label>
                    <input type="email" id="waCandidateEmail" name="candidate_email" placeholder="alex@example.com" class="form-control" style="font-size: 0.9rem; padding: 0.6rem 0.85rem;" value="{{ auth()->user()?->email ?? '' }}">
                </div>

                <!-- Quick Query Chips -->
                <div style="margin-bottom: 0.75rem;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #6b7280; margin-bottom: 0.35rem;">
                        Quick Questions:
                    </label>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                        <button type="button" onclick="setQuickMsg('Hi, what is the expected salary range and perks for this role?')" style="background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 20px; padding: 0.25rem 0.65rem; font-size: 0.78rem; cursor: pointer; color: #374151;">
                            💰 Salary range?
                        </button>
                        <button type="button" onclick="setQuickMsg('Hello, is this role open to remote or hybrid candidates?')" style="background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 20px; padding: 0.25rem 0.65rem; font-size: 0.78rem; cursor: pointer; color: #374151;">
                            🏡 Remote/Hybrid?
                        </button>
                        <button type="button" onclick="setQuickMsg('Hi team, what is the typical interview process and timeline for this position?')" style="background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 20px; padding: 0.25rem 0.65rem; font-size: 0.78rem; cursor: pointer; color: #374151;">
                            ⏱️ Interview timeline?
                        </button>
                    </div>
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.35rem;">
                        Your Message <span style="color: #ef4444;">*</span>
                    </label>
                    <textarea id="waMessage" name="message" required rows="3" placeholder="Type your message to the hiring team..." class="form-control" style="font-size: 0.9rem; padding: 0.6rem 0.85rem; resize: vertical;"></textarea>
                </div>

                <button type="submit" id="waSubmitBtn" style="width: 100%; background: #25D366; color: white; border: none; padding: 0.85rem; border-radius: 12px; font-weight: 700; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; box-shadow: 0 4px 14px rgba(37,211,102,0.3); transition: background 0.2s;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                    </svg>
                    <span>Send WhatsApp Message</span>
                </button>

                <!-- Direct WhatsApp Deep Link Alternative -->
                @php
                    $targetNumber = preg_replace('/[^0-9]/', '', $job->whatsapp_number ?: ($job->user->companyProfile->whatsapp_number ?? '919876543210'));
                    $prefilledText = urlencode("Hello! I am inquiring about the {$job->title} position posted on AI Looping.");
                @endphp
                <div style="text-align: center; margin-top: 1rem; font-size: 0.82rem; color: #6b7280;">
                    Or open in app directly:
                    <a href="https://wa.me/{{ $targetNumber }}?text={{ $prefilledText }}" target="_blank" rel="noopener" style="color: #128C7E; font-weight: 600; text-decoration: underline;">
                        Open WhatsApp Web/App &rarr;
                    </a>
                </div>
            </form>
        </div>

    </div>
</div>

<style>
@keyframes popIn {
    0% { transform: scale(0.95); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}
.btn-whatsapp:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(37,211,102,0.45) !important;
}
</style>

<script>
function openWhatsAppModal() {
    const modal = document.getElementById('whatsappModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeWhatsAppModal() {
    const modal = document.getElementById('whatsappModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function setQuickMsg(text) {
    const textarea = document.getElementById('waMessage');
    if (textarea) {
        textarea.value = text;
        textarea.focus();
    }
}

// Close modal on backdrop click
window.addEventListener('click', function(e) {
    const modal = document.getElementById('whatsappModal');
    if (e.target === modal) {
        closeWhatsAppModal();
    }
});

async function submitWhatsAppMessage(e) {
    e.preventDefault();
    const btn = document.getElementById('waSubmitBtn');
    const errBanner = document.getElementById('waErrorBanner');
    const succBanner = document.getElementById('waSuccessBanner');
    const succText = document.getElementById('waSuccessText');

    errBanner.style.display = 'none';
    succBanner.style.display = 'none';

    const originalBtnHtml = btn.innerHTML;
    btn.disabled = true;
    btn.style.opacity = '0.7';
    btn.innerHTML = '<span>⏳ Sending WhatsApp Message...</span>';

    const payload = {
        candidate_name: document.getElementById('waCandidateName').value.trim(),
        candidate_phone: document.getElementById('waCandidatePhone').value.trim(),
        candidate_email: document.getElementById('waCandidateEmail').value.trim(),
        message: document.getElementById('waMessage').value.trim(),
        _token: '{{ csrf_token() }}'
    };

    try {
        const response = await fetch("{{ route('jobs.public.whatsapp', $job->id) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (response.ok && data.success) {
            succText.innerText = data.message || 'WhatsApp message successfully delivered to the company!';
            succBanner.style.display = 'block';
            document.getElementById('waMessage').value = '';

            setTimeout(() => {
                closeWhatsAppModal();
            }, 3500);
        } else {
            errBanner.innerText = data.error || data.message || 'Unable to deliver WhatsApp message. Please check the phone number.';
            errBanner.style.display = 'block';
        }
    } catch (err) {
        errBanner.innerText = 'Network error occurred while sending WhatsApp message. Please try again.';
        errBanner.style.display = 'block';
    } finally {
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.innerHTML = originalBtnHtml;
    }
}
</script>
@endsection
