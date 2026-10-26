@extends('layouts.app')

@section('content')
    @php
        $linkedIn = $accounts->get('linkedin');
        $naukri = $accounts->get('naukri');
        $indeed = $accounts->get('indeed');
    @endphp

    <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="margin-top: 0.5rem;">
                {{ auth()->user()->isCompany() ? 'Platform & Recruiter Integrations' : 'Social & Profile Integrations' }}
            </h1>
            <p class="subtitle" style="margin-bottom: 0;">
                {{ auth()->user()->isCompany()
                    ? 'Connect your company accounts to publish actual live job postings directly to LinkedIn, Naukri, and Indeed.'
                    : 'Connect your accounts to enable automated AI posting and profile updates.' }}
            </p>
        </div>
        <a href="{{ auth()->user()->isCompany() ? route('company.dashboard') : route('dashboard') }}" class="btn btn-secondary">
            ← Back to Dashboard
        </a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 300px), 1fr)); gap: 1.5rem;">
        
        <!-- 1. LinkedIn Card -->
        <div class="card" style="border-color: rgba(10, 102, 194, 0.35); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="card-header" style="margin-bottom: 1rem;">
                    <div class="card-title" style="color: #0a66c2; display: flex; align-items: center; gap: 0.5rem;">
                        <span>💼</span> LinkedIn Feed & Jobs
                    </div>
                    @if($linkedIn)
                        <span class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac;">🟢 Connected</span>
                    @else
                        <span class="badge" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;">🔴 Disconnected</span>
                    @endif
                </div>

                <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.5; margin-bottom: 1.25rem;">
                    {{ auth()->user()->isCompany()
                        ? 'Connect via official LinkedIn OAuth (w_member_social) to publish hiring announcements to your feed and network.'
                        : 'Connect via OAuth to post directly to your professional feed.' }}
                </p>

                @if($linkedIn)
                    <div style="background: rgba(10, 102, 194, 0.06); padding: 0.85rem 1rem; border-radius: 12px; margin-bottom: 1.25rem; font-size: 0.85rem;">
                        <div style="font-weight: 600; color: #0a66c2;">Active LinkedIn Account:</div>
                        <div style="color: #1f2937; word-break: break-all; margin-top: 0.2rem;">{{ $linkedIn->account_identifier }}</div>
                    </div>
                @endif
            </div>

            <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap; margin-top: 1rem;">
                @if($linkedIn)
                    <a href="{{ route('linkedin.redirect') }}" class="btn btn-secondary" style="font-size: 0.85rem; padding: 0.5rem 1rem;">
                        🔄 Reconnect
                    </a>
                    <form action="{{ route('integrations.disconnect', 'linkedin') }}" method="POST" onsubmit="return confirm('Disconnect LinkedIn account?');" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn" style="background: #fee2e2; color: #b91c1c; font-size: 0.85rem; padding: 0.5rem 1rem; box-shadow: none;">
                            Disconnect
                        </button>
                    </form>
                @else
                    <div style="display: flex; flex-direction: column; gap: 0.5rem; width: 100%;">
                        <a href="{{ route('linkedin.redirect') }}" class="btn btn-primary" style="background: #0a66c2; color: white; width: 100%; text-align: center;">
                            🔗 Connect LinkedIn Account
                        </a>
                        <form action="{{ route('integrations.store', 'linkedin') }}" method="POST" style="margin: 0;">
                            @csrf
                            <input type="hidden" name="connection_type" value="sandbox">
                            <button type="submit" class="btn" style="background: #f1f5f9; color: #475569; width: 100%; border: 1px dashed #94a3b8; font-size: 0.8rem; cursor: pointer; padding: 0.45rem; border-radius: 8px;">
                                🧪 Connect Developer Sandbox (No Account Needed)
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <!-- 2. Naukri.com Card -->
        <div class="card" style="border-color: rgba(30, 162, 185, 0.35); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="card-header" style="margin-bottom: 1rem;">
                    <div class="card-title" style="color: #1ea2b9; display: flex; align-items: center; gap: 0.5rem;">
                        <span>🔵</span> Naukri.com Recruiter
                    </div>
                    @if($naukri)
                        <span class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac;">🟢 Connected</span>
                    @else
                        <span class="badge" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;">🔴 Disconnected</span>
                    @endif
                </div>

                <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.5; margin-bottom: 1rem;">
                    Connect your Naukri Recruiter account or RMS API Key to publish job postings to India's premier candidate network.
                </p>

                @if($naukri)
                    <div style="background: rgba(30, 162, 185, 0.06); padding: 0.85rem 1rem; border-radius: 12px; margin-bottom: 1.25rem; font-size: 0.85rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="font-weight: 600; color: #1ea2b9;">Active Naukri Connection:</div>
                            <span style="font-size: 0.75rem; background: #e0f2fe; color: #0284c7; padding: 0.15rem 0.5rem; border-radius: 6px; font-weight: 600;">
                                {{ ucfirst($naukri->connection_type ?? 'Credentials') }}
                            </span>
                        </div>
                        <div style="color: #1f2937; word-break: break-all; margin-top: 0.3rem;">
                            {{ $naukri->account_identifier }}
                        </div>
                    </div>
                @endif

                <!-- Switcher for Credentials vs API Key vs Sandbox -->
                <div style="display: flex; gap: 0.35rem; margin-bottom: 1rem; flex-wrap: wrap;">
                    <button type="button" onclick="switchNaukriMode('credentials')" id="naukri-tab-cred" class="btn btn-secondary" style="font-size: 0.75rem; padding: 0.35rem 0.5rem; flex: 1 1 90px;">
                        Recruiter Login
                    </button>
                    <button type="button" onclick="switchNaukriMode('api_key')" id="naukri-tab-api" class="btn btn-secondary" style="font-size: 0.75rem; padding: 0.35rem 0.5rem; flex: 1 1 90px;">
                        RMS API Key
                    </button>
                    <button type="button" onclick="switchNaukriMode('sandbox')" id="naukri-tab-sandbox" class="btn btn-secondary" style="font-size: 0.75rem; padding: 0.35rem 0.5rem; flex: 1 1 90px; border: 1px dashed #1ea2b9;">
                        🧪 Sandbox
                    </button>
                </div>

                <!-- Form 1: Credentials -->
                <form action="{{ route('integrations.store', 'naukri') }}" method="POST" id="naukri-form-credentials">
                    @csrf
                    <input type="hidden" name="connection_type" value="credentials">
                    <div class="form-group" style="margin-bottom: 0.75rem;">
                        <label class="form-label" style="font-size: 0.85rem;">Recruiter Email / Username</label>
                        <input type="text" name="account_identifier" class="form-control" value="{{ ($naukri && $naukri->connection_type === 'credentials') ? $naukri->account_identifier : '' }}" placeholder="recruiter@company.com" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" style="font-size: 0.85rem;">Recruiter Password</label>
                        <input type="password" name="password" class="form-control" placeholder="{{ $naukri ? '••••••••' : 'Enter password' }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="background: #1ea2b9; color: white; width: 100%;">
                        {{ $naukri ? 'Update Credentials' : 'Save & Connect Naukri' }}
                    </button>
                </form>

                <!-- Form 2: RMS API Key -->
                <form action="{{ route('integrations.store', 'naukri') }}" method="POST" id="naukri-form-api" style="display: none;">
                    @csrf
                    <input type="hidden" name="connection_type" value="api_key">
                    <div class="form-group" style="margin-bottom: 0.75rem;">
                        <label class="form-label" style="font-size: 0.85rem;">Client ID / App Name</label>
                        <input type="text" name="client_id" class="form-control" placeholder="e.g. MyCompany RMS Client" value="{{ ($naukri && $naukri->connection_type === 'api_key') ? $naukri->account_identifier : '' }}">
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" style="font-size: 0.85rem;">Naukri RMS API Key / Secret</label>
                        <input type="password" name="api_key" class="form-control" placeholder="Enter RMS API Token" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="background: #1ea2b9; color: white; width: 100%;">
                        Save & Connect RMS API Key
                    </button>
                </form>

                <!-- Form 3: Sandbox Mode (Instant Test without Employer Account) -->
                <div id="naukri-form-sandbox" style="display: none; background: rgba(30, 162, 185, 0.05); border: 1px dashed #1ea2b9; padding: 0.85rem; border-radius: 8px; margin-bottom: 0.75rem;">
                    <p style="font-size: 0.8rem; color: #374151; margin: 0 0 0.65rem 0; line-height: 1.4;">
                        Don't have a paid Naukri Recruiter account? Activate Sandbox mode to test job posting and applicant inquiry retrieval instantly.
                    </p>
                    <form action="{{ route('integrations.store', 'naukri') }}" method="POST">
                        @csrf
                        <input type="hidden" name="connection_type" value="sandbox">
                        <button type="submit" class="btn btn-primary" style="background: #1ea2b9; color: white; width: 100%; font-size: 0.85rem;">
                            🧪 Activate Naukri Sandbox Mode
                        </button>
                    </form>
                </div>
            </div>

            @if($naukri)
                <div style="margin-top: 1rem; border-top: 1px solid rgba(0,0,0,0.06); padding-top: 0.75rem;">
                    <form action="{{ route('integrations.disconnect', 'naukri') }}" method="POST" onsubmit="return confirm('Disconnect Naukri account?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn" style="background: #fee2e2; color: #b91c1c; font-size: 0.85rem; width: 100%; box-shadow: none;">
                            Disconnect Naukri
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <!-- 3. Indeed Employer Card -->
        <div class="card" style="border-color: rgba(37, 87, 167, 0.35); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="card-header" style="margin-bottom: 1rem;">
                    <div class="card-title" style="color: #2557a7; display: flex; align-items: center; gap: 0.5rem;">
                        <span>🔷</span> Indeed Employer & Feed
                    </div>
                    @if($indeed)
                        <span class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac;">🟢 Connected</span>
                    @else
                        <span class="badge" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;">🔴 Disconnected</span>
                    @endif
                </div>

                <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.5; margin-bottom: 1rem;">
                    Connect your Indeed Employer account, API Key, or enable the ATS XML Feed to syndicate all job postings to Indeed.
                </p>

                @if($indeed)
                    <div style="background: rgba(37, 87, 167, 0.06); padding: 0.85rem 1rem; border-radius: 12px; margin-bottom: 1.25rem; font-size: 0.85rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="font-weight: 600; color: #2557a7;">Active Indeed Connection:</div>
                            <span style="font-size: 0.75rem; background: #e0f2fe; color: #0284c7; padding: 0.15rem 0.5rem; border-radius: 6px; font-weight: 600;">
                                {{ ucfirst($indeed->connection_type ?? 'Credentials') }}
                            </span>
                        </div>
                        <div style="color: #1f2937; word-break: break-all; margin-top: 0.3rem;">
                            {{ $indeed->account_identifier }}
                        </div>
                    </div>
                @endif

                <!-- Switcher for Indeed Tabs -->
                <div style="display: flex; gap: 0.35rem; margin-bottom: 1rem; flex-wrap: wrap;">
                    <button type="button" onclick="switchIndeedMode('feed')" id="indeed-tab-feed" class="btn btn-secondary" style="font-size: 0.75rem; padding: 0.35rem 0.4rem; flex: 1 1 80px;">
                        ATS XML Feed
                    </button>
                    <button type="button" onclick="switchIndeedMode('credentials')" id="indeed-tab-cred" class="btn btn-secondary" style="font-size: 0.75rem; padding: 0.35rem 0.4rem; flex: 1 1 80px;">
                        Employer Login
                    </button>
                    <button type="button" onclick="switchIndeedMode('api_key')" id="indeed-tab-api" class="btn btn-secondary" style="font-size: 0.75rem; padding: 0.35rem 0.4rem; flex: 1 1 70px;">
                        API Key
                    </button>
                    <button type="button" onclick="switchIndeedMode('sandbox')" id="indeed-tab-sandbox" class="btn btn-secondary" style="font-size: 0.75rem; padding: 0.35rem 0.4rem; flex: 1 1 70px; border: 1px dashed #2557a7;">
                        🧪 Sandbox
                    </button>
                </div>

                <!-- Tab 1: ATS XML Feed (Recommended Free Integration) -->
                <div id="indeed-pane-feed">
                    @if(auth()->user()->isCompany())
                        <div style="background: rgba(37, 87, 167, 0.04); border: 1px dashed rgba(37, 87, 167, 0.4); padding: 0.85rem; border-radius: 10px; margin-bottom: 1rem;">
                            <label class="form-label" style="font-size: 0.8rem; color: #2557a7; margin-bottom: 0.3rem;">
                                Your Live Indeed ATS XML Feed URL:
                            </label>
                            <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                                <input type="text" id="feed-url-input" class="form-control" style="font-size: 0.75rem; padding: 0.4rem 0.6rem; flex: 1 1 180px; min-width: 0;" value="{{ route('feed.indeed', auth()->id()) }}" readonly>
                                <button type="button" onclick="copyFeedUrl()" class="btn btn-secondary" style="font-size: 0.75rem; padding: 0.4rem 0.75rem; white-space: nowrap;">
                                    📋 Copy
                                </button>
                            </div>
                            <p style="font-size: 0.75rem; color: #6b7280; margin: 0.5rem 0 0 0;">
                                Provide this URL to your Indeed account manager or ATS settings for automated syndication.
                            </p>
                        </div>

                        <form action="{{ route('integrations.store', 'indeed') }}" method="POST">
                            @csrf
                            <input type="hidden" name="connection_type" value="feed">
                            <button type="submit" class="btn btn-primary" style="background: #2557a7; color: white; width: 100%;">
                                {{ ($indeed && $indeed->connection_type === 'feed') ? '✓ XML Feed Active' : 'Activate Indeed ATS XML Feed' }}
                            </button>
                        </form>
                    @else
                        <p style="font-size: 0.85rem; color: var(--text-muted);">
                            ATS XML Feed is enabled for registered employer accounts.
                        </p>
                    @endif
                </div>

                <!-- Tab 2: Employer Credentials -->
                <form action="{{ route('integrations.store', 'indeed') }}" method="POST" id="indeed-pane-credentials" style="display: none;">
                    @csrf
                    <input type="hidden" name="connection_type" value="credentials">
                    <div class="form-group" style="margin-bottom: 0.75rem;">
                        <label class="form-label" style="font-size: 0.85rem;">Employer Email</label>
                        <input type="email" name="account_identifier" class="form-control" value="{{ ($indeed && $indeed->connection_type === 'credentials') ? $indeed->account_identifier : '' }}" placeholder="employer@company.com" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" style="font-size: 0.85rem;">Employer Password</label>
                        <input type="password" name="password" class="form-control" placeholder="{{ $indeed ? '••••••••' : 'Enter password' }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="background: #2557a7; color: white; width: 100%;">
                        Save Indeed Credentials
                    </button>
                </form>

                <!-- Tab 3: Indeed API Key -->
                <form action="{{ route('integrations.store', 'indeed') }}" method="POST" id="indeed-pane-api" style="display: none;">
                    @csrf
                    <input type="hidden" name="connection_type" value="api_key">
                    <div class="form-group" style="margin-bottom: 0.75rem;">
                        <label class="form-label" style="font-size: 0.85rem;">Client ID</label>
                        <input type="text" name="client_id" class="form-control" placeholder="Indeed Employer Client ID" value="{{ ($indeed && $indeed->connection_type === 'api_key') ? $indeed->account_identifier : '' }}">
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" style="font-size: 0.85rem;">API Key / Secret Token</label>
                        <input type="password" name="api_key" class="form-control" placeholder="Enter Indeed API Secret" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="background: #2557a7; color: white; width: 100%;">
                        Save Indeed API Key
                    </button>
                </form>

                <!-- Tab 4: Indeed Sandbox Mode -->
                <div id="indeed-pane-sandbox" style="display: none; background: rgba(37, 87, 167, 0.05); border: 1px dashed #2557a7; padding: 0.85rem; border-radius: 8px; margin-bottom: 0.75rem;">
                    <p style="font-size: 0.8rem; color: #374151; margin: 0 0 0.65rem 0; line-height: 1.4;">
                        Test direct API job posting to Indeed without live employer API keys.
                    </p>
                    <form action="{{ route('integrations.store', 'indeed') }}" method="POST">
                        @csrf
                        <input type="hidden" name="connection_type" value="sandbox">
                        <button type="submit" class="btn btn-primary" style="background: #2557a7; color: white; width: 100%; font-size: 0.85rem;">
                            🧪 Activate Indeed Sandbox Mode
                        </button>
                    </form>
                </div>
            </div>

            @if($indeed)
                <div style="margin-top: 1rem; border-top: 1px solid rgba(0,0,0,0.06); padding-top: 0.75rem;">
                    <form action="{{ route('integrations.disconnect', 'indeed') }}" method="POST" onsubmit="return confirm('Disconnect Indeed account?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn" style="background: #fee2e2; color: #b91c1c; font-size: 0.85rem; width: 100%; box-shadow: none;">
                            Disconnect Indeed
                        </button>
                    </form>
                </div>
            @endif
        </div>

    </div>

    <script>
        function switchNaukriMode(mode) {
            const formCred = document.getElementById('naukri-form-credentials');
            const formApi = document.getElementById('naukri-form-api');
            const formSandbox = document.getElementById('naukri-form-sandbox');
            const btnCred = document.getElementById('naukri-tab-cred');
            const btnApi = document.getElementById('naukri-tab-api');
            const btnSandbox = document.getElementById('naukri-tab-sandbox');

            [formCred, formApi, formSandbox].forEach(f => { if (f) f.style.display = 'none'; });
            [btnCred, btnApi, btnSandbox].forEach(b => {
                if (b) {
                    b.style.background = 'white';
                    b.style.color = '#4c51bf';
                }
            });

            if (mode === 'api_key') {
                formApi.style.display = 'block';
                btnApi.style.background = '#1ea2b9';
                btnApi.style.color = 'white';
            } else if (mode === 'sandbox') {
                formSandbox.style.display = 'block';
                btnSandbox.style.background = '#1ea2b9';
                btnSandbox.style.color = 'white';
            } else {
                formCred.style.display = 'block';
                btnCred.style.background = '#1ea2b9';
                btnCred.style.color = 'white';
            }
        }

        function switchIndeedMode(mode) {
            const paneFeed = document.getElementById('indeed-pane-feed');
            const paneCred = document.getElementById('indeed-pane-credentials');
            const paneApi = document.getElementById('indeed-pane-api');
            const paneSandbox = document.getElementById('indeed-pane-sandbox');
            const tabFeed = document.getElementById('indeed-tab-feed');
            const tabCred = document.getElementById('indeed-tab-cred');
            const tabApi = document.getElementById('indeed-tab-api');
            const tabSandbox = document.getElementById('indeed-tab-sandbox');

            [paneFeed, paneCred, paneApi, paneSandbox].forEach(p => { if (p) p.style.display = 'none'; });
            [tabFeed, tabCred, tabApi, tabSandbox].forEach(t => {
                if (t) {
                    t.style.background = 'white';
                    t.style.color = '#4c51bf';
                }
            });

            if (mode === 'credentials') {
                paneCred.style.display = 'block';
                tabCred.style.background = '#2557a7';
                tabCred.style.color = 'white';
            } else if (mode === 'api_key') {
                paneApi.style.display = 'block';
                tabApi.style.background = '#2557a7';
                tabApi.style.color = 'white';
            } else if (mode === 'sandbox') {
                paneSandbox.style.display = 'block';
                tabSandbox.style.background = '#2557a7';
                tabSandbox.style.color = 'white';
            } else {
                paneFeed.style.display = 'block';
                tabFeed.style.background = '#2557a7';
                tabFeed.style.color = 'white';
            }
        }

        function copyFeedUrl() {
            const input = document.getElementById('feed-url-input');
            input.select();
            input.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(input.value);
            alert('Indeed ATS XML Feed URL copied to clipboard!');
        }
    </script>
@endsection
