@extends('layouts.app')

@section('content')
<div style="max-width: 1100px; width: 100%; margin: 0 auto; display: flex; flex-direction: column; gap: 2rem;">

    <!-- Company Welcome Header -->
    <div class="card" style="background: linear-gradient(135deg, rgba(255,255,255,0.7) 0%, rgba(238,242,255,0.85) 100%); border-left: 6px solid #4c51bf;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                    <span style="font-size: 2rem;">🏢</span>
                    <h1 style="font-size: 1.8rem; font-weight: 700; color: #1e1b4b; margin: 0;">
                        {{ $company->company_name ?? $user->name }}
                    </h1>
                    <span class="badge" style="background: #e0e7ff; color: #3730a3; font-weight: 600;">
                        ✓ Company Account
                    </span>
                </div>
                <p style="color: #4b5563; font-size: 0.95rem; margin: 0;">
                    Welcome to your Employer Portal. Manage your organization profile and prepare for incoming recruitment automations.
                </p>
            </div>
            <div>
                <span style="font-size: 0.85rem; color: #6b7280; background: rgba(255,255,255,0.8); padding: 0.4rem 0.8rem; border-radius: 8px; border: 1px solid rgba(229,231,235,0.8);">
                    Signed in as: <strong>{{ $user->email }}</strong>
                </span>
            </div>
        </div>
    </div>

    <!-- Company Profile Details & Stats Grid -->
    <div style="display: grid; grid-template-columns: 1.3fr 1fr; gap: 2rem;" class="grid-2">

        <!-- Company Profile Card -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="card-title" style="font-size: 1.2rem; font-weight: 700; color: #1f2937; display: flex; align-items: center; gap: 0.5rem;">
                    <span>📋</span> Organization Profile
                </div>
                <span class="badge badge-running">Active</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 1.25rem; font-size: 0.95rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 0.5rem; flex-wrap: wrap; gap: 0.25rem;">
                    <span style="color: var(--text-muted); font-weight: 500;">Company Name</span>
                    <span style="font-weight: 600; color: #111827;">{{ $company->company_name ?? 'N/A' }}</span>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 0.5rem; flex-wrap: wrap; gap: 0.25rem;">
                    <span style="color: var(--text-muted); font-weight: 500;">Official Website</span>
                    <span style="word-break: break-all;">
                        @if(!empty($company->website))
                            <a href="{{ Str::startsWith($company->website, ['http://', 'https://']) ? $company->website : 'https://' . $company->website }}" target="_blank" style="color: #4c51bf; font-weight: 600; text-decoration: none;">
                                🌐 {{ $company->website }}
                            </a>
                        @else
                            <span style="color: #9ca3af;">Not provided</span>
                        @endif
                    </span>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 0.5rem; flex-wrap: wrap; gap: 0.25rem;">
                    <span style="color: var(--text-muted); font-weight: 500;">Location / Headquarters</span>
                    <span style="font-weight: 600; color: #111827;">
                        📍 {{ $company->location ?? 'Not specified' }}
                    </span>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 0.5rem; flex-wrap: wrap; gap: 0.25rem;">
                    <span style="color: var(--text-muted); font-weight: 500;">Account Representative</span>
                    <span style="font-weight: 600; color: #111827;">{{ $user->name }}</span>
                </div>

                <div>
                    <span style="color: var(--text-muted); font-weight: 500; display: block; margin-bottom: 0.4rem;">About Company</span>
                    <div style="background: rgba(255,255,255,0.6); padding: 0.85rem 1rem; border-radius: 12px; border: 1px solid rgba(229,231,235,0.8); color: #374151; line-height: 1.5; font-size: 0.9rem;">
                        {{ $company->description ?? 'No description provided yet.' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Summary & Status -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            <div class="card" style="background: white;">
                <div class="card-title" style="font-size: 1.1rem; font-weight: 700; color: #1f2937; margin-bottom: 1rem;">
                    📊 Hiring Metrics Overview
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); gap: 0.75rem;">
                    <div style="background: #f8fafc; padding: 1rem; border-radius: 14px; text-align: center; border: 1px solid #e2e8f0;">
                        <div style="font-size: 1.75rem; font-weight: 700; color: #4c51bf;">{{ $jobsCount ?? 0 }}</div>
                        <div style="font-size: 0.8rem; color: #64748b; font-weight: 600;">Active Jobs</div>
                    </div>
                    <div style="background: #f8fafc; padding: 1rem; border-radius: 14px; text-align: center; border: 1px solid #e2e8f0;">
                        <div style="font-size: 1.75rem; font-weight: 700; color: #059669;">0</div>
                        <div style="font-size: 0.8rem; color: #64748b; font-weight: 600;">Candidates Found</div>
                    </div>
                    <div style="background: #f8fafc; padding: 1rem; border-radius: 14px; text-align: center; border: 1px solid #e2e8f0;">
                        <div style="font-size: 1.75rem; font-weight: 700; color: #d97706;">0</div>
                        <div style="font-size: 0.8rem; color: #64748b; font-weight: 600;">Interviews Set</div>
                    </div>
                    <div style="background: #f8fafc; padding: 1rem; border-radius: 14px; text-align: center; border: 1px solid #e2e8f0;">
                        <div style="font-size: 1.75rem; font-weight: 700; color: #dc2626;">0</div>
                        <div style="font-size: 0.8rem; color: #64748b; font-weight: 600;">Automations Active</div>
                    </div>
                </div>
            </div>

            <div class="card" style="background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%); border: 1px solid #c7d2fe;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span style="font-size: 1.5rem;">🔒</span>
                    <div>
                        <div style="font-weight: 700; color: #312e81; font-size: 0.95rem;">Dedicated Company Space</div>
                        <div style="font-size: 0.85rem; color: #4338ca;">Your company profile is isolated with custom employer workflows and security.</div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
