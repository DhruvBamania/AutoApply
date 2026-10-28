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

        /* ==========================================================================
           MODERN RESPONSIVE FLOATING HEADER & NAVIGATION
           ========================================================================== */
        
        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.25rem;
            padding: 0.6rem 1.25rem;
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 18px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px -10px rgba(79, 70, 229, 0.08), 0 4px 16px -2px rgba(15, 23, 42, 0.04);
            position: sticky;
            top: 0.75rem;
            z-index: 1000;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Brand Logo */
        .header-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            text-decoration: none;
            color: #1e1b4b;
            flex-shrink: 0;
            transition: transform 0.2s ease;
            margin-right: 0.5rem;
        }
        .header-brand:hover {
            transform: translateY(-1px);
        }
        .brand-icon-box {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #ec4899 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
            flex-shrink: 0;
        }
        .brand-icon-box.sm {
            width: 30px;
            height: 30px;
            border-radius: 8px;
        }
        .brand-icon-svg {
            width: 20px;
            height: 20px;
            color: #ffffff;
        }
        .brand-text-group {
            display: flex;
            flex-direction: column;
            line-height: 1.15;
        }
        .brand-title {
            font-size: 1.22rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #0f172a;
        }
        .brand-title-accent {
            background: linear-gradient(135deg, #4f46e5, #9333ea);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .brand-subtitle {
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: #64748b;
            text-transform: uppercase;
        }

        /* Center Nav Links (Desktop) */
        .header-nav-center {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            flex: 1;
            min-width: 0;
        }
        .header-nav-link {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.42rem 0.7rem;
            color: #475569;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            border-radius: 10px;
            transition: all 0.18s ease;
            white-space: nowrap;
            background: transparent;
            border: 1px solid transparent;
            cursor: pointer;
            line-height: 1.2;
            box-sizing: border-box;
            height: 38px;
        }
        .header-nav-link:hover {
            color: #4f46e5;
            background: rgba(99, 102, 241, 0.06);
            transform: translateY(-1px);
        }
        .header-nav-link.active {
            color: #4338ca;
            background: rgba(99, 102, 241, 0.1);
            font-weight: 600;
            border-color: rgba(99, 102, 241, 0.18);
        }
        .nav-link-icon {
            font-size: 0.95rem;
            line-height: 1;
            display: inline-flex;
            align-items: center;
        }
        .pill-sparkle {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            font-size: 0.6rem;
            font-weight: 700;
            padding: 0.12rem 0.4rem;
            border-radius: 6px;
            letter-spacing: 0.04em;
            line-height: 1;
            margin-left: 0.1rem;
        }

        /* Dropdown Trigger & Menus */
        .header-dropdown {
            position: relative;
            display: inline-block;
        }
        .dropdown-chevron {
            width: 14px;
            height: 14px;
            transition: transform 0.2s ease;
            color: #64748b;
        }
        .header-dropdown.open .dropdown-chevron {
            transform: rotate(180deg);
        }
        .header-dropdown-menu {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            min-width: 250px;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 16px;
            box-shadow: 0 20px 40px -12px rgba(15, 23, 42, 0.15);
            padding: 0.5rem;
            z-index: 1050;
            animation: dropdownFadeIn 0.2s ease-out;
        }
        .header-dropdown.open .header-dropdown-menu {
            display: block;
        }
        @keyframes dropdownFadeIn {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .dropdown-menu-label {
            font-size: 0.65rem;
            font-weight: 700;
            color: #94a3b8;
            letter-spacing: 0.08em;
            padding: 0.4rem 0.75rem 0.2rem 0.75rem;
            text-transform: uppercase;
        }
        .dropdown-menu-item {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.55rem 0.75rem;
            border-radius: 10px;
            text-decoration: none;
            color: #334155;
            transition: all 0.18s ease;
        }
        .dropdown-menu-item:hover {
            background: #f1f5f9;
            color: #4f46e5;
            transform: translateX(2px);
        }
        .dropdown-menu-item.active {
            background: rgba(99, 102, 241, 0.08);
            color: #4338ca;
            font-weight: 600;
        }
        .item-icon-box {
            font-size: 1.05rem;
            width: 26px;
            height: 26px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: #f8fafc;
            flex-shrink: 0;
        }
        .item-text-group {
            display: flex;
            flex-direction: column;
            line-height: 1.25;
        }
        .item-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: #1e293b;
        }
        .item-desc {
            font-size: 0.72rem;
            color: #64748b;
        }
        .dropdown-menu-divider {
            height: 1px;
            background: #e2e8f0;
            margin: 0.4rem 0;
        }

        /* Right Actions */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            flex-shrink: 0;
            margin-left: 0.5rem;
        }
        .header-cta-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.42rem 0.9rem;
            border-radius: 10px;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            height: 38px;
            box-sizing: border-box;
            flex-shrink: 0;
        }
        .header-cta-btn.primary-cta {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3);
            border: none;
        }
        .header-cta-btn.primary-cta:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(79, 70, 229, 0.45);
            color: white;
        }
        .header-cta-btn.secondary-cta {
            background: rgba(99, 102, 241, 0.06);
            color: #4f46e5;
            border: 1px solid rgba(99, 102, 241, 0.22);
        }
        .header-cta-btn.secondary-cta:hover {
            background: rgba(99, 102, 241, 0.12);
            transform: translateY(-1px);
            color: #3730a3;
            border-color: rgba(99, 102, 241, 0.35);
        }

        /* User Profile Button & Dropdown */
        .user-profile-trigger {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem 0.25rem 0.35rem;
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 9999px;
            cursor: pointer;
            transition: all 0.2s ease;
            height: 38px;
            box-sizing: border-box;
        }
        .user-profile-trigger:hover {
            background: white;
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        }
        .user-avatar-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            color: white;
            font-size: 0.85rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(99, 102, 241, 0.25);
            flex-shrink: 0;
        }
        .user-info-text {
            display: flex;
            flex-direction: column;
            text-align: left;
            line-height: 1.15;
            max-width: 120px;
        }
        .user-name-line {
            font-size: 0.8rem;
            font-weight: 600;
            color: #1e293b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .user-role-badge {
            font-size: 0.58rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .badge-role-company {
            color: #0284c7;
        }
        .badge-role-candidate {
            color: #7c3aed;
        }

        /* User Menu Dropdown Card */
        .user-dropdown-card {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 290px;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 20px;
            box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.18), 0 0 0 1px rgba(0, 0, 0, 0.03);
            padding: 0.75rem;
            z-index: 1050;
            animation: dropdownFadeIn 0.2s ease-out;
        }
        .header-dropdown.open .user-dropdown-card {
            display: block;
        }
        .dropdown-user-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0.4rem;
        }
        .dropdown-avatar-large {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4f46e5 0%, #9333ea 100%);
            color: white;
            font-size: 1.15rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
            flex-shrink: 0;
        }
        .dropdown-user-meta {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            line-height: 1.25;
        }
        .user-display-name {
            font-size: 0.9rem;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .user-display-email {
            font-size: 0.76rem;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 0.2rem;
        }
        .user-display-role {
            font-size: 0.68rem;
            font-weight: 700;
            padding: 0.15rem 0.5rem;
            border-radius: 6px;
            display: inline-block;
            width: fit-content;
        }
        .user-display-role.company {
            background: #e0f2fe;
            color: #0369a1;
        }
        .user-display-role.candidate {
            background: #f3e8ff;
            color: #7e22ce;
        }
        .dropdown-menu-links {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }
        .dropdown-signout-form {
            margin: 0;
            padding: 0.25rem 0 0 0;
        }
        .dropdown-signout-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.6rem;
            background: #fff1f2;
            color: #e11d48;
            border: 1px solid #ffe4e6;
            border-radius: 12px;
            font-size: 0.84rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .dropdown-signout-btn:hover {
            background: #ffe4e6;
            color: #be123c;
            transform: translateY(-1px);
        }

        /* Modern Animated Hamburger Button */
        .hamburger-btn {
            display: none;
            width: 40px;
            height: 40px;
            padding: 8px;
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 10px;
            cursor: pointer;
            flex-direction: column;
            justify-content: space-around;
            align-items: center;
            transition: all 0.2s ease;
            z-index: 1060;
        }
        .hamburger-btn:hover {
            background: white;
            border-color: #cbd5e1;
        }
        .hamburger-btn .bar {
            width: 22px;
            height: 2.2px;
            background: #334155;
            border-radius: 3px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .hamburger-btn.active .bar-1 {
            transform: translateY(7px) rotate(45deg);
            background: #4f46e5;
        }
        .hamburger-btn.active .bar-2 {
            opacity: 0;
            transform: scale(0.3);
        }
        .hamburger-btn.active .bar-3 {
            transform: translateY(-7px) rotate(-45deg);
            background: #4f46e5;
        }

        /* Mobile Slide Drawer & Overlay */
        .mobile-drawer-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 1090;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .mobile-drawer-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }
        .mobile-nav-drawer {
            position: fixed;
            top: 0;
            right: -340px;
            width: 320px;
            max-width: 88vw;
            height: 100vh;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            box-shadow: -10px 0 35px rgba(0, 0, 0, 0.15);
            z-index: 1100;
            display: flex;
            flex-direction: column;
            transition: right 0.32s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            padding: 1.25rem;
            box-sizing: border-box;
        }
        .mobile-nav-drawer.active {
            right: 0;
        }
        .drawer-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .drawer-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        .drawer-close-btn {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .drawer-close-btn:hover {
            background: #fee2e2;
            color: #ef4444;
            border-color: #fecaca;
        }
        .drawer-user-card {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(168, 85, 247, 0.08) 100%);
            border: 1px solid rgba(99, 102, 241, 0.15);
            border-radius: 14px;
            padding: 0.85rem;
            margin: 1rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .drawer-user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4f46e5 0%, #9333ea 100%);
            color: white;
            font-size: 1.1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .drawer-user-details {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            line-height: 1.25;
        }
        .drawer-user-name {
            font-size: 0.88rem;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .drawer-user-email {
            font-size: 0.74rem;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 0.2rem;
        }
        .drawer-user-role {
            font-size: 0.65rem;
            font-weight: 700;
            padding: 0.12rem 0.45rem;
            border-radius: 6px;
            width: fit-content;
        }
        .drawer-user-role.company { background: #e0f2fe; color: #0284c7; }
        .drawer-user-role.candidate { background: #f3e8ff; color: #7e22ce; }
        .drawer-body {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            flex: 1;
        }
        .drawer-section-title {
            font-size: 0.66rem;
            font-weight: 700;
            color: #94a3b8;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 0.75rem 0.5rem 0.25rem 0.5rem;
        }
        .drawer-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.65rem 0.85rem;
            border-radius: 12px;
            text-decoration: none;
            color: #334155;
            font-size: 0.88rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .drawer-link:hover {
            background: #f1f5f9;
            color: #4f46e5;
            transform: translateX(3px);
        }
        .drawer-link.active {
            background: rgba(99, 102, 241, 0.1);
            color: #4338ca;
            font-weight: 600;
        }
        .drawer-link-icon {
            font-size: 1rem;
            margin-right: 0.65rem;
            display: inline-block;
        }
        .drawer-badge {
            font-size: 0.62rem;
            font-weight: 700;
            background: #0284c7;
            color: white;
            padding: 0.1rem 0.4rem;
            border-radius: 6px;
        }
        .drawer-action-block {
            margin: 0.85rem 0;
        }
        .drawer-footer {
            margin-top: auto;
            padding-top: 1.5rem;
            border-top: 1px solid #f1f5f9;
        }
        .drawer-logout-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem;
            background: #fff1f2;
            color: #e11d48;
            border: 1px solid #ffe4e6;
            border-radius: 12px;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .drawer-logout-btn:hover {
            background: #ffe4e6;
            color: #be123c;
        }

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
        
        /* Laptops & Standard Desktops (<= 1260px) */
        @media (max-width: 1260px) {
            .app-layout { padding-left: 1.25rem; padding-right: 1.25rem; }
            .navbar { gap: 0.75rem; padding: 0.55rem 1rem; }
            .header-nav-link { padding: 0.38rem 0.55rem; font-size: 0.82rem; gap: 0.25rem; }
            .header-brand { margin-right: 0.25rem; }
            .header-actions { margin-left: 0.25rem; }
            .user-role-badge { display: none; } /* Hide role text on laptops to prevent horizontal crowding */
            .user-info-text { max-width: 90px; }
        }

        /* Compact Laptops, Tablets Landscape & Split Screen (<= 1120px) */
        @media (max-width: 1120px) {
            .header-nav-center { display: none; } /* Seamlessly collapse center links into mobile drawer */
            .hamburger-btn { display: flex; }
            .navbar { padding: 0.55rem 1rem; margin-bottom: 1.5rem; }
            .user-role-badge { display: inline-block; } /* Center collapsed, ample room restored */
            .user-info-text { max-width: 120px; }
        }

        /* Tablets & Mobile Navigation (<= 860px) */
        @media (max-width: 860px) {
            .grid-2 { grid-template-columns: 1fr; }
            .user-info-text { display: none; } /* Show avatar circle only */
            .user-profile-trigger { padding: 0.25rem 0.35rem; }
            .user-profile-trigger .dropdown-chevron { display: none; }
            
            /* Editor Layout Stack */
            .editor-layout { flex-direction: column !important; height: auto !important; }
            .editor-layout > div { flex: none !important; width: 100% !important; }
            .editor-sidebar { border-right: none !important; border-bottom: 1px solid var(--surface-border); padding-right: 0 !important; padding-bottom: 1.5rem; margin-bottom: 1.5rem; overflow-y: visible !important; }
            .editor-preview { overflow-y: visible !important; }
        }

        /* Small Screens & Mobile (<= 768px) */
        @media (max-width: 768px) {
            body { padding: 0.75rem 0 2rem 0; }
            .app-layout { padding-left: 0.85rem; padding-right: 0.85rem; }
            .container { gap: 1.25rem; }
            .navbar { padding: 0.5rem 0.85rem; border-radius: 14px; margin-bottom: 1.25rem; }
            .logo { font-size: 1.25rem; }
            .header-cta-btn { display: none; } /* Accessible inside mobile drawer */
            .user-profile-trigger { display: none; } /* Accessible inside mobile drawer header */
            .brand-subtitle { display: none; }
            
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
        <header class="navbar" id="mainHeader">
            <!-- Brand Logo -->
            <a href="/" class="header-brand" title="{{ config('app.name', 'AI Looping') }}">
                <div class="brand-icon-box">
                    <svg class="brand-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
                    </svg>
                </div>
                <div class="brand-text-group">
                    <span class="brand-title">Auto<span class="brand-title-accent">Apply</span></span>
                    <span class="brand-subtitle">AI LOOPING</span>
                </div>
            </a>

            <!-- Desktop Nav Center -->
            <nav class="header-nav-center" id="desktopNav">
                @auth
                    @if(auth()->user()->role === 'company')
                        <a href="{{ route('company.dashboard') }}" class="header-nav-link {{ request()->routeIs('company.dashboard') ? 'active' : '' }}">
                            <span class="nav-link-icon">🏢</span> Dashboard
                        </a>
                        <a href="{{ route('company.jobs.index') }}" class="header-nav-link {{ request()->routeIs('company.jobs.*') && !request()->routeIs('company.jobs.create') ? 'active' : '' }}">
                            <span class="nav-link-icon">💼</span> Jobs
                        </a>
                        <a href="{{ route('company.inquiries.index') }}" class="header-nav-link {{ request()->routeIs('company.inquiries.*') ? 'active' : '' }}">
                            <span class="nav-link-icon">💬</span> Inquiries
                        </a>
                        <a href="{{ route('company.integrations') }}" class="header-nav-link {{ request()->routeIs('company.integrations') ? 'active' : '' }}">
                            <span class="nav-link-icon">🔗</span> Integrations
                        </a>
                        <a href="{{ route('contact') }}" class="header-nav-link {{ request()->routeIs('contact*') ? 'active' : '' }}">
                            <span class="nav-link-icon">🎧</span> Support
                        </a>
                    @else
                        <a href="{{ route('jobs.index') }}" class="header-nav-link {{ request()->routeIs('jobs.*') ? 'active' : '' }}">
                            <span class="nav-link-icon">💼</span> Explore Jobs
                        </a>
                        <a href="{{ route('candidate.headhunter.index') }}" class="header-nav-link {{ request()->routeIs('candidate.headhunter.*') ? 'active' : '' }}">
                            <span class="nav-link-icon">🎯</span> Scouted Jobs
                            <span class="pill-sparkle">AI</span>
                        </a>
                        <a href="{{ route('resumes.tailor') }}" class="header-nav-link {{ request()->routeIs('resumes.*') ? 'active' : '' }}">
                            <span class="nav-link-icon">📄</span> Resume Studio
                        </a>
                        <a href="{{ route('templates.index') }}" class="header-nav-link {{ request()->routeIs('templates.*') ? 'active' : '' }}">
                            <span class="nav-link-icon">🎨</span> Templates
                        </a>
                        <a href="{{ route('interview_prep.index') }}" class="header-nav-link {{ request()->routeIs('interview_prep.*') ? 'active' : '' }}">
                            <span class="nav-link-icon">💡</span> Interview Prep
                        </a>

                        <!-- Desktop Tools Dropdown -->
                        <div class="header-dropdown" id="toolsDropdown">
                            <button type="button" class="header-nav-link {{ request()->routeIs('resume_editor.*', 'role_resume.*', 'social_posts.*', 'integrations.*') ? 'active' : '' }}" id="toolsDropdownBtn" aria-haspopup="true" aria-expanded="false">
                                <span>Tools</span>
                                <svg class="dropdown-chevron" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                            <div class="header-dropdown-menu" id="toolsDropdownMenu">
                                <div class="dropdown-menu-label">AI CAREER TOOLS</div>
                                <a href="{{ route('resume_editor.index') }}" class="dropdown-menu-item {{ request()->routeIs('resume_editor.*') ? 'active' : '' }}">
                                    <span class="item-icon-box">⚡</span>
                                    <div class="item-text-group">
                                        <span class="item-title">Dynamic Editor</span>
                                        <span class="item-desc">Real-time AI resume tailoring</span>
                                    </div>
                                </a>
                                <a href="{{ route('role_resume.index') }}" class="dropdown-menu-item {{ request()->routeIs('role_resume.*') ? 'active' : '' }}">
                                    <span class="item-icon-box">🎯</span>
                                    <div class="item-text-group">
                                        <span class="item-title">Role-Tailored Resumes</span>
                                        <span class="item-desc">Targeted keywords for ATS</span>
                                    </div>
                                </a>
                                <a href="{{ route('social_posts.index') }}" class="dropdown-menu-item {{ request()->routeIs('social_posts.*') ? 'active' : '' }}">
                                    <span class="item-icon-box">📣</span>
                                    <div class="item-text-group">
                                        <span class="item-title">Social Campaign</span>
                                        <span class="item-desc">LinkedIn & viral posts</span>
                                    </div>
                                </a>
                                <div class="dropdown-menu-divider"></div>
                                <a href="{{ route('integrations.index') }}" class="dropdown-menu-item {{ request()->routeIs('integrations.*') ? 'active' : '' }}">
                                    <span class="item-icon-box">🔗</span>
                                    <div class="item-text-group">
                                        <span class="item-title">Account Integrations</span>
                                        <span class="item-desc">LinkedIn & API keys</span>
                                    </div>
                                </a>
                                <a href="{{ route('contact') }}" class="dropdown-menu-item {{ request()->routeIs('contact*') ? 'active' : '' }}">
                                    <span class="item-icon-box">🎧</span>
                                    <div class="item-text-group">
                                        <span class="item-title">Help & Support</span>
                                        <span class="item-desc">24/7 dedicated assistance</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    @endif
                @else
                    <a href="{{ route('jobs.index') }}" class="header-nav-link {{ request()->routeIs('jobs.*') ? 'active' : '' }}">
                        <span class="nav-link-icon">💼</span> Browse Jobs
                    </a>
                    <a href="{{ route('contact') }}" class="header-nav-link {{ request()->routeIs('contact*') ? 'active' : '' }}">
                        <span class="nav-link-icon">💬</span> Contact Us
                    </a>
                    <a href="{{ route('app.download.page') }}" class="header-nav-link {{ request()->routeIs('app.download.*') ? 'active' : '' }}">
                        <span class="nav-link-icon">📱</span> Mobile App
                    </a>
                @endauth
            </nav>

            <!-- Right: Actions & User Dropdown -->
            <div class="header-actions">
                @auth
                    @if(auth()->user()->role === 'company')
                        <a href="{{ route('company.jobs.create') }}" class="header-cta-btn primary-cta">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>Post Job</span>
                        </a>
                    @endif

                    <!-- User Profile Dropdown Button -->
                    <div class="header-dropdown" id="userMenuWrapper">
                        <button type="button" class="user-profile-trigger" id="userMenuBtn" aria-expanded="false" aria-haspopup="true">
                            <div class="user-avatar-circle">
                                {{ strtoupper(substr(auth()->user()->name ?? auth()->user()->email, 0, 1)) }}
                            </div>
                            <div class="user-info-text">
                                <span class="user-name-line">{{ auth()->user()->name ?? explode('@', auth()->user()->email)[0] }}</span>
                                <span class="user-role-badge {{ auth()->user()->role === 'company' ? 'badge-role-company' : 'badge-role-candidate' }}">
                                    {{ auth()->user()->role === 'company' ? 'Company' : 'Candidate' }}
                                </span>
                            </div>
                            <svg class="dropdown-chevron" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <!-- User Profile Dropdown Card -->
                        <div class="user-dropdown-card" id="userMenuDropdown">
                            <div class="dropdown-user-header">
                                <div class="dropdown-avatar-large">
                                    {{ strtoupper(substr(auth()->user()->name ?? auth()->user()->email, 0, 1)) }}
                                </div>
                                <div class="dropdown-user-meta">
                                    <div class="user-display-name">{{ auth()->user()->name ?? 'Account User' }}</div>
                                    <div class="user-display-email" title="{{ auth()->user()->email }}">{{ auth()->user()->email }}</div>
                                    <span class="user-display-role {{ auth()->user()->role === 'company' ? 'company' : 'candidate' }}">
                                        {{ auth()->user()->role === 'company' ? '🏢 Employer & Recruiter' : '🎯 Candidate Pro' }}
                                    </span>
                                </div>
                            </div>

                            <div class="dropdown-menu-divider"></div>

                            <div class="dropdown-menu-links">
                                @if(auth()->user()->role === 'company')
                                    <a href="{{ route('company.dashboard') }}" class="dropdown-menu-item">
                                        <span class="item-icon-box">🏢</span>
                                        <div class="item-text-group">
                                            <span class="item-title">Company Dashboard</span>
                                            <span class="item-desc">Metrics & scouted talents</span>
                                        </div>
                                    </a>
                                    <a href="{{ route('company.jobs.index') }}" class="dropdown-menu-item">
                                        <span class="item-icon-box">💼</span>
                                        <div class="item-text-group">
                                            <span class="item-title">Job Postings Hub</span>
                                            <span class="item-desc">Active, draft & closed roles</span>
                                        </div>
                                    </a>
                                    <a href="{{ route('company.inquiries.index') }}" class="dropdown-menu-item">
                                        <span class="item-icon-box">💬</span>
                                        <div class="item-text-group">
                                            <span class="item-title">Inquiries & Comments</span>
                                            <span class="item-desc">Lead responses & pinned questions</span>
                                        </div>
                                    </a>
                                    <a href="{{ route('company.integrations') }}" class="dropdown-menu-item">
                                        <span class="item-icon-box">🔗</span>
                                        <div class="item-text-group">
                                            <span class="item-title">Platform Integrations</span>
                                            <span class="item-desc">LinkedIn, Indeed & APIs</span>
                                        </div>
                                    </a>
                                @else
                                    <a href="{{ route('jobs.index') }}" class="dropdown-menu-item">
                                        <span class="item-icon-box">💼</span>
                                        <div class="item-text-group">
                                            <span class="item-title">Explore Company Jobs</span>
                                            <span class="item-desc">Browse active openings & WhatsApp chat</span>
                                        </div>
                                    </a>
                                    <a href="{{ route('candidate.headhunter.index') }}" class="dropdown-menu-item">
                                        <span class="item-icon-box">🎯</span>
                                        <div class="item-text-group">
                                            <span class="item-title">Scouted Invitations</span>
                                            <span class="item-desc">Direct invitations from recruiters</span>
                                        </div>
                                    </a>
                                    <a href="{{ route('resumes.tailor') }}" class="dropdown-menu-item">
                                        <span class="item-icon-box">📄</span>
                                        <div class="item-text-group">
                                            <span class="item-title">Resume Studio</span>
                                            <span class="item-desc">Tailor resumes to job descriptions</span>
                                        </div>
                                    </a>
                                    <a href="{{ route('templates.index') }}" class="dropdown-menu-item">
                                        <span class="item-icon-box">🎨</span>
                                        <div class="item-text-group">
                                            <span class="item-title">Template Gallery</span>
                                            <span class="item-desc">ATS-friendly curated layouts</span>
                                        </div>
                                    </a>
                                    <a href="{{ route('interview_prep.index') }}" class="dropdown-menu-item">
                                        <span class="item-icon-box">💡</span>
                                        <div class="item-text-group">
                                            <span class="item-title">Interview Simulator</span>
                                            <span class="item-desc">AI behavioral & tech practice</span>
                                        </div>
                                    </a>
                                @endif

                                <a href="{{ route('contact') }}" class="dropdown-menu-item">
                                    <span class="item-icon-box">🎧</span>
                                    <div class="item-text-group">
                                        <span class="item-title">Support & Contact</span>
                                        <span class="item-desc">Submit tickets & feedback</span>
                                    </div>
                                </a>
                                <a href="{{ route('app.download.page') }}" class="dropdown-menu-item">
                                    <span class="item-icon-box">📱</span>
                                    <div class="item-text-group">
                                        <span class="item-title">Download Mobile App</span>
                                        <span class="item-desc">Android Release (v2.4)</span>
                                    </div>
                                </a>
                            </div>

                            <div class="dropdown-menu-divider"></div>

                            <form action="{{ route('logout') }}" method="POST" class="dropdown-signout-form">
                                @csrf
                                <button type="submit" class="dropdown-signout-btn">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                        <polyline points="16 17 21 12 16 7"></polyline>
                                        <line x1="21" y1="12" x2="9" y2="12"></line>
                                    </svg>
                                    <span>Sign Out</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="header-nav-link {{ request()->routeIs('login') ? 'active' : '' }}">Log In</a>
                    <a href="{{ route('register') }}" class="header-cta-btn primary-cta">Get Started</a>
                @endauth

                <!-- Responsive Hamburger Toggle -->
                <button type="button" class="hamburger-btn" id="nav-toggle" aria-label="Toggle Navigation Menu" aria-expanded="false">
                    <span class="bar bar-1"></span>
                    <span class="bar bar-2"></span>
                    <span class="bar bar-3"></span>
                </button>
            </div>
        </header>

        <!-- Mobile Slide Drawer & Overlay -->
        <div class="mobile-drawer-overlay" id="mobileDrawerOverlay"></div>
        <aside class="mobile-nav-drawer" id="mobileDrawer" aria-hidden="true">
            <div class="drawer-header">
                <a href="/" class="drawer-brand">
                    <div class="brand-icon-box sm">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="white" stroke-width="2.5">
                            <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
                        </svg>
                    </div>
                    <span class="brand-title">Auto<span class="brand-title-accent">Apply</span></span>
                </a>
                <button type="button" class="drawer-close-btn" id="drawerCloseBtn" aria-label="Close Navigation">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            @auth
                <!-- Mobile User Profile Card -->
                <div class="drawer-user-card">
                    <div class="drawer-user-avatar">
                        {{ strtoupper(substr(auth()->user()->name ?? auth()->user()->email, 0, 1)) }}
                    </div>
                    <div class="drawer-user-details">
                        <div class="drawer-user-name">{{ auth()->user()->name ?? 'Account User' }}</div>
                        <div class="drawer-user-email">{{ auth()->user()->email }}</div>
                        <span class="drawer-user-role {{ auth()->user()->role === 'company' ? 'company' : 'candidate' }}">
                            {{ auth()->user()->role === 'company' ? '🏢 Employer / Company' : '🎯 Candidate Pro' }}
                        </span>
                    </div>
                </div>
            @endauth

            <div class="drawer-body">
                @auth
                    @if(auth()->user()->role === 'company')
                        <div class="drawer-section-title">COMPANY WORKSPACE</div>
                        <a href="{{ route('company.dashboard') }}" class="drawer-link {{ request()->routeIs('company.dashboard') ? 'active' : '' }}">
                            <span><span class="drawer-link-icon">🏢</span> Dashboard</span>
                        </a>
                        <a href="{{ route('company.jobs.index') }}" class="drawer-link {{ request()->routeIs('company.jobs.*') && !request()->routeIs('company.jobs.create') ? 'active' : '' }}">
                            <span><span class="drawer-link-icon">💼</span> Job Postings</span>
                        </a>
                        <a href="{{ route('company.inquiries.index') }}" class="drawer-link {{ request()->routeIs('company.inquiries.*') ? 'active' : '' }}">
                            <span><span class="drawer-link-icon">💬</span> Inquiries & Queries</span>
                        </a>
                        <a href="{{ route('company.integrations') }}" class="drawer-link {{ request()->routeIs('company.integrations') ? 'active' : '' }}">
                            <span><span class="drawer-link-icon">🔗</span> Integrations & APIs</span>
                        </a>

                        <div class="drawer-action-block">
                            <a href="{{ route('company.jobs.create') }}" class="header-cta-btn primary-cta" style="width: 100%; justify-content: center;">➕ Post New Job</a>
                        </div>
                    @else
                        <div class="drawer-section-title">CORE NAVIGATION</div>
                        <a href="{{ route('jobs.index') }}" class="drawer-link {{ request()->routeIs('jobs.*') ? 'active' : '' }}">
                            <span><span class="drawer-link-icon">💼</span> Explore Jobs</span>
                            <span class="drawer-badge" style="background: #e0e7ff; color: #4338ca;">LIVE</span>
                        </a>
                        <a href="{{ route('candidate.headhunter.index') }}" class="drawer-link highlight {{ request()->routeIs('candidate.headhunter.*') ? 'active' : '' }}">
                            <span><span class="drawer-link-icon">🎯</span> Scouted Jobs</span>
                            <span class="drawer-badge">AI MATCH</span>
                        </a>
                        <a href="{{ route('resumes.tailor') }}" class="drawer-link {{ request()->routeIs('resumes.*') ? 'active' : '' }}">
                            <span><span class="drawer-link-icon">📄</span> Resume Studio</span>
                        </a>
                        <a href="{{ route('templates.index') }}" class="drawer-link {{ request()->routeIs('templates.*') ? 'active' : '' }}">
                            <span><span class="drawer-link-icon">🎨</span> Template Gallery</span>
                        </a>
                        <a href="{{ route('interview_prep.index') }}" class="drawer-link {{ request()->routeIs('interview_prep.*') ? 'active' : '' }}">
                            <span><span class="drawer-link-icon">💡</span> Interview Simulator</span>
                        </a>

                        <div class="drawer-section-title">AI TOOLS & CAMPAIGNS</div>
                        <a href="{{ route('resume_editor.index') }}" class="drawer-link {{ request()->routeIs('resume_editor.*') ? 'active' : '' }}">
                            <span><span class="drawer-link-icon">⚡</span> Dynamic Resume Editor</span>
                        </a>
                        <a href="{{ route('role_resume.index') }}" class="drawer-link {{ request()->routeIs('role_resume.*') ? 'active' : '' }}">
                            <span><span class="drawer-link-icon">🎯</span> Role-Tailored Resumes</span>
                        </a>
                        <a href="{{ route('social_posts.index') }}" class="drawer-link {{ request()->routeIs('social_posts.*') ? 'active' : '' }}">
                            <span><span class="drawer-link-icon">📣</span> Social Campaign</span>
                        </a>
                        <a href="{{ route('integrations.index') }}" class="drawer-link {{ request()->routeIs('integrations.*') ? 'active' : '' }}">
                            <span><span class="drawer-link-icon">🔗</span> Integrations & APIs</span>
                        </a>
                    @endif

                    <div class="drawer-section-title">HELP & PLATFORM</div>
                    <a href="{{ route('contact') }}" class="drawer-link {{ request()->routeIs('contact*') ? 'active' : '' }}">
                        <span><span class="drawer-link-icon">🎧</span> Contact & Support</span>
                    </a>
                    <a href="{{ route('app.download.page') }}" class="drawer-link {{ request()->routeIs('app.download.*') ? 'active' : '' }}">
                        <span><span class="drawer-link-icon">📱</span> Download Android APK</span>
                    </a>

                    <div class="drawer-footer">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="drawer-logout-btn">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg>
                                <span>Sign Out</span>
                            </button>
                        </form>
                    </div>
                @else
                    <div class="drawer-section-title">EXPLORE & DISCOVER</div>
                    <a href="{{ route('jobs.index') }}" class="drawer-link {{ request()->routeIs('jobs.*') ? 'active' : '' }}">
                        <span><span class="drawer-link-icon">💼</span> Browse Job Openings</span>
                    </a>
                    <a href="{{ route('contact') }}" class="drawer-link {{ request()->routeIs('contact*') ? 'active' : '' }}">
                        <span><span class="drawer-link-icon">💬</span> Contact & Support</span>
                    </a>
                    <a href="{{ route('app.download.page') }}" class="drawer-link {{ request()->routeIs('app.download.*') ? 'active' : '' }}">
                        <span><span class="drawer-link-icon">📱</span> Download Mobile App</span>
                    </a>
                    <a href="{{ route('login') }}" class="drawer-link {{ request()->routeIs('login') ? 'active' : '' }}">
                        <span><span class="drawer-link-icon">🔐</span> Sign In</span>
                    </a>

                    <div class="drawer-action-block" style="margin-top: 1.5rem;">
                        <a href="{{ route('register') }}" class="header-cta-btn primary-cta" style="width: 100%; justify-content: center;">
                            🚀 Register Free
                        </a>
                    </div>
                @endauth
            </div>
        </aside>

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
        // Navigation & Interactive Dropdowns Controller
        document.addEventListener('DOMContentLoaded', function() {
            const navToggle = document.getElementById('nav-toggle');
            const mobileDrawer = document.getElementById('mobileDrawer');
            const drawerOverlay = document.getElementById('mobileDrawerOverlay');
            const drawerCloseBtn = document.getElementById('drawerCloseBtn');

            const userMenuWrapper = document.getElementById('userMenuWrapper');
            const userMenuBtn = document.getElementById('userMenuBtn');

            const toolsDropdown = document.getElementById('toolsDropdown');
            const toolsDropdownBtn = document.getElementById('toolsDropdownBtn');

            // Open Mobile Drawer
            function openDrawer() {
                if (mobileDrawer && drawerOverlay && navToggle) {
                    mobileDrawer.classList.add('active');
                    drawerOverlay.classList.add('active');
                    navToggle.classList.add('active');
                    navToggle.setAttribute('aria-expanded', 'true');
                    mobileDrawer.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                }
            }

            // Close Mobile Drawer
            function closeDrawer() {
                if (mobileDrawer && drawerOverlay && navToggle) {
                    mobileDrawer.classList.remove('active');
                    drawerOverlay.classList.remove('active');
                    navToggle.classList.remove('active');
                    navToggle.setAttribute('aria-expanded', 'false');
                    mobileDrawer.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                }
            }

            if (navToggle) {
                navToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (mobileDrawer && mobileDrawer.classList.contains('active')) {
                        closeDrawer();
                    } else {
                        openDrawer();
                    }
                });
            }

            if (drawerCloseBtn) {
                drawerCloseBtn.addEventListener('click', closeDrawer);
            }

            if (drawerOverlay) {
                drawerOverlay.addEventListener('click', closeDrawer);
            }

            // Close mobile drawer on any link click
            if (mobileDrawer) {
                mobileDrawer.querySelectorAll('a').forEach(function(link) {
                    link.addEventListener('click', closeDrawer);
                });
            }

            // User Profile Dropdown Toggle
            if (userMenuBtn && userMenuWrapper) {
                userMenuBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (toolsDropdown) toolsDropdown.classList.remove('open');
                    userMenuWrapper.classList.toggle('open');
                    const isOpen = userMenuWrapper.classList.contains('open');
                    userMenuBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });
            }

            // Candidate Tools Dropdown Toggle
            if (toolsDropdownBtn && toolsDropdown) {
                toolsDropdownBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (userMenuWrapper) userMenuWrapper.classList.remove('open');
                    toolsDropdown.classList.toggle('open');
                    const isOpen = toolsDropdown.classList.contains('open');
                    toolsDropdownBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });
            }

            // Close dropdowns on outside click
            document.addEventListener('click', function(e) {
                if (userMenuWrapper && !userMenuWrapper.contains(e.target)) {
                    userMenuWrapper.classList.remove('open');
                    if (userMenuBtn) userMenuBtn.setAttribute('aria-expanded', 'false');
                }
                if (toolsDropdown && !toolsDropdown.contains(e.target)) {
                    toolsDropdown.classList.remove('open');
                    if (toolsDropdownBtn) toolsDropdownBtn.setAttribute('aria-expanded', 'false');
                }
            });

            // Escape key handler for all overlays & dropdowns
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeDrawer();
                    if (userMenuWrapper) {
                        userMenuWrapper.classList.remove('open');
                        if (userMenuBtn) userMenuBtn.setAttribute('aria-expanded', 'false');
                    }
                    if (toolsDropdown) {
                        toolsDropdown.classList.remove('open');
                        if (toolsDropdownBtn) toolsDropdownBtn.setAttribute('aria-expanded', 'false');
                    }
                }
            });
        });
    </script>

    {{-- Global AI Chat Assistant --}}
    @include('partials.ai_chat_widget')
</body>
</html>
