@extends('layouts.app')

@section('content')
<div style="max-width: 1100px; margin: 0 auto; display: flex; flex-direction: column; gap: 2rem;">

    <!-- Legal Header Card -->
    <div class="card" style="background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(254,243,199,0.85) 100%); border-left: 6px solid #d97706; padding: 2.5rem 1.75rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
            <div>
                <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 0.8rem; padding: 0.35rem 0.75rem; border-radius: 20px; font-weight: 700; margin-bottom: 0.5rem; display: inline-block;">
                    Cookie Transparency &amp; Tracking Notice
                </span>
                <h1 style="font-size: clamp(1.8rem, 3.2vw, 2.4rem); font-weight: 800; color: #78350f; margin: 0 0 0.5rem 0;">
                    Cookie Policy
                </h1>
                <p style="color: #64748b; font-size: 0.95rem; margin: 0;">
                    Last updated: <strong>{{ $lastUpdated }}</strong> &bull; Effective Date: <strong>{{ $effectiveDate }}</strong> &bull; Version 2.4
                </p>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="button" onclick="window.print()" class="btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem; border-radius: 10px; cursor: pointer;">
                    🖨️ Print Policy
                </button>
            </div>
        </div>
    </div>

    <!-- Legal Grid Layout (Sticky Sidebar + Content) -->
    <div style="display: grid; grid-template-columns: minmax(220px, 280px) 1fr; gap: 2rem; align-items: start;">
        
        <!-- Sticky Navigation Sidebar -->
        <div class="card" style="padding: 1.5rem; position: sticky; top: 1.5rem; background: rgba(255,255,255,0.9); backdrop-filter: blur(8px); border-radius: 16px; border: 1px solid #e2e8f0;">
            <h3 style="font-size: 0.9rem; font-weight: 700; color: #b45309; text-transform: uppercase; letter-spacing: 0.8px; margin: 0 0 1rem 0;">
                Cookie Sections
            </h3>
            <nav style="display: flex; flex-direction: column; gap: 0.6rem; font-size: 0.88rem;">
                <a href="#what-are-cookies" style="color: #475569; text-decoration: none; padding: 0.3rem 0.5rem; border-radius: 6px;">1. What Are Cookies</a>
                <a href="#how-we-use" style="color: #475569; text-decoration: none; padding: 0.3rem 0.5rem; border-radius: 6px;">2. How We Use Cookies</a>
                <a href="#cookie-categories" style="color: #475569; text-decoration: none; padding: 0.3rem 0.5rem; border-radius: 6px;">3. Cookie Categories</a>
                <a href="#cookie-table" style="color: #475569; text-decoration: none; padding: 0.3rem 0.5rem; border-radius: 6px;">4. Inventory of Cookies</a>
                <a href="#third-party" style="color: #475569; text-decoration: none; padding: 0.3rem 0.5rem; border-radius: 6px;">5. Third-Party Cookies</a>
                <a href="#managing-cookies" style="color: #475569; text-decoration: none; padding: 0.3rem 0.5rem; border-radius: 6px;">6. Managing Preferences</a>
                <a href="#contact-cookies" style="color: #475569; text-decoration: none; padding: 0.3rem 0.5rem; border-radius: 6px;">7. Questions &amp; Support</a>
            </nav>

            <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e2e8f0; font-size: 0.8rem; color: #64748b;">
                Related Legal Documents:<br>
                &bull; <a href="{{ route('terms') }}" style="color: #d97706;">Terms of Service</a><br>
                &bull; <a href="{{ route('privacy') }}" style="color: #d97706;">Privacy Policy</a>
            </div>
        </div>

        <!-- Main Cookie Policy Content -->
        <div class="card" style="padding: 2.5rem; background: #ffffff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.04); font-size: 0.95rem; line-height: 1.7; color: #334155;">
            
            <section id="what-are-cookies" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; color: #78350f; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem; margin-top: 0;">
                    1. What Are Cookies?
                </h2>
                <p>
                    Cookies are small text files placed on your computer, smartphone, or tablet when you visit websites. They are widely used to make websites function efficiently, preserve login sessions, prevent Cross-Site Request Forgery (CSRF), and provide analytical insights into website performance.
                </p>
            </section>

            <section id="how-we-use" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; color: #78350f; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem;">
                    2. How AutoApply Uses Cookies
                </h2>
                <p>
                    <strong>AutoApply &amp; AI Looping prioritizes user privacy.</strong> We only utilize cookies that are strictly necessary for core platform functionality, user authentication, and CSRF protection. <em>We do not use invasive third-party cross-site behavioral tracking or advertising cookies.</em>
                </p>
            </section>

            <section id="cookie-categories" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; color: #78350f; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem;">
                    3. Categories of Cookies We Deploy
                </h2>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div style="border-left: 4px solid #10b981; padding-left: 1rem; background: #f0fdf4; padding: 0.85rem 1rem; border-radius: 8px;">
                        <strong style="color: #065f46;">🔒 1. Strictly Necessary Cookies (Essential)</strong>
                        <p style="margin: 0.25rem 0 0 0; font-size: 0.9rem; color: #166534;">
                            These cookies are indispensable for website operation. They maintain your authenticated session, enforce single-device security, and prevent cross-site form tampering. You cannot disable these without disabling platform functionality.
                        </p>
                    </div>

                    <div style="border-left: 4px solid #3b82f6; padding-left: 1rem; background: #eff6ff; padding: 0.85rem 1rem; border-radius: 8px;">
                        <strong style="color: #1e40af;">⚙️ 2. Functional &amp; Preference Cookies</strong>
                        <p style="margin: 0.25rem 0 0 0; font-size: 0.9rem; color: #1e3a8a;">
                            These cookies store your UI preferences, such as selected resume template preview zoom, inquiry filter states, and language settings.
                        </p>
                    </div>
                </div>
            </section>

            <section id="cookie-table" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; color: #78350f; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem;">
                    4. Inventory of Cookies Used on Platform
                </h2>
                <div style="overflow-x: auto; margin-top: 1rem;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.88rem; text-align: left;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 2px solid #cbd5e1;">
                                <th style="padding: 10px 12px; font-weight: 700; color: #334155;">Cookie Name</th>
                                <th style="padding: 10px 12px; font-weight: 700; color: #334155;">Type</th>
                                <th style="padding: 10px 12px; font-weight: 700; color: #334155;">Purpose</th>
                                <th style="padding: 10px 12px; font-weight: 700; color: #334155;">Lifespan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 10px 12px; font-family: monospace; font-weight: 600; color: #4338ca;">laravel_session</td>
                                <td style="padding: 10px 12px;"><span class="badge" style="background: #dcfce7; color: #166534; font-size: 0.75rem;">Necessary</span></td>
                                <td style="padding: 10px 12px;">Stores encrypted PHP session identifier to identify your authenticated login state.</td>
                                <td style="padding: 10px 12px;">7 Days / Sliding</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 10px 12px; font-family: monospace; font-weight: 600; color: #4338ca;">XSRF-TOKEN</td>
                                <td style="padding: 10px 12px;"><span class="badge" style="background: #dcfce7; color: #166534; font-size: 0.75rem;">Security</span></td>
                                <td style="padding: 10px 12px;">Prevents Cross-Site Request Forgery attacks on forms and API endpoints.</td>
                                <td style="padding: 10px 12px;">7 Days</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 10px 12px; font-family: monospace; font-weight: 600; color: #4338ca;">remember_web_*</td>
                                <td style="padding: 10px 12px;"><span class="badge" style="background: #e0e7ff; color: #3730a3; font-size: 0.75rem;">Functional</span></td>
                                <td style="padding: 10px 12px;">Allows seamless re-authentication if "Remember Me" is selected upon login.</td>
                                <td style="padding: 10px 12px;">5 Years / Until Logout</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="third-party" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; color: #78350f; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem;">
                    5. Third-Party Integrations
                </h2>
                <p>
                    When connecting your external profiles (such as <strong>LinkedIn OAuth 2.0</strong> or Google SSO), those external identity providers may set third-party cookies subject to their own cookie and privacy notices. We recommend reviewing LinkedIn's and Google's respective cookie disclosures.
                </p>
            </section>

            <section id="managing-cookies" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; color: #78350f; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem;">
                    6. How to Manage or Disable Cookies in Your Browser
                </h2>
                <p>
                    You can restrict or block cookies through your browser settings. Please note that disabling essential cookies may prevent you from logging into your account or submitting resumes.
                </p>
                <ul style="padding-left: 1.5rem;">
                    <li><strong>Google Chrome:</strong> Settings &gt; Privacy and Security &gt; Cookies and other site data.</li>
                    <li><strong>Mozilla Firefox:</strong> Settings &gt; Privacy &amp; Security &gt; Cookies and Site Data.</li>
                    <li><strong>Apple Safari:</strong> Preferences &gt; Privacy &gt; Manage Website Data.</li>
                    <li><strong>Microsoft Edge:</strong> Settings &gt; Cookies and site permissions &gt; Manage and delete cookies.</li>
                </ul>
            </section>

            <section id="contact-cookies">
                <h2 style="font-size: 1.35rem; font-weight: 800; color: #78350f; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem;">
                    7. Questions &amp; Support
                </h2>
                <p>
                    If you have questions about our Cookie Policy or how we handle your browser data, please reach out via our contact channels:
                </p>
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 1.25rem 1.5rem; font-size: 0.9rem;">
                    <strong>AutoApply &amp; AI Looping Privacy Team</strong><br>
                    Email: <a href="mailto:privacy@ailooping.com" style="color: #b45309; font-weight: 600;">privacy@ailooping.com</a><br>
                    Support Page: <a href="{{ route('contact') }}" style="color: #b45309; font-weight: 600;">Contact Support Form</a>
                </div>
            </section>

        </div>

    </div>

</div>
@endsection
