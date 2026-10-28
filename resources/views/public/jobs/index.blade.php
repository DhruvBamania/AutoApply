@extends('layouts.app')

@section('content')
<div style="max-width: 1200px; margin: 0 auto; padding-bottom: 3rem;">
    <!-- Hero Header & Stats Banner -->
    <div style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4338ca 100%); border-radius: 24px; padding: 3rem 2rem; color: white; margin-bottom: 2.5rem; position: relative; overflow: hidden; box-shadow: 0 20px 40px -15px rgba(49, 46, 129, 0.35);">
        <!-- Background Ambient Accents -->
        <div style="position: absolute; top: -50px; right: -50px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(99, 102, 241, 0.4) 0%, rgba(0,0,0,0) 70%); border-radius: 50%; pointer-events: none;"></div>
        <div style="position: absolute; bottom: -60px; left: 10%; width: 250px; height: 250px; background: radial-gradient(circle, rgba(236, 72, 153, 0.25) 0%, rgba(0,0,0,0) 70%); border-radius: 50%; pointer-events: none;"></div>

        <div style="position: relative; z-index: 2; max-width: 800px;">
            <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(10px); padding: 0.4rem 1rem; border-radius: 9999px; font-size: 0.85rem; font-weight: 600; color: #c7d2fe; margin-bottom: 1.25rem; border: 1px solid rgba(255, 255, 255, 0.15);">
                <span>💼</span> Live Company Openings & Multi-Platform Feed
            </div>
            <h1 style="color: white; font-size: clamp(1.8rem, 4vw, 2.75rem); font-weight: 800; letter-spacing: -0.03em; margin-bottom: 0.85rem; line-height: 1.2;">
                Find Your Next Career Move
            </h1>
            <p style="color: #e0e7ff; font-size: 1.05rem; line-height: 1.6; margin-bottom: 2rem; max-width: 650px;">
                Explore verified job postings from top hiring teams. Contact recruiters directly through our integrated <strong style="color: #86efac;">WhatsApp hiring line</strong> or apply with 1-click tailored resumes.
            </p>

            <!-- Search & Filter Form -->
            <form action="{{ route('jobs.index') }}" method="GET" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); border-radius: 18px; padding: 0.6rem; display: flex; flex-wrap: wrap; gap: 0.6rem; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);">
                <div style="flex: 2; min-width: 220px; position: relative; display: flex; align-items: center;">
                    <span style="position: absolute; left: 1rem; font-size: 1.1rem; color: #64748b;">🔍</span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Job title, skills, or company name..." style="width: 100%; padding: 0.85rem 1rem 0.85rem 2.8rem; border: none; background: transparent; font-size: 0.95rem; color: #1e293b; outline: none;">
                </div>

                <div style="flex: 1.2; min-width: 160px; position: relative; display: flex; align-items: center; border-left: 1px solid #e2e8f0;">
                    <span style="position: absolute; left: 1rem; font-size: 1.1rem; color: #64748b;">📍</span>
                    <input type="text" name="location" value="{{ request('location') }}" placeholder="Location or Remote" style="width: 100%; padding: 0.85rem 1rem 0.85rem 2.8rem; border: none; background: transparent; font-size: 0.95rem; color: #1e293b; outline: none;">
                </div>

                <div style="flex: 1; min-width: 130px; border-left: 1px solid #e2e8f0; display: flex; align-items: center;">
                    <select name="job_type" style="width: 100%; padding: 0.85rem 1rem; border: none; background: transparent; font-size: 0.95rem; color: #1e293b; outline: none; cursor: pointer;">
                        <option value="all" {{ request('job_type') === 'all' || !request('job_type') ? 'selected' : '' }}>All Types</option>
                        <option value="Full-time" {{ request('job_type') === 'Full-time' ? 'selected' : '' }}>Full-time</option>
                        <option value="Part-time" {{ request('job_type') === 'Part-time' ? 'selected' : '' }}>Part-time</option>
                        <option value="Contract" {{ request('job_type') === 'Contract' ? 'selected' : '' }}>Contract</option>
                        <option value="Remote" {{ request('job_type') === 'Remote' ? 'selected' : '' }}>Remote</option>
                        <option value="Internship" {{ request('job_type') === 'Internship' ? 'selected' : '' }}>Internship</option>
                    </select>
                </div>

                <button type="submit" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: white; border: none; padding: 0.85rem 1.75rem; border-radius: 12px; font-weight: 600; font-size: 0.95rem; cursor: pointer; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);">
                    Find Jobs
                </button>
            </form>
        </div>

        <!-- Metric Badges Row -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-top: 2rem; position: relative; z-index: 2; border-top: 1px solid rgba(255, 255, 255, 0.12); padding-top: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(99, 102, 241, 0.25); display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                    🚀
                </div>
                <div>
                    <div style="font-size: 1.35rem; font-weight: 800; color: white;">{{ $totalActiveJobs }}</div>
                    <div style="font-size: 0.8rem; color: #c7d2fe;">Active Roles</div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(16, 185, 129, 0.25); display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                    🏢
                </div>
                <div>
                    <div style="font-size: 1.35rem; font-weight: 800; color: white;">{{ $totalCompaniesHiring }}</div>
                    <div style="font-size: 0.8rem; color: #c7d2fe;">Companies Hiring</div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(245, 158, 11, 0.25); display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                    🌐
                </div>
                <div>
                    <div style="font-size: 1.35rem; font-weight: 800; color: white;">{{ $remoteJobsCount }}</div>
                    <div style="font-size: 0.8rem; color: #c7d2fe;">Remote Friendly</div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(37, 211, 102, 0.25); display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                    💬
                </div>
                <div>
                    <div style="font-size: 1.35rem; font-weight: 800; color: #86efac;">WhatsApp</div>
                    <div style="font-size: 0.8rem; color: #c7d2fe;">Instant Recruiter Chat</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Filter Quick Tags -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.75rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <span style="font-size: 0.85rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin-right: 0.25rem;">Filter:</span>
            <a href="{{ route('jobs.index') }}" style="text-decoration: none; padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.85rem; font-weight: 600; transition: all 0.2s ease; {{ !request('job_type') || request('job_type') === 'all' ? 'background: #4f46e5; color: white;' : 'background: white; color: #475569; border: 1px solid #e2e8f0;' }}">
                All Positions
            </a>
            <a href="{{ route('jobs.index', array_merge(request()->query(), ['job_type' => 'Full-time'])) }}" style="text-decoration: none; padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.85rem; font-weight: 600; transition: all 0.2s ease; {{ request('job_type') === 'Full-time' ? 'background: #4f46e5; color: white;' : 'background: white; color: #475569; border: 1px solid #e2e8f0;' }}">
                Full-time
            </a>
            <a href="{{ route('jobs.index', array_merge(request()->query(), ['job_type' => 'Remote'])) }}" style="text-decoration: none; padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.85rem; font-weight: 600; transition: all 0.2s ease; {{ request('job_type') === 'Remote' ? 'background: #4f46e5; color: white;' : 'background: white; color: #475569; border: 1px solid #e2e8f0;' }}">
                Remote
            </a>
            <a href="{{ route('jobs.index', array_merge(request()->query(), ['job_type' => 'Contract'])) }}" style="text-decoration: none; padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.85rem; font-weight: 600; transition: all 0.2s ease; {{ request('job_type') === 'Contract' ? 'background: #4f46e5; color: white;' : 'background: white; color: #475569; border: 1px solid #e2e8f0;' }}">
                Contract
            </a>
            <a href="{{ route('jobs.index', array_merge(request()->query(), ['job_type' => 'Part-time'])) }}" style="text-decoration: none; padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.85rem; font-weight: 600; transition: all 0.2s ease; {{ request('job_type') === 'Part-time' ? 'background: #4f46e5; color: white;' : 'background: white; color: #475569; border: 1px solid #e2e8f0;' }}">
                Part-time
            </a>
        </div>

        <div style="font-size: 0.9rem; color: #64748b; font-weight: 500;">
            Showing <strong>{{ $jobs->total() }}</strong> active {{ Str::plural('opportunity', $jobs->total()) }}
        </div>
    </div>

    <!-- Jobs Grid -->
    @if($jobs->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
            @foreach($jobs as $job)
                @php
                    $company = $job->user?->companyProfile;
                    $companyName = $company?->company_name ?? $job->user?->name ?? 'Hiring Company';
                    $hasWhatsApp = !empty($job->whatsapp_number) || !empty($company?->whatsapp_number);
                    $targetPhone = !empty($job->whatsapp_number) ? $job->whatsapp_number : ($company?->whatsapp_number ?? '');
                @endphp
                <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; padding: 1.5rem; border-radius: 20px; transition: transform 0.2s ease, box-shadow 0.2s ease; border: 1px solid rgba(226, 232, 240, 0.8); background: white;">
                    <div>
                        <!-- Top Header: Avatar + Title + Badges -->
                        <div style="display: flex; align-items: flex-start; gap: 0.85rem; margin-bottom: 1rem;">
                            <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, #e0e7ff 0%, #ede9fe 100%); color: #4338ca; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem; flex-shrink: 0; border: 1px solid #c7d2fe;">
                                {{ strtoupper(substr($companyName, 0, 1)) }}
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <h3 style="font-size: 1.15rem; font-weight: 700; margin: 0 0 0.25rem 0; line-height: 1.3;">
                                    <a href="{{ route('jobs.public.show', $job->id) }}" style="color: #1e1b4b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#4f46e5'" onmouseout="this.style.color='#1e1b4b'">
                                        {{ $job->title }}
                                    </a>
                                </h3>
                                <div style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.88rem; color: #475569; font-weight: 500;">
                                    <span>{{ $companyName }}</span>
                                    <span style="color: #cbd5e1;">•</span>
                                    <span style="color: #64748b;">📍 {{ $job->location ?? 'Remote' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Job Type & Multi-Posting Badges -->
                        <div style="display: flex; flex-wrap: wrap; gap: 0.45rem; margin-bottom: 1rem;">
                            <span style="background: #f1f5f9; color: #334155; font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.65rem; border-radius: 6px;">
                                ⏱️ {{ $job->job_type ?? 'Full-time' }}
                            </span>

                            @if($hasWhatsApp)
                                <span style="background: #f0fdf4; color: #166534; font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.65rem; border-radius: 6px; border: 1px solid #bbf7d0; display: inline-flex; align-items: center; gap: 0.3rem;">
                                    <span>💬</span> WhatsApp Available
                                </span>
                            @endif

                            @if($job->experience_level)
                                <span style="background: #ede9fe; color: #6d28d9; font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.65rem; border-radius: 6px;">
                                    ⭐ {{ $job->experience_level }}
                                </span>
                            @endif

                            @if($job->salary_range)
                                <span style="background: #fef3c7; color: #92400e; font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.65rem; border-radius: 6px;">
                                    💰 {{ $job->salary_range }}
                                </span>
                            @endif
                        </div>

                        <!-- Description Excerpt -->
                        <p style="font-size: 0.88rem; color: #475569; line-height: 1.5; margin-bottom: 1.25rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ Str::limit(strip_tags($job->description), 140) }}
                        </p>

                        <!-- Skills Tags -->
                        @if(!empty($job->skills))
                            <div style="display: flex; flex-wrap: wrap; gap: 0.35rem; margin-bottom: 1.25rem;">
                                @php
                                    $skillsList = is_array($job->skills) ? $job->skills : explode(',', $job->skills);
                                @endphp
                                @foreach(array_slice($skillsList, 0, 4) as $skill)
                                    <span style="background: #eef2ff; color: #4338ca; font-size: 0.72rem; font-weight: 600; padding: 0.2rem 0.55rem; border-radius: 5px;">
                                        #{{ trim($skill) }}
                                    </span>
                                @endforeach
                                @if(count($skillsList) > 4)
                                    <span style="font-size: 0.72rem; color: #94a3b8; align-self: center;">+{{ count($skillsList) - 4 }} more</span>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Bottom Action Controls -->
                    <div style="border-top: 1px solid #f1f5f9; padding-top: 1rem; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; flex-wrap: wrap;">
                        <span style="font-size: 0.78rem; color: #94a3b8; font-weight: 500;">
                            🕒 {{ $job->created_at->diffForHumans() }}
                        </span>

                        <div style="display: flex; align-items: center; gap: 0.4rem;">
                            <!-- Direct WhatsApp Trigger Button -->
                            <button type="button" 
                                onclick="openJobWhatsAppModal(this)"
                                data-job-id="{{ $job->id }}"
                                data-job-title="{{ $job->title }}"
                                data-company-name="{{ $companyName }}"
                                data-company-phone="{{ $targetPhone }}"
                                title="Message hiring team on WhatsApp" style="background: #25D366; color: white; border: none; padding: 0.5rem 0.75rem; border-radius: 9px; font-size: 0.85rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; transition: transform 0.15s ease;">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.06-1.11-.06-.415-.121-.926-.298-1.574-.582-1.378-.601-2.278-1.996-2.348-2.09-.07-.094-.564-.75-.564-1.429 0-.679.352-1.013.477-1.152.125-.139.272-.174.363-.174.09 0 .182.001.261.005.084.004.195-.032.304.232.114.275.39 1.013.424 1.083.034.07.057.152.011.243-.045.09-.068.147-.136.226-.068.079-.144.175-.205.236-.07.07-.142.146-.061.285.08.139.357.59.767.954.526.467.971.612 1.11.681.139.07.22.06.302-.034.082-.094.351-.409.444-.549.094-.14.188-.117.316-.07.128.047.813.383.953.453.14.07.233.105.267.163.035.059.035.342-.109.747z"/>
                                </svg>
                                <span>WhatsApp</span>
                            </button>

                            <!-- View Opening Details Button -->
                            <a href="{{ route('jobs.public.show', $job->id) }}" class="btn-primary" style="padding: 0.5rem 0.9rem; font-size: 0.85rem; border-radius: 9px; text-decoration: none;">
                                Apply →
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div style="display: flex; justify-content: center; margin-top: 2rem;">
            {{ $jobs->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="card" style="text-align: center; padding: 4rem 2rem; border-radius: 20px; background: white;">
            <div style="font-size: 3.5rem; margin-bottom: 1rem;">🔍</div>
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #1e1b4b; margin-bottom: 0.5rem;">No Job Openings Found</h2>
            <p style="color: #64748b; max-width: 500px; margin: 0 auto 1.5rem auto; line-height: 1.5;">
                We couldn't find any active job postings matching your current search criteria. Try adjusting your keywords or clearing the filters.
            </p>
            <a href="{{ route('jobs.index') }}" class="btn-primary" style="text-decoration: none; display: inline-flex;">
                Clear All Filters & View All
            </a>
        </div>
    @endif
</div>

<!-- Reusable Interactive WhatsApp Modal for Job Openings -->
<div id="jobWhatsAppModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(6px); align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: white; border-radius: 24px; max-width: 520px; width: 100%; padding: 2rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); position: relative; animation: modalPop 0.25s ease-out;">
        <button type="button" onclick="closeJobWhatsAppModal()" style="position: absolute; top: 1.25rem; right: 1.25rem; background: #f1f5f9; border: none; width: 34px; height: 34px; border-radius: 50%; font-size: 1.1rem; color: #64748b; cursor: pointer; display: flex; align-items: center; justify-content: center;">
            ✕
        </button>

        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #25D366; color: white; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; box-shadow: 0 4px 12px rgba(37, 211, 102, 0.35);">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.06-1.11-.06-.415-.121-.926-.298-1.574-.582-1.378-.601-2.278-1.996-2.348-2.09-.07-.094-.564-.75-.564-1.429 0-.679.352-1.013.477-1.152.125-.139.272-.174.363-.174.09 0 .182.001.261.005.084.004.195-.032.304.232.114.275.39 1.013.424 1.083.034.07.057.152.011.243-.045.09-.068.147-.136.226-.068.079-.144.175-.205.236-.07.07-.142.146-.061.285.08.139.357.59.767.954.526.467.971.612 1.11.681.139.07.22.06.302-.034.082-.094.351-.409.444-.549.094-.14.188-.117.316-.07.128.047.813.383.953.453.14.07.233.105.267.163.035.059.035.342-.109.747z"/>
                </svg>
            </div>
            <div>
                <h3 style="font-size: 1.15rem; font-weight: 700; color: #1e1b4b; margin: 0;" id="modalJobTitle">Message Hiring Team</h3>
                <span style="font-size: 0.85rem; color: #16a34a; font-weight: 600;" id="modalCompanyName">Direct WhatsApp Channel</span>
            </div>
        </div>

        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 0.75rem 1rem; margin-bottom: 1.25rem; font-size: 0.82rem; color: #166534; display: flex; align-items: center; gap: 0.5rem;">
            <span>⚡</span>
            <span><strong>WhatsApp Service:</strong> Your inquiry is dispatched directly to the employer's business phone.</span>
        </div>

        <form id="jobWhatsAppForm" onsubmit="submitJobWhatsApp(event)">
            @csrf
            <input type="hidden" id="modalJobId" name="job_id">

            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">Your Full Name *</label>
                <input type="text" id="modalCandidateName" name="candidate_name" required value="{{ auth()->user()?->name ?? '' }}" placeholder="e.g. Alex Rivera" class="form-control" style="font-size: 0.9rem;">
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">Your WhatsApp Phone Number (with Country Code) *</label>
                <input type="tel" id="modalCandidatePhone" name="candidate_phone" required placeholder="e.g. +1 555 123 4567 or +91 9876543210" class="form-control" style="font-size: 0.9rem;">
            </div>

            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">Your Question or Inquiry *</label>
                <textarea id="modalMessage" name="message" rows="4" required placeholder="Hi, I am interested in this position and would like to ask..." class="form-control" style="font-size: 0.9rem; resize: vertical;"></textarea>
            </div>

            <div id="modalAlertBox" style="display: none; padding: 0.75rem 1rem; border-radius: 10px; margin-bottom: 1rem; font-size: 0.85rem;"></div>

            <div style="display: flex; gap: 0.75rem; align-items: center;">
                <button type="submit" id="modalSubmitBtn" style="flex: 1; background: #25D366; color: white; border: none; padding: 0.85rem 1.5rem; border-radius: 12px; font-weight: 700; font-size: 0.95rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; box-shadow: 0 4px 14px rgba(37, 211, 102, 0.4);">
                    <span>Send WhatsApp Message</span>
                </button>
            </div>

            <!-- Fallback direct wa.me link -->
            <div id="directWaLinkBox" style="text-align: center; margin-top: 1rem; font-size: 0.82rem; color: #64748b;">
                <a id="directWaLink" href="#" target="_blank" style="color: #16a34a; font-weight: 600; text-decoration: underline;">
                    Or open directly in WhatsApp App →
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    let currentJobPhone = '';

    function openJobWhatsAppModal(jobIdOrElement, jobTitle, companyName, companyPhone) {
        let jobId = jobIdOrElement;
        if (jobIdOrElement && (jobIdOrElement instanceof HTMLElement || jobIdOrElement.dataset)) {
            jobId = jobIdOrElement.dataset.jobId;
            jobTitle = jobIdOrElement.dataset.jobTitle;
            companyName = jobIdOrElement.dataset.companyName;
            companyPhone = jobIdOrElement.dataset.companyPhone;
        }

        document.getElementById('modalJobId').value = jobId;
        document.getElementById('modalJobTitle').innerText = jobTitle;
        document.getElementById('modalCompanyName').innerText = companyName;
        currentJobPhone = companyPhone;

        const defaultMsg = encodeURIComponent("Hello! I am inquiring about the " + jobTitle + " position at " + companyName + ".");
        const cleanPhone = (companyPhone || '').replace(/[^0-9]/g, '');
        const waUrl = cleanPhone ? ("https://wa.me/" + cleanPhone + "?text=" + defaultMsg) : ("https://wa.me/?text=" + defaultMsg);
        document.getElementById('directWaLink').href = waUrl;

        document.getElementById('modalAlertBox').style.display = 'none';
        document.getElementById('jobWhatsAppModal').style.display = 'flex';
    }

    function closeJobWhatsAppModal() {
        document.getElementById('jobWhatsAppModal').style.display = 'none';
    }

    window.onclick = function(event) {
        const modal = document.getElementById('jobWhatsAppModal');
        if (event.target === modal) {
            closeJobWhatsAppModal();
        }
    };

    function submitJobWhatsApp(e) {
        e.preventDefault();
        const jobId = document.getElementById('modalJobId').value;
        const btn = document.getElementById('modalSubmitBtn');
        const alertBox = document.getElementById('modalAlertBox');

        btn.disabled = true;
        btn.innerHTML = '<span>Sending WhatsApp Message...</span>';

        const payload = {
            candidate_name: document.getElementById('modalCandidateName').value,
            candidate_phone: document.getElementById('modalCandidatePhone').value,
            message: document.getElementById('modalMessage').value,
            _token: '{{ csrf_token() }}'
        };

        fetch('/jobs/' + jobId + '/whatsapp-message', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<span>Send WhatsApp Message</span>';
            if (data.success) {
                alertBox.style.display = 'block';
                alertBox.style.background = '#dcfce7';
                alertBox.style.color = '#166534';
                alertBox.style.border = '1px solid #86efac';
                alertBox.innerHTML = '✅ <strong>Message Sent!</strong> ' + (data.message || 'WhatsApp message delivered to hiring team.');
                setTimeout(() => {
                    closeJobWhatsAppModal();
                }, 2200);
            } else {
                alertBox.style.display = 'block';
                alertBox.style.background = '#fee2e2';
                alertBox.style.color = '#991b1b';
                alertBox.style.border = '1px solid #fca5a5';
                alertBox.innerHTML = '✕ ' + (data.error || 'Failed to send WhatsApp message.');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<span>Send WhatsApp Message</span>';
            alertBox.style.display = 'block';
            alertBox.style.background = '#fee2e2';
            alertBox.style.color = '#991b1b';
            alertBox.style.border = '1px solid #fca5a5';
            alertBox.innerHTML = '✕ Network error sending message. Please try opening via WhatsApp directly.';
        });
    }
</script>
@endsection
