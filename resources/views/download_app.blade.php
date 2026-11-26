@extends('layouts.app')

@section('content')
<div style="max-width: 860px; width: 100%; margin: 0 auto; display: flex; flex-direction: column; gap: 2rem;">

    <!-- Hero Card -->
    <div class="card" style="background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(238,242,255,0.95) 100%); border-left: 6px solid #4f46e5; text-align: center; padding: 2.5rem 1.5rem;">
        <div style="font-size: 3.5rem; margin-bottom: 0.5rem;">📱</div>
        <h1 style="font-size: clamp(1.6rem, 3.5vw, 2.3rem); font-weight: 800; color: #1e1b4b; margin-bottom: 0.5rem;">
            Download AI Looping Mobile App
        </h1>
        <p style="color: #4b5563; font-size: 1.05rem; max-width: 600px; margin: 0 auto 1.5rem auto; line-height: 1.5;">
            Experience recruitment automations, 1-click job postings, candidate matching, and AI interview preparation on your Android device.
        </p>

        <div style="display: flex; justify-content: center; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
            <span class="badge" style="background: #e0e7ff; color: #3730a3; font-size: 0.82rem; padding: 0.4rem 0.8rem;">
                🤖 Android APK (v1.0.0)
            </span>
            <span class="badge" style="background: #dcfce7; color: #166534; font-size: 0.82rem; padding: 0.4rem 0.8rem;">
                📦 Size: {{ !empty($fileSizeMb) && $fileSizeMb > 0 ? $fileSizeMb . ' MB' : 'Release Build' }}
            </span>
            <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 0.82rem; padding: 0.4rem 0.8rem;">
                ⚡ Direct Install
            </span>
        </div>

        <!-- Primary Download Buttons -->
        <div style="display: flex; justify-content: center; gap: 0.85rem; flex-wrap: wrap;">
            <a href="{{ $downloadUrl }}" class="btn-primary" style="padding: 0.85rem 1.8rem; font-size: 1rem; font-weight: 700; border-radius: 14px; box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4); text-decoration: none;">
                <span>📥</span> Download Android APK (Fast CDN)
            </a>
            <a href="{{ route('apk.download') }}" class="btn-secondary" style="padding: 0.85rem 1.4rem; font-size: 0.92rem; font-weight: 600; border-radius: 14px; text-decoration: none;">
                <span>🌐</span> Direct Mirror
            </a>
            <a href="{{ $githubReleasePage ?? 'https://github.com/DhruvBamania/AutoApply/releases' }}" target="_blank" rel="noopener noreferrer" class="btn-secondary" style="padding: 0.85rem 1.2rem; font-size: 0.92rem; font-weight: 600; border-radius: 14px; text-decoration: none;">
                <span>🐙</span> GitHub Release Assets
            </a>
        </div>
    </div>

    <!-- QR Codes & Instructions Grid -->
    <div class="grid-2">
        <!-- Scan to Download QR Code -->
        <div class="card" style="text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <div class="card-title" style="margin-bottom: 0.5rem;">📷 Scan with Your Phone Camera</div>
            <p class="text-muted" style="font-size: 0.88rem; margin-bottom: 1.25rem;">
                Point your Android camera at the QR code to start downloading the APK directly to your phone.
            </p>
            
            <div style="background: white; padding: 14px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); display: inline-block; border: 1px solid #e2e8f0; margin-bottom: 1rem;">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($downloadUrl) }}" alt="QR Code to Download Android APK" width="180" height="180" style="display: block; border-radius: 8px;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.5rem; width: 100%;">
                <div style="font-size: 0.82rem; color: #475569; font-family: monospace; word-break: break-all; background: #f8fafc; padding: 6px 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                    🔗 Direct Link: <a href="{{ $downloadUrl }}" style="color: #4f46e5; text-decoration: underline;"><strong>{{ $downloadUrl }}</strong></a>
                </div>
                <div style="font-size: 0.78rem; color: #64748b;">
                    Server Host: <strong>{{ $currentHost }}</strong>
                </div>
            </div>
        </div>

        <!-- Installation Instructions -->
        <div class="card">
            <div class="card-title" style="margin-bottom: 1rem;">🛠️ Android Installation Guide</div>
            
            <div style="display: flex; flex-direction: column; gap: 1.2rem; font-size: 0.92rem;">
                <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
                    <span style="display: inline-flex; align-items: center; justify-content: center; min-width: 28px; height: 28px; border-radius: 50%; background: #e0e7ff; color: #4338ca; font-weight: 700; font-size: 0.85rem;">1</span>
                    <div>
                        <strong style="color: #1e1b4b; display: block; margin-bottom: 2px;">Download APK & Confirm Prompt</strong>
                        <span style="color: #64748b; line-height: 1.45;">Tap the download button. When Android Chrome prompts <em>"File might be harmful. Do you want to download app-release.apk anyway?"</em>, tap <strong style="color: #1e1b4b;">Download anyway</strong>.</span>
                    </div>
                </div>

                <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
                    <span style="display: inline-flex; align-items: center; justify-content: center; min-width: 28px; height: 28px; border-radius: 50%; background: #e0e7ff; color: #4338ca; font-weight: 700; font-size: 0.85rem;">2</span>
                    <div>
                        <strong style="color: #1e1b4b; display: block; margin-bottom: 2px;">Allow Unknown Apps</strong>
                        <span style="color: #64748b; line-height: 1.45;">When opening the APK, if Android says <em>"For your security, your phone is not allowed to install unknown apps"</em>, tap <strong style="color: #1e1b4b;">Settings</strong> and switch ON <strong style="color: #1e1b4b;">Allow from this source</strong>.</span>
                    </div>
                </div>

                <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
                    <span style="display: inline-flex; align-items: center; justify-content: center; min-width: 28px; height: 28px; border-radius: 50%; background: #e0e7ff; color: #4338ca; font-weight: 700; font-size: 0.85rem;">3</span>
                    <div>
                        <strong style="color: #1e1b4b; display: block; margin-bottom: 2px;">Install & Open</strong>
                        <span style="color: #64748b; line-height: 1.45;">Tap <strong style="color: #1e1b4b;">Install</strong>, then launch AI Looping and log in to sync all candidate & recruiter data.</span>
                    </div>
                </div>

                <div style="margin-top: 0.5rem; padding: 0.75rem 1rem; background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0; font-size: 0.82rem; color: #64748b;">
                    💡 <strong>Cloud Sync:</strong> When connecting to the live server, the mobile app automatically communicates with all cloud APIs securely over HTTPS.
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Deep Link Testing Hub -->
    <div class="card" style="padding: 2rem;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 1.5rem;">⚡</span>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: #1e1b4b; margin: 0;">Interactive Deep Link Tester & Router</h3>
            </div>
            <span class="badge" style="background: #eef2ff; color: #4338ca; font-weight: 700;">
                autoapply:// & Universal Links
            </span>
        </div>
        <p class="text-muted" style="font-size: 0.9rem; margin-bottom: 1.5rem; line-height: 1.5;">
            Test deep linking on your mobile phone or browser. Tapping any button triggers the native app intent, navigating directly to that screen inside the AutoApply Mobile App.
        </p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
            <!-- Deep Link 1: Explore Jobs -->
            <a href="{{ route('app.deeplink.open', ['target' => 'jobs']) }}" class="card" style="padding: 1.1rem; border: 1px solid #e2e8f0; border-radius: 14px; text-decoration: none; transition: all 0.2s; background: #f8fafc;" onmouseover="this.style.background='#ffffff'; this.style.borderColor='#4f46e5';" onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0';">
                <div style="display: flex; align-items: center; gap: 0.65rem; margin-bottom: 0.35rem;">
                    <span style="font-size: 1.25rem;">💼</span>
                    <strong style="color: #1e1b4b; font-size: 0.95rem;">Explore Jobs Feed</strong>
                </div>
                <div style="font-size: 0.78rem; color: #64748b; font-family: monospace;">autoapply://jobs</div>
            </a>

            <!-- Deep Link 2: Scouted Opportunities -->
            <a href="{{ route('app.deeplink.open', ['target' => 'scouted']) }}" class="card" style="padding: 1.1rem; border: 1px solid #e2e8f0; border-radius: 14px; text-decoration: none; transition: all 0.2s; background: #f8fafc;" onmouseover="this.style.background='#ffffff'; this.style.borderColor='#4f46e5';" onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0';">
                <div style="display: flex; align-items: center; gap: 0.65rem; margin-bottom: 0.35rem;">
                    <span style="font-size: 1.25rem;">🎯</span>
                    <strong style="color: #1e1b4b; font-size: 0.95rem;">Scouted Invitations</strong>
                </div>
                <div style="font-size: 0.78rem; color: #64748b; font-family: monospace;">autoapply://scouted</div>
            </a>

            <!-- Deep Link 3: AI Resume Studio -->
            <a href="{{ route('app.deeplink.open', ['target' => 'resumes']) }}" class="card" style="padding: 1.1rem; border: 1px solid #e2e8f0; border-radius: 14px; text-decoration: none; transition: all 0.2s; background: #f8fafc;" onmouseover="this.style.background='#ffffff'; this.style.borderColor='#4f46e5';" onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0';">
                <div style="display: flex; align-items: center; gap: 0.65rem; margin-bottom: 0.35rem;">
                    <span style="font-size: 1.25rem;">📄</span>
                    <strong style="color: #1e1b4b; font-size: 0.95rem;">AI Resume Builder</strong>
                </div>
                <div style="font-size: 0.78rem; color: #64748b; font-family: monospace;">autoapply://resume-builder</div>
            </a>

            <!-- Deep Link 4: AI Interview Prep -->
            <a href="{{ route('app.deeplink.open', ['target' => 'interview-prep']) }}" class="card" style="padding: 1.1rem; border: 1px solid #e2e8f0; border-radius: 14px; text-decoration: none; transition: all 0.2s; background: #f8fafc;" onmouseover="this.style.background='#ffffff'; this.style.borderColor='#4f46e5';" onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0';">
                <div style="display: flex; align-items: center; gap: 0.65rem; margin-bottom: 0.35rem;">
                    <span style="font-size: 1.25rem;">💡</span>
                    <strong style="color: #1e1b4b; font-size: 0.95rem;">AI Interview Prep</strong>
                </div>
                <div style="font-size: 0.78rem; color: #64748b; font-family: monospace;">autoapply://interview-prep</div>
            </a>

            <!-- Deep Link 5: Employer Dashboard -->
            <a href="{{ route('app.deeplink.open', ['target' => 'dashboard']) }}" class="card" style="padding: 1.1rem; border: 1px solid #e2e8f0; border-radius: 14px; text-decoration: none; transition: all 0.2s; background: #f8fafc;" onmouseover="this.style.background='#ffffff'; this.style.borderColor='#4f46e5';" onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0';">
                <div style="display: flex; align-items: center; gap: 0.65rem; margin-bottom: 0.35rem;">
                    <span style="font-size: 1.25rem;">🏢</span>
                    <strong style="color: #1e1b4b; font-size: 0.95rem;">Company Dashboard</strong>
                </div>
                <div style="font-size: 0.78rem; color: #64748b; font-family: monospace;">autoapply://company/dashboard</div>
            </a>

            <!-- Deep Link 6: Inquiries & Leads -->
            <a href="{{ route('app.deeplink.open', ['target' => 'inquiries']) }}" class="card" style="padding: 1.1rem; border: 1px solid #e2e8f0; border-radius: 14px; text-decoration: none; transition: all 0.2s; background: #f8fafc;" onmouseover="this.style.background='#ffffff'; this.style.borderColor='#4f46e5';" onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0';">
                <div style="display: flex; align-items: center; gap: 0.65rem; margin-bottom: 0.35rem;">
                    <span style="font-size: 1.25rem;">💬</span>
                    <strong style="color: #1e1b4b; font-size: 0.95rem;">Recruiter Inquiries</strong>
                </div>
                <div style="font-size: 0.78rem; color: #64748b; font-family: monospace;">autoapply://company/inquiries</div>
            </a>
        </div>
    </div>

</div>
@endsection
