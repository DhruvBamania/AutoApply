@extends('layouts.app')

@section('content')
<div style="max-width: 900px; width: 100%; margin: 0 auto; display: flex; flex-direction: column; gap: 2rem;">

    <!-- Top Navigation -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <a href="{{ route('company.jobs.index') }}" style="color: #4c51bf; font-weight: 600; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.3rem;">
            ← Back to All Job Postings
        </a>
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <!-- 1-Click Re-sync -->
            <form action="{{ route('company.jobs.repost', $job->id) }}" method="POST" style="margin: 0;">
                @csrf
                <input type="hidden" name="platforms[]" value="linkedin">
                <input type="hidden" name="platforms[]" value="naukri">
                <input type="hidden" name="platforms[]" value="indeed">
                <button type="submit" class="btn" style="background: #3b82f6; color: white; padding: 0.5rem 1rem; font-size: 0.85rem;">
                    🔄 1-Click Re-sync Platforms
                </button>
            </form>

            <form action="{{ route('company.jobs.destroy', $job->id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete this job posting?');">
                @csrf
                @method('DELETE')
                <button type="submit" style="background: transparent; border: 1px solid #f87171; color: #dc2626; padding: 0.5rem 1rem; border-radius: 12px; cursor: pointer; font-size: 0.85rem; font-weight: 600;">
                    Delete
                </button>
            </form>
        </div>
    </div>

    <!-- Job Header Card -->
    <div class="card" style="background: linear-gradient(135deg, rgba(255,255,255,0.7) 0%, rgba(238,242,255,0.85) 100%); border-left: 6px solid #4c51bf;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                    <h1 style="font-size: 1.8rem; font-weight: 700; color: #1e1b4b; margin: 0;">
                        {{ $job->title }}
                    </h1>
                    <span class="badge" style="background: #e0e7ff; color: #3730a3;">
                        {{ $job->job_type }}
                    </span>
                    <span class="badge badge-running">Active</span>
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 1.25rem; font-size: 0.9rem; color: #4b5563;">
                    @if($job->location) <span>📍 {{ $job->location }}</span> @endif
                    @if($job->salary_range) <span>💰 {{ $job->salary_range }}</span> @endif
                    @if($job->experience_required) <span>⏳ {{ $job->experience_required }}</span> @endif
                    <span>📅 Created {{ $job->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Platform Distribution Status Banner -->
    <div class="card">
        <div class="card-title" style="font-size: 1.15rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem;">
            🚀 Multi-Platform Distribution Status
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 220px), 1fr)); gap: 1rem;">
            
            <!-- LinkedIn -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <div style="font-weight: 700; color: #0077b5; display: flex; align-items: center; gap: 0.4rem;">
                        <span>💼</span> LinkedIn
                    </div>
                    @if($job->linkedin_status === 'posted')
                        <span class="badge" style="background: #0077b5; color: white;">Live ✓</span>
                    @elseif($job->linkedin_status === 'failed')
                        <span class="badge" style="background: #fee2e2; color: #991b1b;">Failed ✗</span>
                    @else
                        <span class="badge" style="background: #f3f4f6; color: #6b7280;">Inactive</span>
                    @endif
                </div>
                <div style="font-size: 0.8rem; color: #64748b;">
                    @if($job->linkedin_posted_at)
                        Dispatched: {{ $job->linkedin_posted_at->diffForHumans() }}
                    @else
                        Status: {{ ucfirst($job->linkedin_status) }}
                    @endif
                </div>
                @if($job->linkedin_error)
                    <div style="font-size: 0.75rem; color: #b91c1c; margin-top: 0.5rem; background: #fee2e2; padding: 0.4rem 0.6rem; border-radius: 6px; word-break: break-word;">
                        ⚠️ {{ $job->linkedin_error }}
                    </div>
                @endif
            </div>

            <!-- Naukri -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <div style="font-weight: 700; color: #2563eb; display: flex; align-items: center; gap: 0.4rem;">
                        <span>🔵</span> Naukri.com
                    </div>
                    @if($job->naukri_status === 'posted')
                        <span class="badge" style="background: #2563eb; color: white;">Live ✓</span>
                    @elseif($job->naukri_status === 'failed')
                        <span class="badge" style="background: #fee2e2; color: #991b1b;">Failed ✗</span>
                    @else
                        <span class="badge" style="background: #f3f4f6; color: #6b7280;">Inactive</span>
                    @endif
                </div>
                <div style="font-size: 0.8rem; color: #64748b;">
                    @if($job->naukri_posted_at)
                        Dispatched: {{ $job->naukri_posted_at->diffForHumans() }}
                    @else
                        Status: {{ ucfirst($job->naukri_status) }}
                    @endif
                </div>
                @if($job->naukri_error)
                    <div style="font-size: 0.75rem; color: #b91c1c; margin-top: 0.5rem; background: #fee2e2; padding: 0.4rem 0.6rem; border-radius: 6px; word-break: break-word;">
                        ⚠️ {{ $job->naukri_error }}
                    </div>
                @endif
            </div>

            <!-- Indeed -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <div style="font-weight: 700; color: #0a66c2; display: flex; align-items: center; gap: 0.4rem;">
                        <span>🔷</span> Indeed
                    </div>
                    @if($job->indeed_status === 'posted')
                        <span class="badge" style="background: #0a66c2; color: white;">Live ✓</span>
                    @elseif($job->indeed_status === 'failed')
                        <span class="badge" style="background: #fee2e2; color: #991b1b;">Failed ✗</span>
                    @else
                        <span class="badge" style="background: #f3f4f6; color: #6b7280;">Inactive</span>
                    @endif
                </div>
                <div style="font-size: 0.8rem; color: #64748b;">
                    @if($job->indeed_posted_at)
                        Dispatched: {{ $job->indeed_posted_at->diffForHumans() }}
                    @else
                        Status: {{ ucfirst($job->indeed_status) }}
                    @endif
                </div>
                @if($job->indeed_error)
                    <div style="font-size: 0.75rem; color: #b91c1c; margin-top: 0.5rem; background: #fee2e2; padding: 0.4rem 0.6rem; border-radius: 6px; word-break: break-word;">
                        ⚠️ {{ $job->indeed_error }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    @if($job->image_path)
        <!-- Attached Hiring Graphic / Banner -->
        <div class="card" style="padding: 1.25rem;">
            <div style="font-size: 1.05rem; font-weight: 700; color: #1f2937; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem;">
                <span>🖼️</span> Attached Hiring Flyer / Banner Graphic
            </div>
            <div style="background: #f8fafc; border-radius: 14px; overflow: hidden; display: flex; justify-content: center; align-items: center; border: 1px solid #e2e8f0; padding: 0.5rem;">
                <img src="{{ asset('storage/' . $job->image_path) }}" alt="{{ $job->title }} Flyer" style="max-width: 100%; max-height: 440px; object-fit: contain; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.04);">
            </div>
        </div>
    @endif

    <!-- Job Description & Required Skills -->
    <div class="card">
        @if($job->skills)
            <div style="margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.05rem; font-weight: 700; color: #1f2937; margin-bottom: 0.6rem;">Required Skills</h3>
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
            <h3 style="font-size: 1.05rem; font-weight: 700; color: #1f2937; margin-bottom: 0.6rem;">Job Description</h3>
            <div style="background: rgba(255,255,255,0.7); border: 1px solid rgba(229,231,235,0.9); padding: 1.5rem; border-radius: 14px; white-space: pre-wrap; line-height: 1.6; color: #374151; font-size: 0.95rem;">
                {{ $job->description }}
            </div>
        </div>
    </div>

    <!-- 🤖 Gemini AI Candidate Match & Rank Section -->
    <div class="card" id="ai-matching-section" style="border: 2px solid #6366f1; background: linear-gradient(135deg, rgba(255,255,255,0.98) 0%, rgba(245,243,255,0.96) 100%); box-shadow: 0 10px 30px rgba(99,102,241,0.08);">
        
        <!-- AI Header Banner -->
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; border-bottom: 1px solid #e0e7ff; padding-bottom: 1.25rem; margin-bottom: 1.25rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
                    <span style="font-size: 1.6rem;">🤖</span>
                    <h2 style="font-size: 1.35rem; font-weight: 800; color: #1e1b4b; margin: 0;">
                        AI Candidate Match & Rank
                    </h2>
                </div>
                <p style="font-size: 0.88rem; color: #4b5563; margin: 0.35rem 0 0 0;">
                    AI evaluates candidate resumes, experience depth, projects, and key skills to rank top talent for <strong>{{ $job->title }}</strong>.
                </p>
            </div>

            <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                @if($topMatchesCount > 0)
                    <form action="{{ route('company.jobs.ai_shortlist', $job->id) }}" method="POST" style="margin: 0;">
                        @csrf
                        <input type="hidden" name="threshold" value="75">
                        <button type="submit" class="btn" style="display: inline-flex; align-items: center; gap: 0.45rem; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-weight: 700; font-size: 0.85rem; padding: 0.55rem 1.1rem; border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                            <span>⭐</span> Shortlist Top Matches (≥75%)
                        </button>
                    </form>
                @endif

                <form action="{{ route('company.jobs.ai_match', $job->id) }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); font-weight: 700; font-size: 0.9rem; padding: 0.6rem 1.3rem; border: none; box-shadow: 0 4px 15px rgba(79,70,229,0.3); border-radius: 8px; cursor: pointer;">
                        <span>✨</span> {{ $rankedCandidates->isNotEmpty() ? 'Re-Analyze with AI' : 'Analyze & Rank with AI' }}
                    </button>
                </form>
            </div>
        </div>

        <!-- AI Analytics Bar -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 180px), 1fr)); gap: 0.75rem; margin-bottom: 1.5rem;">
            <div style="background: white; border: 1px solid #e0e7ff; border-radius: 10px; padding: 0.85rem 1rem;">
                <div style="font-size: 0.75rem; color: #6b7280; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;">Analyzed Candidates</div>
                <div style="font-size: 1.4rem; font-weight: 800; color: #1e1b4b; margin-top: 0.2rem;">
                    {{ $rankedCandidates->count() }} <span style="font-size: 0.85rem; font-weight: 500; color: #6b7280;">/ {{ $inquiries->count() }}</span>
                </div>
            </div>

            <div style="background: white; border: 1px solid #bbf7d0; border-radius: 10px; padding: 0.85rem 1rem;">
                <div style="font-size: 0.75rem; color: #166534; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;">🌟 Top Matches (≥80%)</div>
                <div style="font-size: 1.4rem; font-weight: 800; color: #15803d; margin-top: 0.2rem;">{{ $topMatchesCount }}</div>
            </div>

            <div style="background: white; border: 1px solid #e0e7ff; border-radius: 10px; padding: 0.85rem 1rem;">
                <div style="font-size: 0.75rem; color: #3730a3; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;">⚡ Potential (60-79%)</div>
                <div style="font-size: 1.4rem; font-weight: 800; color: #4338ca; margin-top: 0.2rem;">{{ $potentialMatchesCount }}</div>
            </div>

            <div style="background: white; border: 1px solid #fef08a; border-radius: 10px; padding: 0.85rem 1rem;">
                <div style="font-size: 0.75rem; color: #854d0e; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;">⭐ Shortlisted</div>
                <div style="font-size: 1.4rem; font-weight: 800; color: #a16207; margin-top: 0.2rem;">{{ $shortlistedCount }}</div>
            </div>
        </div>

        @if($rankedCandidates->isEmpty())
            <!-- Empty State -->
            <div style="text-align: center; padding: 3rem 1.5rem; background: white; border-radius: 14px; border: 1px dashed #cbd5e1;">
                <div style="font-size: 3rem; margin-bottom: 0.75rem;">🤖</div>
                <h3 style="font-size: 1.2rem; font-weight: 700; color: #1e1b4b; margin-bottom: 0.4rem;">
                    Resumes Not Yet Evaluated
                </h3>
                <p style="color: #64748b; font-size: 0.9rem; max-width: 520px; margin: 0 auto 1.5rem auto; line-height: 1.5;">
                    Gemini AI will read each candidate's uploaded resume (PDF, Word, or direct profile), compare skills, score experience, evaluate projects, and produce a ranked leaderboard.
                </p>

                <form action="{{ route('company.jobs.ai_match', $job->id) }}" method="POST" style="display: inline-block;">
                    @csrf
                    <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); font-size: 0.95rem; font-weight: 700; padding: 0.75rem 1.8rem;">
                        ✨ Start AI Candidate Ranking Now
                    </button>
                </form>
            </div>
        @else
            <!-- Ranked Leaderboard -->
            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                @foreach($rankedCandidates as $candidate)
                    @php
                        $isTopPick = $candidate->ai_rank === 1;
                        $isTopThree = in_array($candidate->ai_rank, [1, 2, 3]);
                        
                        $rankBadgeBg = match($candidate->ai_rank) {
                            1 => 'linear-gradient(135deg, #fef08a 0%, #facc15 100%)',
                            2 => 'linear-gradient(135deg, #f1f5f9 0%, #cbd5e1 100%)',
                            3 => 'linear-gradient(135deg, #fed7aa 0%, #fb923c 100%)',
                            default => '#f3f4f6'
                        };
                        $rankBadgeColor = match($candidate->ai_rank) {
                            1 => '#713f12',
                            2 => '#334155',
                            3 => '#7c2d12',
                            default => '#4b5563'
                        };
                        $rankIcon = match($candidate->ai_rank) {
                            1 => '🥇 Rank #1',
                            2 => '🥈 Rank #2',
                            3 => '🥉 Rank #3',
                            default => '🎖️ Rank #' . $candidate->ai_rank
                        };

                        $scoreColor = $candidate->ai_match_score >= 80 ? '#16a34a' : ($candidate->ai_match_score >= 60 ? '#4f46e5' : '#ea580c');
                    @endphp

                    <div style="background: white; border: {{ $isTopPick ? '2px solid #f59e0b' : '1px solid #e2e8f0' }}; border-radius: 14px; padding: 1.35rem; box-shadow: {{ $isTopPick ? '0 6px 20px rgba(245,158,11,0.15)' : '0 2px 10px rgba(0,0,0,0.03)' }}; position: relative; transition: all 0.2s ease;">
                        
                        <!-- Top Info Bar -->
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
                            <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                                <!-- Rank Badge -->
                                <span style="background: {{ $rankBadgeBg }}; color: {{ $rankBadgeColor }}; font-weight: 800; font-size: 0.85rem; padding: 0.35rem 0.85rem; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: inline-flex; align-items: center; gap: 0.3rem;">
                                    {{ $rankIcon }}
                                    @if($isTopPick)
                                        <span style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; background: rgba(0,0,0,0.1); padding: 0.1rem 0.4rem; border-radius: 4px;">Top Pick</span>
                                    @endif
                                </span>

                                <div>
                                    <h3 style="font-size: 1.15rem; font-weight: 800; color: #1e1b4b; margin: 0; display: inline-flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                        {{ $candidate->candidate_name }}
                                        @if($candidate->is_pinned)
                                            <span class="badge" style="background: #ede9fe; color: #6d28d9; border: 1px solid #c4b5fd; font-size: 0.7rem;">📌 Pinned</span>
                                        @endif
                                    </h3>
                                    @if($candidate->candidate_headline)
                                        <div style="font-size: 0.83rem; color: #64748b; margin-top: 0.15rem;">
                                            {{ $candidate->candidate_headline }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Match Score Meter & Recommendation Badge -->
                            <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                                <div style="text-align: right;">
                                    <div style="font-size: 1.35rem; font-weight: 800; color: {{ $scoreColor }};">
                                        {{ $candidate->ai_match_score }}%
                                    </div>
                                    <div style="font-size: 0.72rem; color: #6b7280; font-weight: 600; text-transform: uppercase;">Overall Fit</div>
                                </div>

                                <span class="badge" style="background: {{ $candidate->ai_match_score >= 80 ? '#dcfce7' : ($candidate->ai_match_score >= 60 ? '#e0e7ff' : '#fef3c7') }}; color: {{ $candidate->ai_match_score >= 80 ? '#15803d' : ($candidate->ai_match_score >= 60 ? '#4338ca' : '#b45309') }}; border: 1px solid {{ $candidate->ai_match_score >= 80 ? '#bbf7d0' : ($candidate->ai_match_score >= 60 ? '#c7d2fe' : '#fde68a') }}; font-weight: 700; font-size: 0.8rem; padding: 0.35rem 0.75rem; border-radius: 8px;">
                                    {{ $candidate->ai_recommendation ?: 'Match' }}
                                </span>
                            </div>
                        </div>

                        <!-- 3-Metric Breakdown Bar -->
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 0.85rem 1rem; margin-bottom: 1rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 200px), 1fr)); gap: 1rem;">
                            <!-- Experience Score -->
                            <div>
                                <div style="display: flex; justify-content: space-between; font-size: 0.78rem; font-weight: 600; color: #475569; margin-bottom: 0.3rem;">
                                    <span>💼 Experience Alignment</span>
                                    <span style="color: #1e1b4b; font-weight: 700;">{{ $candidate->ai_experience_score ?? $candidate->ai_match_score }}%</span>
                                </div>
                                <div style="background: #e2e8f0; border-radius: 999px; height: 6px; overflow: hidden;">
                                    <div style="background: #4f46e5; height: 100%; width: {{ $candidate->ai_experience_score ?? $candidate->ai_match_score }}%; border-radius: 999px;"></div>
                                </div>
                            </div>

                            <!-- Skills Score -->
                            <div>
                                <div style="display: flex; justify-content: space-between; font-size: 0.78rem; font-weight: 600; color: #475569; margin-bottom: 0.3rem;">
                                    <span>🛠️ Key Skills Match</span>
                                    <span style="color: #1e1b4b; font-weight: 700;">{{ $candidate->ai_skills_score ?? $candidate->ai_match_score }}%</span>
                                </div>
                                <div style="background: #e2e8f0; border-radius: 999px; height: 6px; overflow: hidden;">
                                    <div style="background: #0ea5e9; height: 100%; width: {{ $candidate->ai_skills_score ?? $candidate->ai_match_score }}%; border-radius: 999px;"></div>
                                </div>
                            </div>

                            <!-- Projects Score -->
                            <div>
                                <div style="display: flex; justify-content: space-between; font-size: 0.78rem; font-weight: 600; color: #475569; margin-bottom: 0.3rem;">
                                    <span>🚀 Projects & Impact</span>
                                    <span style="color: #1e1b4b; font-weight: 700;">{{ $candidate->ai_projects_score ?? $candidate->ai_match_score }}%</span>
                                </div>
                                <div style="background: #e2e8f0; border-radius: 999px; height: 6px; overflow: hidden;">
                                    <div style="background: #10b981; height: 100%; width: {{ $candidate->ai_projects_score ?? $candidate->ai_match_score }}%; border-radius: 999px;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- AI Recruiter Rationale / Summary -->
                        @if($candidate->ai_summary)
                            <div style="background: #fdf4ff; border-left: 3px solid #c084fc; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.88rem; color: #581c87; line-height: 1.5; margin-bottom: 1rem;">
                                <strong style="color: #701a75;">💬 Gemini Recruiter Assessment:</strong> {{ $candidate->ai_summary }}
                            </div>
                        @endif

                        <!-- Matched & Missing Skills Tag Chips -->
                        <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1rem;">
                            @if(!empty($candidate->ai_matched_skills))
                                <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                                    <span style="font-size: 0.75rem; font-weight: 700; color: #166534; text-transform: uppercase;">Matched Skills:</span>
                                    @foreach($candidate->ai_matched_skills as $skill)
                                        <span style="background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; font-size: 0.73rem; font-weight: 600; padding: 0.15rem 0.55rem; border-radius: 6px;">
                                            ✓ {{ $skill }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            @if(!empty($candidate->ai_missing_skills))
                                <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                                    <span style="font-size: 0.75rem; font-weight: 700; color: #9a3412; text-transform: uppercase;">Skills Gaps / Needs:</span>
                                    @foreach($candidate->ai_missing_skills as $skill)
                                        <span style="background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; font-size: 0.73rem; font-weight: 600; padding: 0.15rem 0.55rem; border-radius: 6px;">
                                            ⚠ {{ $skill }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Card Footer: Quick Actions -->
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; border-top: 1px solid #f1f5f9; padding-top: 0.85rem;">
                            <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
                                @if($candidate->resume_path)
                                    <a href="{{ asset('storage/' . $candidate->resume_path) }}" target="_blank" style="font-size: 0.8rem; font-weight: 600; color: #4338ca; background: #e0e7ff; padding: 0.3rem 0.7rem; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.3rem;">
                                        <span>📄</span> View Resume ↗
                                    </a>
                                @endif

                                @if($candidate->candidate_email)
                                    <span style="font-size: 0.8rem; color: #64748b;">
                                        ✉️ {{ $candidate->candidate_email }}
                                    </span>
                                @endif
                            </div>

                            <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
                                <!-- Status Changer Form -->
                                <form action="{{ route('company.inquiries.status', $candidate->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" data-prev-status="{{ $candidate->status }}" onchange="handleStatusChange(this, {{ $candidate->id }}, '{{ addslashes($candidate->candidate_name) }}', '{{ addslashes($candidate->candidate_email ?? '') }}', '{{ addslashes($job->title) }}', '{{ $candidate->interview_scheduled_at ? $candidate->interview_scheduled_at->format('Y-m-d\TH:i') : '' }}', '{{ $candidate->meeting_type ?? 'google_meet' }}', '{{ addslashes($candidate->meeting_link_or_location ?? '') }}', '{{ addslashes($candidate->interview_notes ?? '') }}')" style="font-size: 0.75rem; font-weight: 600; padding: 0.3rem 0.65rem; border-radius: 8px; border: 1px solid #cbd5e1; background: 
                                        {{ $candidate->status === 'new' ? '#fef3c7' : ($candidate->status === 'replied' ? '#dcfce7' : ($candidate->status === 'shortlisted' ? '#fef9c3' : '#f1f5f9')) }};">
                                        <option value="new" {{ $candidate->status === 'new' ? 'selected' : '' }}>🟡 New</option>
                                        <option value="under_review" {{ $candidate->status === 'under_review' ? 'selected' : '' }}>🔵 Under Review</option>
                                        <option value="replied" {{ $candidate->status === 'replied' ? 'selected' : '' }}>🟢 Replied</option>
                                        <option value="shortlisted" {{ $candidate->status === 'shortlisted' ? 'selected' : '' }}>⭐ Shortlisted</option>
                                        <option value="rejected" {{ $candidate->status === 'rejected' ? 'selected' : '' }}>❌ Rejected</option>
                                    </select>
                                </form>

                                @if($candidate->status !== 'shortlisted')
                                    <button type="button" onclick="openShortlistModal({{ $candidate->id }}, '{{ addslashes($candidate->candidate_name) }}', '{{ addslashes($candidate->candidate_email ?? '') }}', '{{ addslashes($job->title) }}')" class="btn" style="background: #fef08a; color: #854d0e; border: 1px solid #fde047; font-size: 0.75rem; font-weight: 700; padding: 0.3rem 0.75rem; border-radius: 8px; cursor: pointer;">
                                        ⭐ Shortlist
                                    </button>
                                @else
                                    <button type="button" onclick="openShortlistModal({{ $candidate->id }}, '{{ addslashes($candidate->candidate_name) }}', '{{ addslashes($candidate->candidate_email ?? '') }}', '{{ addslashes($job->title) }}', '{{ $candidate->interview_scheduled_at ? $candidate->interview_scheduled_at->format('Y-m-d\TH:i') : '' }}', '{{ $candidate->meeting_type ?? 'google_meet' }}', '{{ addslashes($candidate->meeting_link_or_location ?? '') }}', '{{ addslashes($candidate->interview_notes ?? '') }}')" class="btn" style="background: #ede9fe; color: #6d28d9; border: 1px solid #c4b5fd; font-size: 0.75rem; font-weight: 700; padding: 0.3rem 0.75rem; border-radius: 8px; cursor: pointer;">
                                        📅 Interview Info
                                    </button>
                                @endif
                            </div>
                        </div>

                        @if($candidate->status === 'shortlisted' && $candidate->interview_scheduled_at)
                            <div style="background: linear-gradient(135deg, #fefce8 0%, #fffbeb 100%); border: 1px solid #fde047; border-left: 4px solid #ca8a04; border-radius: 8px; padding: 0.65rem 0.9rem; margin-top: 0.75rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
                                <div style="font-size: 0.82rem; color: #854d0e;">
                                    <span style="font-weight: 700;">📅 Interview:</span> {{ $candidate->interview_scheduled_at->format('D, M j, Y \a\t g:i A') }}
                                    <span style="display: inline-block; background: #fef08a; color: #713f12; padding: 1px 7px; border-radius: 5px; font-weight: 700; font-size: 0.72rem; margin-left: 6px;">
                                        @if($candidate->meeting_type === 'in_person') 🏢 In-Person
                                        @elseif($candidate->meeting_type === 'phone') 📞 Phone
                                        @elseif($candidate->meeting_type === 'zoom') 🎥 Zoom
                                        @else 💻 Google Meet
                                        @endif
                                    </span>
                                    @if($candidate->meeting_link_or_location)
                                        <span style="margin-left: 6px; color: #475569;">
                                            @if(Str::startsWith($candidate->meeting_link_or_location, ['http://', 'https://']))
                                                <a href="{{ $candidate->meeting_link_or_location }}" target="_blank" style="color: #4f46e5; font-weight: 600; text-decoration: underline;">Link ↗</a>
                                            @else
                                                • {{ Str::limit($candidate->meeting_link_or_location, 35) }}
                                            @endif
                                        </span>
                                    @endif
                                </div>
                                <button type="button" onclick="openShortlistModal({{ $candidate->id }}, '{{ addslashes($candidate->candidate_name) }}', '{{ addslashes($candidate->candidate_email ?? '') }}', '{{ addslashes($job->title) }}', '{{ $candidate->interview_scheduled_at ? $candidate->interview_scheduled_at->format('Y-m-d\TH:i') : '' }}', '{{ $candidate->meeting_type ?? 'google_meet' }}', '{{ addslashes($candidate->meeting_link_or_location ?? '') }}', '{{ addslashes($candidate->interview_notes ?? '') }}')" style="background: #ffffff; border: 1px solid #eab308; color: #854d0e; font-size: 0.72rem; font-weight: 700; padding: 0.25rem 0.6rem; border-radius: 6px; cursor: pointer;">
                                    ✏️ Reschedule
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

    </div>

    <!-- 🎯 AI Headhunter Agent: Autonomous Sourcing & Outreach -->
    @php
        $hhTotal = $headhunterMatches->count();
        $hhTopMatches = $headhunterMatches->filter(fn($m) => ($m->match_score ?? $m->ai_match_score) >= 80)->count();
        $hhInvited = $headhunterMatches->whereIn('status', ['invited', 'accepted', 'declined'])->count();
        $hhAccepted = $headhunterMatches->where('status', 'accepted')->count();
    @endphp

    <div class="card" id="headhunter-section" style="border: 2px solid #0284c7; background: linear-gradient(135deg, rgba(255,255,255,0.98) 0%, rgba(240,249,255,0.96) 100%); box-shadow: 0 10px 30px rgba(2,132,199,0.08);">
        
        <!-- Headhunter Header Banner -->
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; border-bottom: 1px solid #bae6fd; padding-bottom: 1.25rem; margin-bottom: 1.25rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
                    <span style="font-size: 1.6rem;">🎯</span>
                    <h2 style="font-size: 1.35rem; font-weight: 800; color: #0c4a6e; margin: 0;">
                        AI Headhunter Agent: Autonomous Talent Scout
                    </h2>
                    <span class="badge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc; font-weight: 700; font-size: 0.75rem;">
                        Hybrid Talent Pool
                    </span>
                </div>
                <p style="font-size: 0.88rem; color: #334155; margin: 0.35rem 0 0 0;">
                    Autonomous agent proactively discovers high-caliber tech profiles matching <strong>{{ $job->title }}</strong>, analyzes experience depth & tech stacks, and crafts high-converting personalized pitches.
                </p>
                @if($job->last_headhunter_scout_at)
                    <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.25rem;">
                        🕒 Last scouted: {{ $job->last_headhunter_scout_at->diffForHumans() }}
                    </div>
                @endif
            </div>

            <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                @if($hhTopMatches > 0)
                    <form action="{{ route('company.headhunter.batch_invite', $job->id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Send personalized AI outreach invitations to all uninvited candidates with Match Score ≥ 80%?');">
                        @csrf
                        <input type="hidden" name="min_score" value="80">
                        <button type="submit" class="btn" style="display: inline-flex; align-items: center; gap: 0.45rem; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-weight: 700; font-size: 0.85rem; padding: 0.55rem 1.1rem; border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                            <span>🚀</span> Batch Invite Top Talent (≥80%)
                        </button>
                    </form>
                @endif

                <form action="{{ route('company.headhunter.scout', $job->id) }}" method="POST" style="margin: 0;">
                    @csrf
                    <input type="hidden" name="limit" value="6">
                    <button type="submit" class="btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%); font-weight: 700; font-size: 0.9rem; padding: 0.6rem 1.3rem; border: none; box-shadow: 0 4px 15px rgba(2,132,199,0.3); border-radius: 8px; cursor: pointer;">
                        <span>🎯</span> {{ $headhunterMatches->isNotEmpty() ? 'Scout More Candidates' : 'Scout Candidates with AI' }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Headhunter Analytics Bar -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 180px), 1fr)); gap: 0.75rem; margin-bottom: 1.5rem;">
            <div style="background: white; border: 1px solid #bae6fd; border-radius: 10px; padding: 0.85rem 1rem;">
                <div style="font-size: 0.75rem; color: #0369a1; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;">Total Scouted</div>
                <div style="font-size: 1.4rem; font-weight: 800; color: #0c4a6e; margin-top: 0.2rem;">{{ $hhTotal }}</div>
            </div>

            <div style="background: white; border: 1px solid #bbf7d0; border-radius: 10px; padding: 0.85rem 1rem;">
                <div style="font-size: 0.75rem; color: #166534; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;">🌟 High Fit (≥80%)</div>
                <div style="font-size: 1.4rem; font-weight: 800; color: #15803d; margin-top: 0.2rem;">{{ $hhTopMatches }}</div>
            </div>

            <div style="background: white; border: 1px solid #fed7aa; border-radius: 10px; padding: 0.85rem 1rem;">
                <div style="font-size: 0.75rem; color: #9a3412; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;">✉️ Invitations Sent</div>
                <div style="font-size: 1.4rem; font-weight: 800; color: #c2410c; margin-top: 0.2rem;">{{ $hhInvited }}</div>
            </div>

            <div style="background: white; border: 1px solid #ddd6fe; border-radius: 10px; padding: 0.85rem 1rem;">
                <div style="font-size: 0.75rem; color: #5b21b6; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;">🎉 Accepted & Connected</div>
                <div style="font-size: 1.4rem; font-weight: 800; color: #6d28d9; margin-top: 0.2rem;">{{ $hhAccepted }}</div>
            </div>
        </div>

        @if($headhunterMatches->isEmpty())
            <!-- Empty State -->
            <div style="text-align: center; padding: 3rem 1.5rem; background: white; border-radius: 14px; border: 1px dashed #7dd3fc;">
                <div style="font-size: 3rem; margin-bottom: 0.75rem;">🎯</div>
                <h3 style="font-size: 1.2rem; font-weight: 700; color: #0c4a6e; margin-bottom: 0.4rem;">
                    Autonomous Headhunter Agent Ready
                </h3>
                <p style="color: #475569; font-size: 0.9rem; max-width: 540px; margin: 0 auto 1.5rem auto; line-height: 1.5;">
                    The Headhunter Agent searches platform candidate resumes and external high-impact developer profiles matching your requirements for <strong>{{ $job->title }}</strong>, computing match affinity and drafting personalized email pitches.
                </p>

                <form action="{{ route('company.headhunter.scout', $job->id) }}" method="POST" style="display: inline-block;">
                    @csrf
                    <input type="hidden" name="limit" value="6">
                    <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%); font-size: 0.95rem; font-weight: 700; padding: 0.75rem 1.8rem; box-shadow: 0 4px 15px rgba(2,132,199,0.35);">
                        🎯 Launch AI Headhunter Scout Now
                    </button>
                </form>
            </div>
        @else
            <!-- Scouted Candidates List -->
            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                @foreach($headhunterMatches as $match)
                    @php
                        $scoreBadgeBg = $match->ai_match_score >= 80 ? '#dcfce7' : ($match->ai_match_score >= 60 ? '#e0f2fe' : '#fef3c7');
                        $scoreBadgeColor = $match->ai_match_score >= 80 ? '#15803d' : ($match->ai_match_score >= 60 ? '#0369a1' : '#b45309');
                        $scoreBorderColor = $match->ai_match_score >= 80 ? '#bbf7d0' : ($match->ai_match_score >= 60 ? '#bae6fd' : '#fde68a');
                    @endphp

                    <div style="background: white; border: 1px solid #e2e8f0; border-radius: 14px; padding: 1.35rem; box-shadow: 0 2px 10px rgba(0,0,0,0.03); transition: all 0.2s ease;">
                        
                        <!-- Top Info Bar -->
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
                                    <h3 style="font-size: 1.15rem; font-weight: 700; color: #0f172a; margin: 0;">
                                        {{ $match->candidate_name }}
                                    </h3>
                                    
                                    <!-- Source Pill -->
                                    <span class="badge" style="font-size: 0.72rem; font-weight: 700; background: {{ $match->pool_source === 'platform' ? '#ede9fe' : '#e0f2fe' }}; color: {{ $match->pool_source === 'platform' ? '#6d28d9' : '#0369a1' }}; border: 1px solid {{ $match->pool_source === 'platform' ? '#c4b5fd' : '#bae6fd' }};">
                                        {{ $match->pool_source === 'platform' ? '👤 Platform Candidate' : '🌐 External Tech Talent' }}
                                    </span>

                                    <!-- Status Badge -->
                                    @if($match->status === 'accepted')
                                        <span class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-weight: 700; font-size: 0.72rem;">
                                            🎉 Accepted & Connected
                                        </span>
                                    @elseif($match->status === 'invited')
                                        <span class="badge" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-weight: 700; font-size: 0.72rem;">
                                            ✉️ Invitation Sent
                                        </span>
                                    @elseif($match->status === 'declined')
                                        <span class="badge" style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; font-weight: 700; font-size: 0.72rem;">
                                            ✕ Declined
                                        </span>
                                    @else
                                        <span class="badge" style="background: #f1f5f9; color: #475569; font-weight: 700; font-size: 0.72rem;">
                                            🔍 Scouted
                                        </span>
                                    @endif
                                </div>

                                <div style="font-size: 0.85rem; color: #475569; margin-top: 0.25rem; display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                                    @if($match->current_title)
                                        <span>💼 {{ $match->current_title }} {{ $match->current_company ? '@ ' . $match->current_company : '' }}</span>
                                    @endif
                                    @if($match->location)
                                        <span>📍 {{ $match->location }}</span>
                                    @endif
                                    @if($match->candidate_email)
                                        <span>📧 {{ $match->candidate_email }}</span>
                                    @endif
                                    @if($match->experience_years)
                                        <span>⏳ {{ $match->experience_years }} yrs exp</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Match Score Pill -->
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="background: {{ $scoreBadgeBg }}; color: {{ $scoreBadgeColor }}; border: 1.5px solid {{ $scoreBorderColor }}; padding: 0.35rem 0.85rem; border-radius: 20px; font-weight: 800; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <span>🎯</span> {{ $match->ai_match_score }}% Match
                                </div>
                            </div>
                        </div>

                        <!-- Skills -->
                        @if(!empty($match->skills) && is_array($match->skills))
                            <div style="display: flex; flex-wrap: wrap; gap: 0.4rem; margin-bottom: 0.85rem;">
                                @foreach($match->skills as $skill)
                                    <span style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; padding: 0.2rem 0.65rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">
                                        {{ $skill }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <!-- AI Rationale -->
                        @if($match->ai_reasoning)
                            <div style="background: #f8fafc; border-left: 3px solid #0284c7; padding: 0.65rem 0.85rem; border-radius: 6px; font-size: 0.85rem; color: #334155; line-height: 1.5; margin-bottom: 0.85rem;">
                                <strong style="color: #0369a1;">AI Headhunter Assessment:</strong> {{ $match->ai_reasoning }}
                            </div>
                        @endif

                        <!-- AI Generated Outreach Preview -->
                        <details style="margin-bottom: 1rem; font-size: 0.82rem; background: #faf5ff; border: 1px solid #e9d5ff; border-radius: 8px; padding: 0.5rem 0.75rem;">
                            <summary style="cursor: pointer; font-weight: 700; color: #7e22ce;">
                                ✨ View AI-Crafted Personalized Outreach Pitch
                            </summary>
                            <div style="margin-top: 0.6rem; padding-top: 0.6rem; border-top: 1px dashed #d8b4fe; color: #4c1d95; line-height: 1.55; white-space: pre-wrap;">
<strong>Subject:</strong> {{ $match->outreach_subject ?? ('Career Opportunity: ' . $job->title) }}

{{ $match->personalized_pitch }}
                            </div>
                        </details>

                        <!-- Actions Bar -->
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; padding-top: 0.75rem; border-top: 1px solid #f1f5f9;">
                            <div style="font-size: 0.78rem; color: #64748b;">
                                @if($match->invited_at)
                                    ✉️ Invited {{ $match->invited_at->diffForHumans() }}
                                @else
                                    Ready for recruiter review & outreach dispatch
                                @endif
                            </div>

                            <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                                @if($match->status === 'accepted')
                                    <a href="#inquiries-section" class="btn" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-size: 0.8rem; font-weight: 700; padding: 0.4rem 0.85rem; text-decoration: none; border-radius: 6px;">
                                        ⭐ View in Job Shortlist →
                                    </a>
                                @elseif($match->status === 'invited')
                                    <button type="button" onclick="openHeadhunterModal({{ $match->id }}, '{{ addslashes($match->candidate_name) }}', '{{ addslashes($match->candidate_email ?? '') }}', {{ $match->ai_match_score }}, '{{ $match->pool_source }}', '{{ addslashes($match->outreach_subject ?? ('Career Opportunity: ' . $job->title)) }}', '{{ addslashes($match->personalized_pitch ?? '') }}')" class="btn-secondary" style="font-size: 0.8rem; padding: 0.4rem 0.85rem;">
                                        🔄 Re-send Invitation
                                    </button>
                                @else
                                    <button type="button" onclick="openHeadhunterModal({{ $match->id }}, '{{ addslashes($match->candidate_name) }}', '{{ addslashes($match->candidate_email ?? '') }}', {{ $match->ai_match_score }}, '{{ $match->pool_source }}', '{{ addslashes($match->outreach_subject ?? ('Career Opportunity: ' . $job->title)) }}', '{{ addslashes($match->personalized_pitch ?? '') }}')" class="btn-primary" style="background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%); font-size: 0.82rem; font-weight: 700; padding: 0.45rem 1rem; border: none; box-shadow: 0 2px 8px rgba(2,132,199,0.3); border-radius: 6px; cursor: pointer;">
                                        ✉️ Review & Send Invite
                                    </button>
                                @endif
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif

    </div>
    <div class="card" id="inquiries-section">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;">
            <div>
                <div class="card-title" style="font-size: 1.2rem; font-weight: 700; color: #1f2937; display: flex; align-items: center; gap: 0.5rem;">
                    <span>📬</span> Candidate Inquiries & Platform Comments
                    <span class="badge" style="background: #e0e7ff; color: #4338ca; font-size: 0.8rem;">
                        {{ $inquiries->count() }}
                    </span>
                </div>
                <p style="font-size: 0.85rem; color: #64748b; margin: 0.2rem 0 0 0;">
                    Real-time questions, feedback, and applications from LinkedIn, Naukri, Indeed, and direct link for this role.
                </p>
            </div>

            <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                <form action="{{ route('company.inquiries.sync') }}" method="POST" style="margin: 0;">
                    @csrf
                    <input type="hidden" name="job_id" value="{{ $job->id }}">
                    <button type="submit" class="btn-secondary" style="font-size: 0.85rem; padding: 0.45rem 0.9rem;">
                        🔄 Sync Platform Comments
                    </button>
                </form>
                <a href="{{ route('company.inquiries.index', ['job_id' => $job->id]) }}" class="btn-primary" style="font-size: 0.85rem; padding: 0.45rem 1rem;">
                    View in All Inquiries Hub →
                </a>
            </div>
        </div>

        @if($inquiries->isEmpty())
            <div style="text-align: center; padding: 2.5rem 1rem; background: #f8fafc; border-radius: 14px; border: 1px dashed #cbd5e1;">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">💬</div>
                <h4 style="font-size: 1.05rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">No Inquiries or Comments Yet</h4>
                <p style="color: #64748b; font-size: 0.85rem; max-width: 460px; margin: 0 auto 1.25rem auto;">
                    Share your public candidate link on social channels or sync directly from your connected platform posts.
                </p>
                <div style="display: flex; gap: 0.5rem; justify-content: center; align-items: center; flex-wrap: wrap; width: 100%;">
                    <input type="text" id="public-job-url" value="{{ route('jobs.public.show', $job->id) }}" readonly class="form-control" style="width: 100%; max-width: 340px; font-size: 0.8rem; padding: 0.4rem 0.75rem;">
                    <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('public-job-url').value); alert('Copied public job link!');" class="btn-secondary" style="font-size: 0.8rem; padding: 0.4rem 0.85rem; white-space: nowrap;">
                        📋 Copy Link
                    </button>
                </div>
            </div>
        @else
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                @foreach($inquiries as $inquiry)
                    <div style="background: {{ $inquiry->is_pinned ? 'linear-gradient(135deg, #ffffff 0%, #faf5ff 100%)' : 'white' }}; border: {{ $inquiry->is_pinned ? '2px solid #8b5cf6' : '1px solid #e2e8f0' }}; border-radius: 12px; padding: 1.25rem; box-shadow: {{ $inquiry->is_pinned ? '0 4px 15px rgba(139,92,246,0.12)' : 'none' }}; transition: all 0.2s ease;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                    <span style="font-weight: 700; color: #1e1b4b;">{{ $inquiry->candidate_name }}</span>

                                    @if($inquiry->is_pinned)
                                        <span class="badge" style="background: #ede9fe; color: #6d28d9; border: 1px solid #c4b5fd; font-weight: 700; font-size: 0.72rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                            📌 Pinned
                                        </span>
                                    @endif

                                    @if($inquiry->ai_match_score !== null)
                                        <span class="badge" style="background: {{ $inquiry->ai_match_score >= 80 ? '#dcfce7' : ($inquiry->ai_match_score >= 60 ? '#e0e7ff' : '#fef3c7') }}; color: {{ $inquiry->ai_match_score >= 80 ? '#15803d' : ($inquiry->ai_match_score >= 60 ? '#4338ca' : '#b45309') }}; border: 1px solid {{ $inquiry->ai_match_score >= 80 ? '#bbf7d0' : ($inquiry->ai_match_score >= 60 ? '#c7d2fe' : '#fde68a') }}; font-size: 0.72rem; font-weight: 700;">
                                            🤖 Rank #{{ $inquiry->ai_rank }} • {{ $inquiry->ai_match_score }}% Match
                                        </span>
                                    @endif

                                    @if($inquiry->candidate_email)
                                        <span style="font-size: 0.8rem; color: #64748b;">&lt;{{ $inquiry->candidate_email }}&gt;</span>
                                    @endif

                                    <!-- Platform Pill -->
                                    <span class="badge" style="font-size: 0.72rem; 
                                        background: {{ $inquiry->platform === 'linkedin' ? '#e0f2fe' : ($inquiry->platform === 'naukri' ? '#dbeafe' : ($inquiry->platform === 'indeed' ? '#e0e7ff' : '#f1f5f9')) }};
                                        color: {{ $inquiry->platform === 'linkedin' ? '#0369a1' : ($inquiry->platform === 'naukri' ? '#1d4ed8' : ($inquiry->platform === 'indeed' ? '#4338ca' : '#475569')) }};">
                                        {{ ucfirst($inquiry->platform) }}
                                    </span>

                                    <!-- Type Pill -->
                                    <span class="badge" style="background: #f3f4f6; color: #4b5563; font-size: 0.72rem;">
                                        {{ ucfirst($inquiry->type) }}
                                    </span>
                                </div>
                                @if($inquiry->candidate_headline)
                                    <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.15rem;">{{ $inquiry->candidate_headline }}</div>
                                @endif
                            </div>

                            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                <span style="font-size: 0.75rem; color: #94a3b8;">{{ $inquiry->created_at->diffForHumans() }}</span>

                                <!-- Pin / Unpin Form Toggle -->
                                <form action="{{ route('company.inquiries.pin', $inquiry->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" title="{{ $inquiry->is_pinned ? 'Unpin this inquiry' : 'Pin inquiry to top' }}" style="font-size: 0.72rem; font-weight: 600; padding: 0.2rem 0.55rem; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.3rem; transition: all 0.15s ease; border: {{ $inquiry->is_pinned ? '1px solid #c4b5fd' : '1px solid #cbd5e1' }}; background: {{ $inquiry->is_pinned ? '#ede9fe' : '#ffffff' }}; color: {{ $inquiry->is_pinned ? '#6d28d9' : '#475569' }};">
                                        <span>📌</span>
                                        <span>{{ $inquiry->is_pinned ? 'Unpin' : 'Pin' }}</span>
                                    </button>
                                </form>

                                <form action="{{ route('company.inquiries.status', $inquiry->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" data-prev-status="{{ $inquiry->status }}" onchange="handleStatusChange(this, {{ $inquiry->id }}, '{{ addslashes($inquiry->candidate_name) }}', '{{ addslashes($inquiry->candidate_email ?? '') }}', '{{ addslashes($job->title) }}', '{{ $inquiry->interview_scheduled_at ? $inquiry->interview_scheduled_at->format('Y-m-d\TH:i') : '' }}', '{{ $inquiry->meeting_type ?? 'google_meet' }}', '{{ addslashes($inquiry->meeting_link_or_location ?? '') }}', '{{ addslashes($inquiry->interview_notes ?? '') }}')" style="font-size: 0.72rem; font-weight: 600; padding: 0.2rem 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; background: 
                                        {{ $inquiry->status === 'new' ? '#fef3c7' : ($inquiry->status === 'replied' ? '#dcfce7' : ($inquiry->status === 'shortlisted' ? '#fef9c3' : '#f1f5f9')) }};">
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
                            <div style="background: #fefce8; border: 1px solid #fde047; border-left: 3px solid #ca8a04; border-radius: 6px; padding: 0.45rem 0.75rem; margin: 0.4rem 0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.4rem;">
                                <div style="font-size: 0.78rem; color: #854d0e;">
                                    <strong>📅 Interview:</strong> {{ $inquiry->interview_scheduled_at->format('D, M j, Y \a\t g:i A') }}
                                    <span style="background: #fef08a; color: #713f12; padding: 1px 5px; border-radius: 4px; font-weight: 700; font-size: 0.7rem; margin-left: 4px;">
                                        {{ ucfirst(str_replace('_', ' ', $inquiry->meeting_type ?? 'Online')) }}
                                    </span>
                                    @if($inquiry->meeting_link_or_location)
                                        <span style="margin-left: 4px; color: #475569;">
                                            @if(Str::startsWith($inquiry->meeting_link_or_location, ['http://', 'https://']))
                                                <a href="{{ $inquiry->meeting_link_or_location }}" target="_blank" style="color: #4f46e5; font-weight: 600; text-decoration: underline;">Link ↗</a>
                                            @else
                                                • {{ Str::limit($inquiry->meeting_link_or_location, 30) }}
                                            @endif
                                        </span>
                                    @endif
                                </div>
                                <button type="button" onclick="openShortlistModal({{ $inquiry->id }}, '{{ addslashes($inquiry->candidate_name) }}', '{{ addslashes($inquiry->candidate_email ?? '') }}', '{{ addslashes($job->title) }}', '{{ $inquiry->interview_scheduled_at ? $inquiry->interview_scheduled_at->format('Y-m-d\TH:i') : '' }}', '{{ $inquiry->meeting_type ?? 'google_meet' }}', '{{ addslashes($inquiry->meeting_link_or_location ?? '') }}', '{{ addslashes($inquiry->interview_notes ?? '') }}')" style="background: #ffffff; border: 1px solid #eab308; color: #854d0e; font-size: 0.7rem; font-weight: 700; padding: 0.15rem 0.45rem; border-radius: 4px; cursor: pointer;">
                                    ✏️ Reschedule
                                </button>
                            </div>
                        @endif

                        <div style="background: #f8fafc; border-left: 3px solid #818cf8; padding: 0.75rem 1rem; border-radius: 8px; color: #334155; font-size: 0.9rem; line-height: 1.5; margin: 0.5rem 0;">
                            {{ $inquiry->message }}
                        </div>

                        @if($inquiry->resume_path)
                            <div style="margin-bottom: 0.5rem;">
                                <a href="{{ asset('storage/' . $inquiry->resume_path) }}" target="_blank" style="font-size: 0.8rem; color: #4338ca; font-weight: 600; text-decoration: none;">
                                    📄 Attached Resume ↗
                                </a>
                            </div>
                        @endif

                        @if($inquiry->company_reply)
                            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 0.6rem 0.85rem; font-size: 0.82rem; color: #14532d; margin-top: 0.5rem;">
                                <strong>Your Reply:</strong> {{ $inquiry->company_reply }}
                            </div>
                        @endif

                        <details style="margin-top: 0.5rem; font-size: 0.8rem;">
                            <summary style="cursor: pointer; color: #4c51bf; font-weight: 600;">
                                {{ $inquiry->company_reply ? 'Edit Reply' : 'Reply to Candidate' }}
                            </summary>
                            <form action="{{ route('company.inquiries.reply', $inquiry->id) }}" method="POST" style="margin-top: 0.5rem;">
                                @csrf
                                <textarea name="company_reply" rows="2" class="form-control" placeholder="Write response..." required style="font-size: 0.82rem;">{{ $inquiry->company_reply }}</textarea>
                                <div style="display: flex; justify-content: flex-end; margin-top: 0.35rem;">
                                    <button type="submit" class="btn-primary" style="font-size: 0.75rem; padding: 0.3rem 0.8rem;">Send Reply</button>
                                </div>
                            </form>
                        </details>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>

@include('company.partials.shortlist_modal')
@include('company.partials.headhunter_modal')
@endsection
