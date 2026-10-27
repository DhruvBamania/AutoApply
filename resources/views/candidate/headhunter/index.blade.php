@extends('layouts.app')

@section('content')
<div style="max-width: 1000px; width: 100%; margin: 0 auto; display: flex; flex-direction: column; gap: 2rem;">

    <!-- Page Header Card -->
    <div class="card" style="background: linear-gradient(135deg, rgba(255,255,255,0.85) 0%, rgba(240,249,255,0.95) 100%); border-left: 6px solid #0284c7;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.4rem;">
                    <span style="font-size: 1.8rem;">🎯</span>
                    <h1 style="font-size: 1.8rem; font-weight: 800; color: #0c4a6e; margin: 0;">
                        Scouted by Employers
                    </h1>
                    <span class="badge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc; font-weight: 700;">
                        AI Headhunter Invitations
                    </span>
                </div>
                <p style="font-size: 0.95rem; color: #334155; margin: 0; max-width: 680px; line-height: 1.5;">
                    Autonomous AI Headhunters scouted your tech profile and sent direct invitations for tailored career opportunities. Accept invitations to instantly connect and jump directly into the employer's shortlist pipeline.
                </p>
            </div>
            <div>
                <a href="{{ route('interview_prep.index') }}" class="btn-secondary" style="font-size: 0.85rem; padding: 0.5rem 1rem;">
                    🎯 Prepare for Interviews →
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats Bar -->
    @php
        $totalInvites = $invitations->count();
        $pendingInvites = $invitations->where('status', 'invited')->count();
        $acceptedInvites = $invitations->where('status', 'accepted')->count();
    @endphp
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 200px), 1fr)); gap: 1rem;">
        <div class="card" style="padding: 1.25rem; background: white; border: 1px solid #bae6fd;">
            <div style="font-size: 0.78rem; font-weight: 700; color: #0369a1; text-transform: uppercase; letter-spacing: 0.04em;">Total Scouted Invitations</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #0c4a6e; margin-top: 0.25rem;">{{ $totalInvites }}</div>
        </div>

        <div class="card" style="padding: 1.25rem; background: white; border: 1px solid #fde68a;">
            <div style="font-size: 0.78rem; font-weight: 700; color: #b45309; text-transform: uppercase; letter-spacing: 0.04em;">Pending Review</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #d97706; margin-top: 0.25rem;">{{ $pendingInvites }}</div>
        </div>

        <div class="card" style="padding: 1.25rem; background: white; border: 1px solid #bbf7d0;">
            <div style="font-size: 0.78rem; font-weight: 700; color: #15803d; text-transform: uppercase; letter-spacing: 0.04em;">Accepted & Connected</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #16a34a; margin-top: 0.25rem;">{{ $acceptedInvites }}</div>
        </div>
    </div>

    <!-- Invitations List -->
    @if($invitations->isEmpty())
        <div class="card" style="text-align: center; padding: 3.5rem 1.5rem; background: white; border-radius: 16px; border: 1.5px dashed #cbd5e1;">
            <div style="font-size: 3.5rem; margin-bottom: 0.75rem;">📬</div>
            <h3 style="font-size: 1.3rem; font-weight: 700; color: #1e1b4b; margin-bottom: 0.5rem;">
                No Headhunter Invitations Yet
            </h3>
            <p style="color: #64748b; font-size: 0.95rem; max-width: 520px; margin: 0 auto 1.5rem auto; line-height: 1.55;">
                Companies use our Autonomous AI Headhunter Agent to discover top developer profiles. Upload your latest PDF resume or optimize your skills to increase your visibility to hiring companies.
            </p>
            <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap;">
                <a href="{{ route('dashboard') }}" class="btn-primary" style="padding: 0.65rem 1.4rem;">
                    📄 Upload / Update Resume
                </a>
                <a href="{{ route('resumes.tailor') }}" class="btn-secondary" style="padding: 0.65rem 1.4rem;">
                    ✨ AI Resume Tailor
                </a>
            </div>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            @foreach($invitations as $match)
                @php
                    $job = $match->jobPosting;
                    $company = $job?->user;
                    $scoreBadgeBg = $match->ai_match_score >= 80 ? '#dcfce7' : ($match->ai_match_score >= 60 ? '#e0f2fe' : '#fef3c7');
                    $scoreBadgeColor = $match->ai_match_score >= 80 ? '#15803d' : ($match->ai_match_score >= 60 ? '#0369a1' : '#b45309');
                    $scoreBorderColor = $match->ai_match_score >= 80 ? '#bbf7d0' : ($match->ai_match_score >= 60 ? '#bae6fd' : '#fde68a');
                @endphp

                <div class="card" style="background: white; border: {{ $match->status === 'invited' ? '2px solid #38bdf8' : '1px solid #e2e8f0' }}; border-radius: 16px; padding: 1.75rem; box-shadow: {{ $match->status === 'invited' ? '0 8px 24px rgba(56,189,248,0.15)' : '0 2px 10px rgba(0,0,0,0.03)' }}; position: relative;">
                    
                    <!-- Card Top Header -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
                                <h2 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0;">
                                    {{ $job ? $job->title : 'Opportunity Scouted' }}
                                </h2>
                                
                                @if($match->status === 'accepted')
                                    <span class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-weight: 700; font-size: 0.75rem;">
                                        ✅ Connected & Shortlisted
                                    </span>
                                @elseif($match->status === 'declined')
                                    <span class="badge" style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; font-weight: 700; font-size: 0.75rem;">
                                        ✕ Declined
                                    </span>
                                @else
                                    <span class="badge" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-weight: 700; font-size: 0.75rem; animation: pulse 2s infinite;">
                                        🌟 New Invitation
                                    </span>
                                @endif
                            </div>

                            <div style="font-size: 0.95rem; font-weight: 600; color: #0284c7; margin-top: 0.25rem;">
                                🏢 {{ $company?->name ?? 'Confidential Tech Employer' }}
                            </div>

                            <div style="display: flex; flex-wrap: wrap; gap: 1rem; font-size: 0.85rem; color: #64748b; margin-top: 0.35rem;">
                                @if($job?->location) <span>📍 {{ $job->location }}</span> @endif
                                @if($job?->job_type) <span>💼 {{ $job->job_type }}</span> @endif
                                @if($job?->salary_range) <span>💰 {{ $job->salary_range }}</span> @endif
                                @if($match->invited_at) <span>✉️ Invited {{ $match->invited_at->diffForHumans() }}</span> @endif
                            </div>
                        </div>

                        <!-- Match Affinity Badge -->
                        <div style="background: {{ $scoreBadgeBg }}; color: {{ $scoreBadgeColor }}; border: 1.5px solid {{ $scoreBorderColor }}; padding: 0.45rem 1rem; border-radius: 20px; font-weight: 800; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                            <span>🎯</span> {{ $match->ai_match_score }}% Match
                        </div>
                    </div>

                    <!-- Personalized Outreach Pitch Box -->
                    <div style="background: linear-gradient(135deg, #f0fdf4 0%, #f8fafc 100%); border: 1px solid #bbf7d0; border-left: 4px solid #16a34a; border-radius: 12px; padding: 1.25rem; margin-bottom: 1.25rem;">
                        <div style="font-size: 0.82rem; font-weight: 700; text-transform: uppercase; color: #166534; letter-spacing: 0.04em; margin-bottom: 0.4rem; display: flex; align-items: center; gap: 0.4rem;">
                            <span>✉️</span> Recruiter's Personalized Pitch
                        </div>
                        <div style="font-size: 0.92rem; color: #1e293b; line-height: 1.6; white-space: pre-wrap;">
{{ $match->personalized_pitch }}
                        </div>
                    </div>

                    <!-- Skills & Match Reasoning -->
                    @if(!empty($match->skills) && is_array($match->skills))
                        <div style="margin-bottom: 1rem;">
                            <span style="font-size: 0.8rem; font-weight: 700; color: #475569; display: block; margin-bottom: 0.35rem;">Key Matched Skills:</span>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                                @foreach($match->skills as $skill)
                                    <span style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; padding: 0.2rem 0.65rem; border-radius: 12px; font-size: 0.78rem; font-weight: 600;">
                                        {{ $skill }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($match->ai_reasoning)
                        <div style="background: #f8fafc; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.85rem; color: #475569; margin-bottom: 1.25rem; border: 1px solid #e2e8f0;">
                            <strong style="color: #0f172a;">Why AI scouted you:</strong> {{ $match->ai_reasoning }}
                        </div>
                    @endif

                    <!-- Action Controls -->
                    @if($match->status === 'invited')
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; padding-top: 1rem; border-top: 1px solid #f1f5f9;">
                            <div style="font-size: 0.82rem; color: #64748b;">
                                💡 Accepting links your profile into the hiring manager's shortlist pipeline.
                            </div>

                            <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                                @if($job)
                                    <a href="{{ route('jobs.public.show', $job->id) }}" target="_blank" class="btn" style="background: #f0fdf4; border: 1.5px solid #25D366; color: #15803d; font-size: 0.85rem; font-weight: 700; padding: 0.5rem 0.9rem; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="#25D366"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.772.82 2.791.82 3.181 0 5.767-2.586 5.768-5.766 0-3.18-2.586-5.766-5.768-5.766zm9.969 5.828c0 5.514-4.486 10-10 10-1.802 0-3.486-.481-4.945-1.321l-5.055 1.321 1.353-4.939c-.896-1.492-1.353-3.21-1.353-5.061 0-5.514 4.486-10 10-10s10 4.486 10 10z"/></svg>
                                        <span>Message on WhatsApp</span>
                                    </a>
                                @endif

                                <!-- Decline Form -->
                                <form action="{{ route('candidate.headhunter.respond', $match->id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to decline this invitation?');">
                                    @csrf
                                    <input type="hidden" name="action" value="declined">
                                    <button type="submit" class="btn" style="background: transparent; border: 1px solid #cbd5e1; color: #64748b; font-size: 0.85rem; padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer;">
                                        ✕ Decline
                                    </button>
                                </form>

                                <!-- Accept Form -->
                                <form action="{{ route('candidate.headhunter.respond', $match->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <input type="hidden" name="action" value="accepted">
                                    <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); font-size: 0.88rem; font-weight: 700; padding: 0.55rem 1.4rem; border: none; box-shadow: 0 4px 12px rgba(22,163,74,0.3); border-radius: 8px; cursor: pointer;">
                                        🤝 Accept & Connect
                                    </button>
                                </form>
                            </div>
                        </div>
                    @elseif($match->status === 'accepted')
                        <div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 0.85rem 1.25rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
                            <div style="color: #15803d; font-size: 0.88rem; font-weight: 600;">
                                🎉 You accepted this invitation on {{ $match->responded_at ? $match->responded_at->format('M d, Y') : 'recently' }}. Your profile is shortlisted with {{ $company?->name ?? 'the employer' }}.
                            </div>
                            <a href="{{ route('interview_prep.index') }}" class="btn" style="background: #15803d; color: white; font-size: 0.8rem; padding: 0.35rem 0.85rem; border-radius: 6px; text-decoration: none;">
                                Practice Questions for this Role →
                            </a>
                        </div>
                    @else
                        <div style="color: #94a3b8; font-size: 0.85rem;">
                            You declined this invitation on {{ $match->responded_at ? $match->responded_at->format('M d, Y') : 'recently' }}.
                        </div>
                    @endif

                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
