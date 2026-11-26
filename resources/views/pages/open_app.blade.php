@extends('layouts.app')

@section('content')
<div style="max-width: 640px; margin: 2rem auto; width: 100%;">
    <div class="glass-card" style="text-align: center; padding: 2.5rem 1.75rem; border-radius: 24px; box-shadow: 0 20px 45px -10px rgba(79, 70, 229, 0.18);">
        
        <!-- Animated App Icon Box -->
        <div style="width: 72px; height: 72px; margin: 0 auto 1.5rem auto; border-radius: 20px; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #ec4899 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4);">
            <svg style="width: 38px; height: 38px; color: white;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
            </svg>
        </div>

        <div style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.25rem 0.8rem; background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.25); border-radius: 9999px; font-size: 0.76rem; font-weight: 700; color: #4f46e5; margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.05em;">
            <span>⚡</span> Deep Link Router
        </div>

        <h1 style="font-size: clamp(1.4rem, 3.2vw, 1.9rem); font-weight: 800; color: #0f172a; margin-bottom: 0.5rem;">
            Opening in AutoApply App
        </h1>

        <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 1.75rem; line-height: 1.5;">
            Redirecting to <strong>{{ $targetTitle }}</strong> on your mobile device...
        </p>

        <!-- Progress Spinner & Status Indicator -->
        <div id="redirectIndicator" style="display: flex; align-items: center; justify-content: center; gap: 0.65rem; margin-bottom: 2rem; color: #4338ca; font-weight: 600; font-size: 0.9rem;">
            <div class="deeplink-spinner"></div>
            <span>Launching mobile app...</span>
        </div>

        <!-- Action Buttons -->
        <div style="display: flex; flex-direction: column; gap: 0.85rem; width: 100%;">
            <!-- Primary Deep Link Launch Button -->
            <a href="{{ $deepLinkUrl }}" id="manualLaunchBtn" class="btn btn-primary" style="padding: 0.85rem 1.25rem; font-size: 0.98rem; font-weight: 700; border-radius: 14px; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem; box-shadow: 0 6px 20px rgba(79, 70, 229, 0.35);">
                <span>📱</span> Open in AutoApply App
            </a>

            <!-- Download APK Button -->
            <a href="{{ $apkDirectUrl }}" class="btn btn-secondary" style="padding: 0.75rem 1.25rem; font-size: 0.92rem; font-weight: 600; border-radius: 14px; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                <span>📥</span> Don't Have the App? Download APK
            </a>

            <!-- Web Fallback Link -->
            <a href="{{ $webFallback }}" class="btn" style="background: transparent; color: #64748b; font-size: 0.88rem; font-weight: 500; text-decoration: underline; margin-top: 0.25rem;">
                Continue in web browser instead &rarr;
            </a>
        </div>

        <!-- Debug / Direct URI Info -->
        <div style="margin-top: 2rem; padding: 0.75rem 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; text-align: left; font-size: 0.75rem; color: #64748b; word-break: break-all;">
            <div style="font-weight: 700; color: #334155; margin-bottom: 4px;">URI Schemes:</div>
            <div>• Primary: <a href="{{ $deepLinkUrl }}" style="color: #4f46e5;"><code>{{ $deepLinkUrl }}</code></a></div>
            <div>• Alternative: <a href="{{ $altDeepLinkUrl }}" style="color: #7c3aed;"><code>{{ $altDeepLinkUrl }}</code></a></div>
        </div>

    </div>
</div>

<style>
.deeplink-spinner {
    width: 20px;
    height: 20px;
    border: 2.5px solid rgba(79, 70, 229, 0.2);
    border-top-color: #4f46e5;
    border-radius: 50%;
    animation: deeplinkSpin 0.8s linear infinite;
}
@keyframes deeplinkSpin {
    to { transform: rotate(360deg); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var deepLink = @json($deepLinkUrl);
    var webFallback = @json($webFallback);
    var isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);

    if (isMobile) {
        // Attempt immediate custom scheme invocation
        var start = Date.now();
        window.location.href = deepLink;

        // Fallback after 2.5s if app didn't capture intent
        setTimeout(function () {
            var elapsed = Date.now() - start;
            var indicator = document.getElementById('redirectIndicator');
            if (indicator && elapsed < 3500) {
                indicator.innerHTML = '<span style="color: #64748b;">If the app didn\'t open automatically, tap the button below.</span>';
            }
        }, 2200);
    } else {
        var indicator = document.getElementById('redirectIndicator');
        if (indicator) {
            indicator.innerHTML = '<span style="color: #64748b;">💻 Desktop browser detected. Click below to launch on mobile or test custom scheme.</span>';
        }
    }
});
</script>
@endsection
