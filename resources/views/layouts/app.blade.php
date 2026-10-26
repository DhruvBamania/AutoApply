<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'AI Looping') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
        
        :root {
            --surface-border: rgba(255, 255, 255, 0.5);
            --text-muted: #718096;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --primary-color: #4c51bf;
        }

        html, body {
            max-width: 100%;
            overflow-x: hidden;
            width: 100%;
        }

        body {
            background: radial-gradient(circle at 10% 20%, rgb(255, 238, 238) 0%, rgb(238, 246, 255) 50%, rgb(240, 230, 255) 100%);
            min-height: 100vh;
            color: #2d3748;
            padding: 1.25rem 0 2.5rem 0;
            position: relative;
            line-height: 1.5;
            margin: 0;
        }

        /* Decorative blur blobs */
        .blob-1 { position: fixed; top: -100px; left: -100px; width: 400px; height: 400px; background: rgba(255, 183, 178, 0.35); border-radius: 50%; filter: blur(70px); z-index: -1; pointer-events: none; }
        .blob-2 { position: fixed; bottom: -50px; right: -50px; width: 450px; height: 450px; background: rgba(181, 222, 255, 0.35); border-radius: 50%; filter: blur(80px); z-index: -1; pointer-events: none; }
        
        /* Unified Responsive App Layout Container */
        .app-layout {
            width: 100%;
            max-width: 1320px;
            margin-left: auto;
            margin-right: auto;
            padding-left: clamp(1rem, 2.5vw, 2rem);
            padding-right: clamp(1rem, 2.5vw, 2rem);
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        /* Navbar */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.85rem 1.4rem;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.85);
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.05);
            position: relative;
            z-index: 50;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }
        .logo {
            font-size: 1.35rem;
            font-weight: 700;
            color: #4c51bf;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .nav-links {
            display: flex;
            gap: 0.45rem;
            align-items: center;
            flex-wrap: wrap;
            justify-content: flex-end;
        }
        .nav-item {
            padding: 0.45rem 0.75rem;
            text-decoration: none;
            color: #4a5568;
            font-weight: 500;
            border-radius: 10px;
            transition: all 0.25s ease;
            font-size: 0.88rem;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
        }
        .nav-item:hover {
            background: rgba(255, 255, 255, 0.85);
            color: #4c51bf;
            transform: translateY(-1px);
        }
        .nav-user-email {
            max-width: 160px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: var(--text-muted);
            cursor: default;
            display: inline-block;
            vertical-align: middle;
        }
        
        .hamburger {
            display: none;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0.4rem;
            flex-direction: column;
            gap: 5px;
            z-index: 60;
        }
        .hamburger span {
            display: block;
            width: 24px;
            height: 2.5px;
            background: #4c51bf;
            border-radius: 3px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .hamburger.active span:nth-child(1) { transform: translateY(7.5px) rotate(45deg); }
        .hamburger.active span:nth-child(2) { opacity: 0; transform: scale(0.5); }
        .hamburger.active span:nth-child(3) { transform: translateY(-7.5px) rotate(-45deg); }

        /* Buttons */
        .btn-primary, .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; padding: 0.55rem 1.25rem; border: none; border-radius: 12px; font-weight: 600;
            cursor: pointer; transition: all 0.25s ease; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.35); text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; font-size: 0.9rem; min-height: 40px; text-align: center;
        }
        .btn-primary:hover, .btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5); color: white; }
        .btn-secondary {
            background: rgba(255, 255, 255, 0.85); color: #4c51bf; padding: 0.55rem 1.25rem; border: 1px solid rgba(255,255,255,0.9); border-radius: 12px; font-weight: 600;
            cursor: pointer; transition: all 0.25s ease; box-shadow: 0 3px 12px rgba(0, 0, 0, 0.04); text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; font-size: 0.9rem; min-height: 40px; text-align: center;
        }
        .btn-secondary:hover { background: white; transform: translateY(-2px); color: #3730a3; box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08); }
        .btn-sm { padding: 0.4rem 0.85rem; font-size: 0.82rem; min-height: 34px; border-radius: 10px; }
        
        /* Layout & Cards */
        .container { display: grid; gap: 2rem; max-width: 100%; width: 100%; margin: 0 auto; box-sizing: border-box; min-width: 0; }
        .grid-2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 2rem; }
        
        /* Universal Containment to prevent horizontal overflow */
        .grid-2 > *,
        .container > *,
        .card,
        .glass-card,
        .prep-layout > *,
        .prep-main,
        .prep-sidebar,
        .editor-layout > * {
            min-width: 0;
            max-width: 100%;
        }

        .glass-card, .card {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 22px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.06);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            box-sizing: border-box;
            max-width: 100%;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }
        .card-header { border-bottom: 1px solid rgba(0,0,0,0.06); padding-bottom: 1rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; }
        .card-title { font-size: 1.25rem; font-weight: 700; color: #1e1b4b; }
        
        /* Typography */
        h1, h2, h3, h4 { color: #1e1b4b; font-weight: 700; overflow-wrap: break-word; }
        h1 { font-size: clamp(1.4rem, 3.5vw, 2.2rem); margin-bottom: 0.5rem; line-height: 1.25; }
        h2 { font-size: clamp(1.2rem, 3vw, 1.75rem); margin-bottom: 0.5rem; line-height: 1.3; }
        h3 { font-size: clamp(1.05rem, 2.5vw, 1.3rem); margin-bottom: 0.4rem; line-height: 1.35; }
        p.subtitle, .text-muted { color: #64748b; line-height: 1.55; font-size: 0.95rem; }
        
        /* Forms */
        .form-group { margin-bottom: 1.25rem; width: 100%; box-sizing: border-box; }
        label, .form-label { display: block; font-size: 0.88rem; font-weight: 600; color: #334155; margin-bottom: 0.4rem; }
        input, textarea, select, .form-control {
            width: 100%; max-width: 100%; padding: 0.85rem 1rem;
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid rgba(203, 213, 225, 0.8);
            border-radius: 14px;
            font-size: 16px; /* Prevents auto zoom on mobile browsers */
            color: #1e293b;
            outline: none; transition: all 0.25s ease;
            box-sizing: border-box;
        }
        input:focus, textarea:focus, select:focus, .form-control:focus {
            background: white; border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }
        
        /* Alerts & Badges */
        .alert { padding: 1rem 1.25rem; border-radius: 14px; margin-bottom: 1.5rem; font-weight: 500; font-size: 0.92rem; line-height: 1.5; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .alert-error, .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        .badge { padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.78rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.3rem; white-space: nowrap; }
        .badge-running { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .badge-paused, .badge-drafted { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-posted { background: #e0f2fe; color: #075985; border: 1px solid #bae6fd; }

        /* Media safety */
        img, video, canvas, svg { max-width: 100%; height: auto; }
        .draft-box { background: rgba(255, 255, 255, 0.6); padding: 1.25rem; border-radius: 12px; border: 1px solid rgba(226, 232, 240, 0.9); font-family: monospace; white-space: pre-wrap; font-size: 0.88rem; line-height: 1.5; overflow-x: auto; max-width: 100%; word-break: break-word; }

        /* Tables */
        table { max-width: 100%; }
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }

        /* --- RESPONSIVE MEDIA QUERIES --- */
        
        /* Laptops, Tablets & Medium Screens (<= 1200px) */
        @media (max-width: 1200px) {
            .app-layout { padding-left: 1.25rem; padding-right: 1.25rem; }
            .grid-2 { grid-template-columns: 1fr; }
            
            /* Navbar Collapses into Mobile Menu */
            .hamburger { display: flex; }
            .nav-links {
                display: none;
                position: absolute;
                top: calc(100% + 10px);
                left: 0;
                right: 0;
                background: rgba(255, 255, 255, 0.97);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 0.95);
                border-radius: 20px;
                box-shadow: 0 16px 40px rgba(31, 38, 135, 0.15);
                padding: 1.25rem;
                flex-direction: column;
                align-items: stretch;
                gap: 0.5rem;
                z-index: 100;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }
            .nav-links.active {
                display: flex;
                animation: slideDownMenu 0.25s ease-out forwards;
            }
            .nav-item {
                width: 100%;
                text-align: left;
                padding: 0.75rem 1rem;
                font-size: 0.95rem;
                border-radius: 12px;
            }
            .nav-user-email {
                max-width: 100% !important;
            }
            .nav-links .btn-primary, .nav-links .btn-secondary, .nav-links form, .nav-links form button {
                width: 100% !important;
                margin: 0.25rem 0 !important;
                text-align: center;
                justify-content: center;
            }
            
            /* Editor Layout Stack */
            .editor-layout { flex-direction: column !important; height: auto !important; }
            .editor-layout > div { flex: none !important; width: 100% !important; }
            .editor-sidebar { border-right: none !important; border-bottom: 1px solid var(--surface-border); padding-right: 0 !important; padding-bottom: 1.5rem; margin-bottom: 1.5rem; overflow-y: visible !important; }
            .editor-preview { overflow-y: visible !important; }
        }

        @keyframes slideDownMenu {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Small Screens & Mobile (<= 768px) */
        @media (max-width: 768px) {
            body { padding: 0.75rem 0 2rem 0; }
            .app-layout { padding-left: 0.85rem; padding-right: 0.85rem; }
            .container { gap: 1.25rem; }
            .navbar { padding: 0.75rem 1rem; border-radius: 16px; margin-bottom: 1.25rem; }
            .logo { font-size: 1.25rem; }
            
            .glass-card, .card {
                padding: 1.25rem 1rem !important;
                border-radius: 18px !important;
            }
            
            /* Force inline 2-col grids to stack on mobile */
            .grid-2,
            [style*="grid-template-columns: 1fr 1fr"],
            [style*="grid-template-columns: 1.3fr 1fr"] {
                grid-template-columns: 1fr !important;
                gap: 1rem !important;
            }

            /* Responsive item cards */
            .history-item, .lead-item, .integration-card, .list-group-item {
                flex-direction: column;
                align-items: stretch;
                gap: 1rem;
                padding: 1rem !important;
            }

            /* Touch targets & action toolbars */
            .action-bar {
                flex-direction: column;
                align-items: stretch;
                width: 100%;
            }
            .action-bar > * {
                width: 100% !important;
            }

            /* Inquiries filter form elements stretch */
            form[action*="inquiries"] > div {
                flex: 1 1 100% !important;
            }
        }

        /* Small Phones (<= 480px) */
        @media (max-width: 480px) {
            body { padding: 0.5rem 0 1.5rem 0; }
            .app-layout { padding-left: 0.5rem; padding-right: 0.5rem; }
            .navbar { padding: 0.65rem 0.85rem; border-radius: 14px; margin-bottom: 1rem; }
            .glass-card, .card {
                padding: 1rem 0.85rem !important;
                border-radius: 14px !important;
            }
            h1 { font-size: 1.45rem !important; }
            h2 { font-size: 1.25rem !important; }
            h3 { font-size: 1.1rem !important; }
            
            /* Compact badges */
            .badge { font-size: 0.72rem; padding: 0.25rem 0.55rem; }
            
            /* Stack flex headers */
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            /* Reset large minmax grids that could cause horizontal scroll */
            [style*="grid-template-columns: repeat(auto-fit, minmax(340px, 1fr))"],
            [style*="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr))"],
            [style*="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr))"],
            [style*="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr))"] {
                grid-template-columns: 1fr !important;
            }

            /* Ensure full-width buttons inside card actions */
            .card-actions, .mobile-full-width {
                display: flex;
                flex-direction: column;
                width: 100%;
                gap: 0.5rem;
            }
            .card-actions > *, .mobile-full-width > * {
                width: 100% !important;
            }
        }
    </style>
</head>
<body>
    <div class="blob-1"></div>
    <div class="blob-2"></div>

    <div class="app-layout">
        <header class="navbar">
            <a href="/" class="logo">✨ {{ config('app.name', 'AI Looping') }}</a>
            <button class="hamburger" id="nav-toggle" aria-label="Toggle Navigation">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <nav class="nav-links" id="nav-links">
                @auth
                    @if(auth()->user()->role === 'company')
                        <a href="{{ route('company.dashboard') }}" class="nav-item">🏢 Dashboard</a>
                        <a href="{{ route('company.jobs.index') }}" class="nav-item">💼 Job Postings</a>
                        <a href="{{ route('company.inquiries.index') }}" class="nav-item">💬 Inquiries & Queries</a>
                        <a href="{{ route('company.integrations') }}" class="nav-item">🔗 Integrations</a>
                        <a href="{{ route('contact') }}" class="nav-item">💬 Contact</a>
                        <a href="{{ route('company.jobs.create') }}" class="btn-primary" style="padding: 0.4rem 1rem; font-size: 0.85rem; margin-right: 0.5rem;">➕ Post a Job</a>
                    @else
                        <a href="{{ route('resumes.tailor') }}" class="nav-item">Resume Builder</a>
                        <a href="{{ route('templates.index') }}" class="nav-item">Template Gallery</a>
                        <a href="{{ route('resume_editor.index') }}" class="nav-item">Dynamic Editor</a>
                        <a href="{{ route('role_resume.index') }}" class="nav-item">Role Resume</a>
                        <a href="{{ route('social_posts.index') }}" class="nav-item">Social Campaign</a>
                        <a href="{{ route('interview_prep.index') }}" class="nav-item" style="font-weight: 600; color: #4338ca;">🎯 Interview Prep</a>
                        <a href="{{ route('candidate.headhunter.index') }}" class="nav-item" style="font-weight: 600; color: #0284c7;">🎯 Scouted Jobs</a>
                        <a href="{{ route('integrations.index') }}" class="nav-item">Integrations</a>
                        <a href="{{ route('contact') }}" class="nav-item">💬 Contact</a>
                        <a href="{{ route('app.download.page') }}" class="nav-item" style="font-weight: 600; color: #4f46e5;">📱 Mobile App</a>
                    @endif
                    <span class="nav-item nav-user-email" title="{{ auth()->user()->email }}">{{ auth()->user()->email }}</span>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn-primary" style="margin-left: 0.5rem; padding: 0.45rem 1rem; font-size: 0.88rem; min-height: 38px;">Sign Out</button>
                    </form>
                @else
                    <a href="{{ route('contact') }}" class="nav-item">💬 Contact Us</a>
                    <a href="{{ route('app.download.page') }}" class="nav-item" style="color: #4f46e5; font-weight: 600;">📱 Mobile App</a>
                    <a href="{{ route('login') }}" class="nav-item">Log In</a>
                    <a href="{{ route('register') }}" class="btn-primary">Register</a>
                @endauth
            </nav>
        </header>

        <main class="container">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif
            
            @yield('content')
        </main>

        <!-- Production-Ready Enterprise Footer -->
        <footer style="margin-top: 4rem; background: rgba(255, 255, 255, 0.82); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.9); border-radius: 24px; box-shadow: 0 12px 35px rgba(31, 38, 135, 0.05); padding: 3rem 2.2rem 1.8rem 2.2rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 2.5rem; margin-bottom: 2.5rem;">
                
                <!-- Col 1: Brand & Status -->
                <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-size: 1.5rem;">🔄</span>
                        <span style="font-size: 1.25rem; font-weight: 800; color: #1e1b4b; letter-spacing: -0.3px;">AutoApply &amp; AI Looping</span>
                    </div>
                    <p style="font-size: 0.88rem; color: #475569; line-height: 1.55; margin: 0;">
                        Next-generation autonomous recruitment intelligence platform connecting top tech talent and high-growth employers with AI-driven precision.
                    </p>
                    <div style="display: inline-flex; align-items: center; gap: 0.45rem; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; border-radius: 20px; padding: 0.3rem 0.75rem; font-size: 0.78rem; font-weight: 600; width: fit-content;">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                        All Production Systems Operational
                    </div>
                    <div style="font-size: 0.8rem; color: #64748b;">
                        Powered by Google Gemini 2.5 Flash &amp; Groq Llama 3
                    </div>
                </div>

                <!-- Col 2: Candidate Suite -->
                <div>
                    <h4 style="font-size: 0.92rem; font-weight: 700; color: #1e1b4b; text-transform: uppercase; letter-spacing: 0.8px; margin: 0 0 1rem 0;">
                        Candidate Solutions
                    </h4>
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.55rem; font-size: 0.88rem;">
                        <li><a href="{{ route('resumes.tailor') }}" style="color: #475569; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#4f46e5'" onmouseout="this.style.color='#475569'">Dynamic Resume Builder</a></li>
                        <li><a href="{{ route('templates.index') }}" style="color: #475569; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#4f46e5'" onmouseout="this.style.color='#475569'">15 ATS Resume Templates</a></li>
                        <li><a href="{{ route('resume_editor.index') }}" style="color: #475569; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#4f46e5'" onmouseout="this.style.color='#475569'">Dynamic Visual Editor</a></li>
                        <li><a href="{{ route('interview_prep.index') }}" style="color: #475569; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#4f46e5'" onmouseout="this.style.color='#475569'">🎯 AI Interview Prep Studio</a></li>
                        <li><a href="{{ route('candidate.headhunter.index') }}" style="color: #475569; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#4f46e5'" onmouseout="this.style.color='#475569'">🎯 Scouted Jobs Portal</a></li>
                        <li><a href="{{ route('social_posts.index') }}" style="color: #475569; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#4f46e5'" onmouseout="this.style.color='#475569'">Social Leadership Campaigns</a></li>
                    </ul>
                </div>

                <!-- Col 3: Company Suite -->
                <div>
                    <h4 style="font-size: 0.92rem; font-weight: 700; color: #1e1b4b; text-transform: uppercase; letter-spacing: 0.8px; margin: 0 0 1rem 0;">
                        Employer Solutions
                    </h4>
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.55rem; font-size: 0.88rem;">
                        <li><a href="{{ route('company.jobs.create') }}" style="color: #475569; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#4f46e5'" onmouseout="this.style.color='#475569'">1-Click Job Syndication</a></li>
                        <li><a href="{{ route('company.jobs.index') }}" style="color: #475569; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#4f46e5'" onmouseout="this.style.color='#475569'">Multi-Platform Postings</a></li>
                        <li><a href="{{ route('company.inquiries.index') }}" style="color: #475569; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#4f46e5'" onmouseout="this.style.color='#475569'">Candidate Inquiries Hub</a></li>
                        <li><a href="{{ route('company.integrations') }}" style="color: #475569; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#4f46e5'" onmouseout="this.style.color='#475569'">ATS Integrations &amp; Feeds</a></li>
                        <li><a href="{{ route('company.feed.indeed') }}" target="_blank" style="color: #475569; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#4f46e5'" onmouseout="this.style.color='#475569'">Indeed ATS XML Feed</a></li>
                    </ul>
                </div>

                <!-- Col 4: Legal & Contact -->
                <div>
                    <h4 style="font-size: 0.92rem; font-weight: 700; color: #1e1b4b; text-transform: uppercase; letter-spacing: 0.8px; margin: 0 0 1rem 0;">
                        Support &amp; Legal
                    </h4>
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.55rem; font-size: 0.88rem;">
                        <li><a href="{{ route('contact') }}" style="color: #4f46e5; font-weight: 600; text-decoration: none; transition: color 0.2s;">💬 Contact Support &amp; Sales</a></li>
                        <li><a href="{{ route('terms') }}" style="color: #475569; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#4f46e5'" onmouseout="this.style.color='#475569'">⚖️ Terms of Service</a></li>
                        <li><a href="{{ route('privacy') }}" style="color: #475569; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#4f46e5'" onmouseout="this.style.color='#475569'">🔒 Privacy Policy (GDPR / CCPA)</a></li>
                        <li><a href="{{ route('cookies') }}" style="color: #475569; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#4f46e5'" onmouseout="this.style.color='#475569'">🍪 Cookie Policy</a></li>
                        <li><a href="{{ route('app.download.page') }}" style="color: #475569; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#4f46e5'" onmouseout="this.style.color='#475569'">📱 Download Mobile App (APK)</a></li>
                    </ul>
                </div>

            </div>

            <!-- Footer Bottom Line -->
            <div style="border-top: 1px solid #e2e8f0; padding-top: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; font-size: 0.82rem; color: #64748b;">
                <div>
                    &copy; {{ date('Y') }} <strong>AutoApply &amp; AI Looping Systems Inc.</strong> All rights reserved.
                </div>
                <div style="display: flex; gap: 1.25rem; align-items: center; flex-wrap: wrap;">
                    <a href="{{ route('terms') }}" style="color: #64748b; text-decoration: none;">Terms</a>
                    <a href="{{ route('privacy') }}" style="color: #64748b; text-decoration: none;">Privacy</a>
                    <a href="{{ route('cookies') }}" style="color: #64748b; text-decoration: none;">Cookies</a>
                    <a href="{{ route('contact') }}" style="color: #64748b; text-decoration: none;">Contact</a>
                    <span style="color: #cbd5e1;">|</span>
                    <span style="color: #059669; font-weight: 600;">🔒 TLS 1.3 Production Secure</span>
                </div>
            </div>
        </footer>
    </div>

    <script>
        const navToggle = document.getElementById('nav-toggle');
        const navLinks = document.getElementById('nav-links');

        if (navToggle && navLinks) {
            navToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                navToggle.classList.toggle('active');
                navLinks.classList.toggle('active');
            });

            // Close menu when clicking outside
            document.addEventListener('click', function(e) {
                if (!navLinks.contains(e.target) && !navToggle.contains(e.target)) {
                    navToggle.classList.remove('active');
                    navLinks.classList.remove('active');
                }
            });

            // Close menu when any navigation link is clicked
            navLinks.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    navToggle.classList.remove('active');
                    navLinks.classList.remove('active');
                });
            });
        }
    </script>
</body>
</html>
