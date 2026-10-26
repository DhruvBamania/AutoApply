<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You've Been Scouted: {{ $job->title }}</title>
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
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #4338ca 100%);
            padding: 36px 32px 30px;
            text-align: center;
            color: #ffffff;
        }
        .badge {
            display: inline-block;
            background-color: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #fbbf24;
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
            margin-bottom: 12px;
        }
        .highlight-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #4f46e5;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .role-title {
            font-size: 20px;
            font-weight: 800;
            color: #1e1b4b;
            margin: 0 0 4px;
        }
        .company-name {
            font-size: 14px;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 14px;
        }
        .match-pill {
            display: inline-block;
            background-color: #ecfdf5;
            color: #059669;
            font-weight: 700;
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 20px;
            border: 1px solid #a7f3d0;
            margin-bottom: 12px;
        }
        .meta-grid {
            display: table;
            width: 100%;
            margin-top: 10px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 10px;
        }
        .meta-item {
            display: table-cell;
            font-size: 13px;
            color: #475569;
        }
        .meta-item strong {
            color: #0f172a;
        }
        .outreach-quote {
            background-color: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 12px;
            padding: 18px 20px;
            color: #312e81;
            font-size: 14px;
            line-height: 1.6;
            margin: 20px 0;
            font-style: italic;
        }
        .reasons-list {
            margin: 16px 0;
            padding-left: 20px;
            color: #334155;
            font-size: 13px;
        }
        .reasons-list li {
            margin-bottom: 6px;
        }
        .btn-container {
            text-align: center;
            margin: 28px 0 10px;
        }
        .btn-primary {
            display: inline-block;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 15px;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }
        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 24px 32px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="badge">🎯 AI Headhunter Scout</span>
            <h1>You've Been Scouted!</h1>
            <p>{{ $company->company_name ?? 'An Employer' }} is looking for your exact skills.</p>
        </div>

        <div class="body-content">
            <div class="greeting">Hi {{ $match->candidate_name }},</div>
            <p>
                Our AI Talent Scout matched your technical profile as an exceptional fit for an open role at 
                <strong>{{ $company->company_name ?? 'our partner company' }}</strong>.
            </p>

            <div class="highlight-box">
                <span class="match-pill">⭐ {{ $match->match_score }}% Match Score</span>
                <div class="role-title">{{ $job->title }}</div>
                <div class="company-name">{{ $company->company_name ?? 'Confidential Tech Enterprise' }} &bull; {{ $job->location }}</div>

                <div class="meta-grid">
                    <div class="meta-item"><strong>Job Type:</strong> {{ $job->job_type }}</div>
                    @if($job->salary_range)
                    <div class="meta-item"><strong>Compensation:</strong> {{ $job->salary_range }}</div>
                    @endif
                    @if($job->experience_required)
                    <div class="meta-item"><strong>Seniority:</strong> {{ $job->experience_required }}</div>
                    @endif
                </div>
            </div>

            @if($match->outreach_message)
            <div style="font-weight: 700; font-size: 13px; color: #4338ca; text-transform: uppercase; letter-spacing: 0.5px;">Message from Hiring Team:</div>
            <div class="outreach-quote">
                "{{ $match->outreach_message }}"
            </div>
            @endif

            @if(!empty($match->match_reasons))
            <div style="font-weight: 700; font-size: 13px; color: #0f172a; margin-top: 18px;">Why You Were Scouted:</div>
            <ul class="reasons-list">
                @foreach($match->match_reasons as $reason)
                <li>{{ $reason }}</li>
                @endforeach
            </ul>
            @endif

            <div class="btn-container">
                <a href="{{ url('/dashboard') }}" class="btn-primary">View Opportunity &amp; Accept</a>
            </div>
            <p style="text-align: center; font-size: 12px; color: #64748b; margin-top: 10px;">
                Log into your AI Looping candidate portal to fast-track your connection with this company.
            </p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} AI Looping Talent Intelligence. All rights reserved.<br>
            Automated headhunter invitation sent on behalf of {{ $company->company_name ?? 'Employer' }}.
        </div>
    </div>
</body>
</html>
