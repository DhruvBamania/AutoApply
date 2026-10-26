@extends('layouts.app')

@section('content')
<div style="max-width: 1100px; width: 100%; margin: 0 auto; display: flex; flex-direction: column; gap: 2rem;">

    <!-- Header Banner -->
    <div class="card" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; background: linear-gradient(135deg, rgba(255,255,255,0.7) 0%, rgba(238,242,255,0.85) 100%); border-left: 6px solid #4c51bf;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span style="font-size: 2rem;">💼</span>
                <h1 style="font-size: 1.8rem; font-weight: 700; color: #1e1b4b; margin: 0;">Multi-Platform Job Postings</h1>
            </div>
            <p style="color: #4b5563; font-size: 0.95rem; margin-top: 0.35rem; margin-bottom: 0;">
                Create and publish job roles to <strong>LinkedIn, Naukri, and Indeed</strong> directly with one click.
            </p>
        </div>
        <a href="{{ route('company.jobs.create') }}" class="btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; font-size: 0.95rem;">
            <span>➕</span> Post a New Job (1-Click)
        </a>
    </div>

    <!-- Connected Platforms Status Bar -->
    <div style="background: rgba(255,255,255,0.7); border: 1px solid rgba(229,231,235,0.9); border-radius: 14px; padding: 0.85rem 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; font-size: 0.85rem;">
            <span style="font-weight: 700; color: #1e1b4b;">🔗 Connected Platforms:</span>
            
            <span>
                💼 LinkedIn: 
                @if(isset($connectedAccounts['linkedin']))
                    <strong style="color: #15803d;">🟢 Connected</strong>
                @else
                    <strong style="color: #b91c1c;">🔴 Disconnected</strong>
                @endif
            </span>

            <span>
                🔵 Naukri: 
                @if(isset($connectedAccounts['naukri']))
                    <strong style="color: #15803d;">🟢 Connected</strong>
                @else
                    <strong style="color: #b91c1c;">🔴 Disconnected</strong>
                @endif
            </span>

            <span>
                🔷 Indeed: 
                @if(isset($connectedAccounts['indeed']))
                    <strong style="color: #15803d;">🟢 Connected</strong>
                @else
                    <strong style="color: #b91c1c;">🔴 Disconnected</strong>
                @endif
            </span>
        </div>

        <a href="{{ route('company.integrations') }}" class="btn-secondary" style="font-size: 0.8rem; padding: 0.35rem 0.85rem;">
            ⚙️ Manage Integrations
        </a>
    </div>

    <!-- Platform Status Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 200px), 1fr)); gap: 1rem;">
        <div class="card" style="background: white; padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #e0e7ff; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                📋
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #1e1b4b;">{{ $jobs->count() }}</div>
                <div style="font-size: 0.8rem; color: #6b7280; font-weight: 600;">Total Job Openings</div>
            </div>
        </div>

        <div class="card" style="background: white; padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #e0f2fe; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                💼
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0284c7;">
                    {{ $jobs->where('linkedin_status', 'posted')->count() }}
                </div>
                <div style="font-size: 0.8rem; color: #6b7280; font-weight: 600;">Live on LinkedIn</div>
            </div>
        </div>

        <div class="card" style="background: white; padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #ecfdf5; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                🔵
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #059669;">
                    {{ $jobs->where('naukri_status', 'posted')->count() }}
                </div>
                <div style="font-size: 0.8rem; color: #6b7280; font-weight: 600;">Live on Naukri</div>
            </div>
        </div>

        <div class="card" style="background: white; padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #fef3c7; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                🔷
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #d97706;">
                    {{ $jobs->where('indeed_status', 'posted')->count() }}
                </div>
                <div style="font-size: 0.8rem; color: #6b7280; font-weight: 600;">Live on Indeed</div>
            </div>
        </div>
    </div>

    <!-- Job Postings List -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div class="card-title" style="font-size: 1.25rem; font-weight: 700; color: #1f2937;">
                All Active Openings
            </div>
            <span style="font-size: 0.85rem; color: var(--text-muted);">
                Showing {{ $jobs->count() }} position(s)
            </span>
        </div>

        @if($jobs->isEmpty())
            <div style="text-align: center; padding: 3rem 1rem;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📢</div>
                <h3 style="font-size: 1.2rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem;">No jobs posted yet</h3>
                <p style="color: #6b7280; font-size: 0.9rem; max-width: 450px; margin: 0 auto 1.5rem auto;">
                    Publish your first job vacancy. With a single click, it will be automatically distributed across LinkedIn, Naukri, and Indeed.
                </p>
                <a href="{{ route('company.jobs.create') }}" class="btn-primary" style="padding: 0.75rem 1.5rem;">
                    🚀 Create & Distribute First Job
                </a>
            </div>
        @else
            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                @foreach($jobs as $job)
                    <div style="background: rgba(255,255,255,0.7); border: 1px solid rgba(229,231,235,0.9); border-radius: 16px; padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.25rem; transition: transform 0.2s ease;">
                        <div style="flex: 1 1 260px; min-width: 0; display: flex; align-items: flex-start; gap: 1rem;">
                            @if($job->image_path)
                                <a href="{{ route('company.jobs.show', $job->id) }}" style="flex-shrink: 0;">
                                    <img src="{{ asset('storage/' . $job->image_path) }}" alt="{{ $job->title }}" style="width: 52px; height: 52px; object-fit: cover; border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                </a>
                            @endif
                            <div style="flex: 1;">
                                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.4rem;">
                                    <h3 style="font-size: 1.15rem; font-weight: 700; color: #1e1b4b; margin: 0;">
                                        <a href="{{ route('company.jobs.show', $job->id) }}" style="color: inherit; text-decoration: none;">
                                            {{ $job->title }}
                                        </a>
                                    </h3>
                                    <span class="badge" style="background: #e0e7ff; color: #3730a3; font-size: 0.75rem;">
                                        {{ $job->job_type }}
                                    </span>
                                </div>

                            <div style="display: flex; flex-wrap: wrap; gap: 1rem; font-size: 0.85rem; color: #4b5563; margin-bottom: 0.75rem;">
                                @if($job->location)
                                    <span>📍 {{ $job->location }}</span>
                                @endif
                                @if($job->salary_range)
                                    <span>💰 {{ $job->salary_range }}</span>
                                @endif
                                @if($job->experience_required)
                                    <span>⏳ {{ $job->experience_required }}</span>
                                @endif
                                <span>📅 Posted {{ $job->created_at->diffForHumans() }}</span>
                            </div>

                            <!-- Platform Status Badges -->
                            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                <span style="font-size: 0.75rem; font-weight: 600; color: #6b7280;">1-Click Platforms:</span>
                                
                                <!-- LinkedIn -->
                                @if($job->linkedin_status === 'posted')
                                    <span class="badge" style="background: #0077b5; color: white; display: inline-flex; align-items: center; gap: 0.3rem;">
                                        ✓ LinkedIn
                                    </span>
                                @elseif($job->linkedin_status === 'failed')
                                    <span class="badge" style="background: #fee2e2; color: #991b1b;">
                                        ✗ LinkedIn (Failed)
                                    </span>
                                @else
                                    <span class="badge" style="background: #f3f4f6; color: #9ca3af;">
                                        ○ LinkedIn
                                    </span>
                                @endif

                                <!-- Naukri -->
                                @if($job->naukri_status === 'posted')
                                    <span class="badge" style="background: #2563eb; color: white; display: inline-flex; align-items: center; gap: 0.3rem;">
                                        ✓ Naukri
                                    </span>
                                @elseif($job->naukri_status === 'failed')
                                    <span class="badge" style="background: #fee2e2; color: #991b1b;">
                                        ✗ Naukri (Failed)
                                    </span>
                                @else
                                    <span class="badge" style="background: #f3f4f6; color: #9ca3af;">
                                        ○ Naukri
                                    </span>
                                @endif

                                <!-- Indeed -->
                                @if($job->indeed_status === 'posted')
                                    <span class="badge" style="background: #0a66c2; color: white; display: inline-flex; align-items: center; gap: 0.3rem;">
                                        ✓ Indeed
                                    </span>
                                @elseif($job->indeed_status === 'failed')
                                    <span class="badge" style="background: #fee2e2; color: #991b1b;">
                                        ✗ Indeed (Failed)
                                    </span>
                                @else
                                    <span class="badge" style="background: #f3f4f6; color: #9ca3af;">
                                        ○ Indeed
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                            <a href="{{ route('company.jobs.show', $job->id) }}" class="btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">
                                View Details
                            </a>

                            <!-- 1-Click Re-sync -->
                            <form action="{{ route('company.jobs.repost', $job->id) }}" method="POST" style="margin: 0;">
                                @csrf
                                <input type="hidden" name="platforms[]" value="linkedin">
                                <input type="hidden" name="platforms[]" value="naukri">
                                <input type="hidden" name="platforms[]" value="indeed">
                                <button type="submit" class="btn" style="background: #3b82f6; color: white; padding: 0.5rem 1rem; font-size: 0.85rem;" title="Re-sync and post to LinkedIn, Naukri, and Indeed in one click">
                                    🔄 1-Click Sync
                                </button>
                            </form>

                            <!-- Delete -->
                            <form action="{{ route('company.jobs.destroy', $job->id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete this job posting?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background: transparent; border: 1px solid #f87171; color: #dc2626; padding: 0.5rem 0.8rem; border-radius: 12px; cursor: pointer; font-size: 0.85rem; font-weight: 600;">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
