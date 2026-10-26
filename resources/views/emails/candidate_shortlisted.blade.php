<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interview Invitation - {{ $job->title }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            padding: 36px 32px 30px;
            text-align: center;
            color: #ffffff;
        }
        .badge {
            display: inline-block;
            background-color: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .header h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .header p {
            margin: 8px 0 0;
            font-size: 15px;
            color: #e0e7ff;
            font-weight: 500;
        }
        .body-content {
            padding: 32px 32px 24px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 14px;
        }
        .lead-text {
            font-size: 15px;
            color: #334155;
            margin-bottom: 24px;
        }
        .schedule-card {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 2px solid #e2e8f0;
            border-left: 6px solid #4f46e5;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 26px;
        }
        .schedule-card h2 {
            margin: 0 0 16px;
            font-size: 18px;
            font-weight: 800;
            color: #1e1b4b;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .detail-row {
            display: flex;
            margin-bottom: 12px;
            font-size: 14px;
        }
        .detail-label {
            font-weight: 700;
            color: #475569;
            min-width: 130px;
        }
        .detail-value {
            font-weight: 600;
            color: #0f172a;
            flex: 1;
        }
        .btn-join {
            display: inline-block;
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 15px;
            margin-top: 14px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }
        .notes-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            padding: 14px 16px;
            margin-top: 16px;
            font-size: 14px;
            color: #92400e;
        }
        .job-overview {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .job-overview h3 {
            margin: 0 0 12px;
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
        }
        .job-tag {
            display: inline-block;
            background: #eef2ff;
            color: #4338ca;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            margin-right: 6px;
            margin-bottom: 6px;
        }
        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 24px 32px;
            text-align: center;
            font-size: 13px;
            color: #64748b;
        }
        .footer a {
            color: #4f46e5;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="badge">⭐ Shortlisted for Interview</div>
            <h1>{{ $companyName }}</h1>
            <p>Interview Invitation for {{ $job->title }}</p>
        </div>

        <!-- Body Content -->
        <div class="body-content">
            <div class="greeting">
                Hello {{ $inquiry->candidate_name }},
            </div>

            <p class="lead-text">
                Great news! We have thoroughly reviewed your application and qualifications for the <strong>{{ $job->title }}</strong> position at <strong>{{ $companyName }}</strong>, and we are excited to invite you for an interview.
            </p>

            <!-- Schedule Card -->
            <div class="schedule-card">
                <h2>📅 Interview Schedule & Details</h2>

                <div class="detail-row">
                    <span class="detail-label">🗓️ Date & Time:</span>
                    <span class="detail-value">
                        @if($inquiry->interview_scheduled_at)
                            {{ \Carbon\Carbon::parse($inquiry->interview_scheduled_at)->format('l, F j, Y \a\t g:i A') }}
                        @else
                            To be coordinated
                        @endif
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">🎯 Meeting Mode:</span>
                    <span class="detail-value">
                        @if($inquiry->meeting_type === 'in_person')
                            🏢 In-Person (Office Visit)
                        @elseif($inquiry->meeting_type === 'phone')
                            📞 Phone Discussion
                        @elseif($inquiry->meeting_type === 'zoom')
                            🎥 Zoom Video Meeting
                        @else
                            💻 Google Meet / Video Conference
                        @endif
                    </span>
                </div>

                @if($inquiry->meeting_link_or_location)
                    <div class="detail-row">
                        <span class="detail-label">📍 Link / Location:</span>
                        <span class="detail-value">
                            @if(Str::startsWith($inquiry->meeting_link_or_location, ['http://', 'https://']))
                                <a href="{{ $inquiry->meeting_link_or_location }}" target="_blank" style="color: #4f46e5; font-weight: 700; word-break: break-all;">
                                    {{ $inquiry->meeting_link_or_location }}
                                </a>
                            @else
                                {{ $inquiry->meeting_link_or_location }}
                            @endif
                        </span>
                    </div>

                    @if(Str::startsWith($inquiry->meeting_link_or_location, ['http://', 'https://']))
                        <div style="text-align: center; margin-top: 16px;">
                            <a href="{{ $inquiry->meeting_link_or_location }}" class="btn-join" target="_blank">
                                🚀 Join Video Interview
                            </a>
                        </div>
                    @endif
                @endif

                @if($inquiry->interview_notes)
                    <div class="notes-box">
                        <strong>📝 Special Instructions from Recruiter:</strong><br>
                        {{ $inquiry->interview_notes }}
                    </div>
                @endif
            </div>

            <!-- Job Overview -->
            <div class="job-overview">
                <h3>💼 Position Details</h3>
                <div style="margin-bottom: 10px;">
                    <span class="job-tag">👔 {{ $job->title }}</span>
                    @if($job->location)
                        <span class="job-tag">📍 {{ $job->location }}</span>
                    @endif
                    @if($job->job_type)
                        <span class="job-tag">⏱️ {{ $job->job_type }}</span>
                    @endif
                    @if($job->salary_range)
                        <span class="job-tag">💰 {{ $job->salary_range }}</span>
                    @endif
                </div>
            </div>

            <p style="font-size: 14px; color: #475569; margin-top: 20px;">
                Please ensure you are available on time. If you have any questions or require rescheduling, you can reply directly to this email or reach us at <a href="mailto:{{ $recruiterEmail }}" style="color: #4f46e5; font-weight: 600;">{{ $recruiterEmail }}</a>.
            </p>

            <p style="font-size: 14px; color: #1e293b; font-weight: 600; margin-top: 24px;">
                Best regards,<br>
                <span style="color: #4f46e5;">{{ $companyName }} Recruitment Team</span>
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0 0 6px;">
                This automated email was sent via <strong>{{ config('app.name', 'AI Looping') }}</strong> on behalf of <strong>{{ $companyName }}</strong>.
            </p>
            <p style="margin: 0;">
                © {{ date('Y') }} {{ $companyName }}. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
