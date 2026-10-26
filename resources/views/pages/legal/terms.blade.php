@extends('layouts.app')

@section('content')
<div style="max-width: 1100px; margin: 0 auto; display: flex; flex-direction: column; gap: 2rem;">

    <!-- Legal Header Card -->
    <div class="card" style="background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(243,244,246,0.98) 100%); border-left: 6px solid #4338ca; padding: 2.5rem 1.75rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
            <div>
                <span class="badge" style="background: #e0e7ff; color: #3730a3; font-size: 0.8rem; padding: 0.35rem 0.75rem; border-radius: 20px; font-weight: 700; margin-bottom: 0.5rem; display: inline-block;">
                    Legal Compliance &amp; Governance
                </span>
                <h1 style="font-size: clamp(1.8rem, 3.2vw, 2.4rem); font-weight: 800; color: #1e1b4b; margin: 0 0 0.5rem 0;">
                    Terms of Service
                </h1>
                <p style="color: #64748b; font-size: 0.95rem; margin: 0;">
                    Last updated: <strong>{{ $lastUpdated }}</strong> &bull; Effective Date: <strong>{{ $effectiveDate }}</strong> &bull; Version 2.4
                </p>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="button" onclick="window.print()" class="btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem; border-radius: 10px; cursor: pointer;">
                    🖨️ Print Terms
                </button>
            </div>
        </div>
    </div>

    <!-- Legal Grid Layout (Sticky Sidebar + Content) -->
    <div style="display: grid; grid-template-columns: minmax(220px, 280px) 1fr; gap: 2rem; align-items: start;">
        
        <!-- Sticky Navigation Sidebar -->
        <div class="card" style="padding: 1.5rem; position: sticky; top: 1.5rem; background: rgba(255,255,255,0.9); backdrop-filter: blur(8px); border-radius: 16px; border: 1px solid #e2e8f0;">
            <h3 style="font-size: 0.9rem; font-weight: 700; color: #4338ca; text-transform: uppercase; letter-spacing: 0.8px; margin: 0 0 1rem 0;">
                Table of Contents
            </h3>
            <nav style="display: flex; flex-direction: column; gap: 0.6rem; font-size: 0.88rem;">
                <a href="#acceptance" style="color: #475569; text-decoration: none; padding: 0.3rem 0.5rem; border-radius: 6px; transition: all 0.2s;">1. Acceptance of Terms</a>
                <a href="#services" style="color: #475569; text-decoration: none; padding: 0.3rem 0.5rem; border-radius: 6px; transition: all 0.2s;">2. Description of Services</a>
                <a href="#accounts" style="color: #475569; text-decoration: none; padding: 0.3rem 0.5rem; border-radius: 6px; transition: all 0.2s;">3. Accounts &amp; Dual Roles</a>
                <a href="#ai-matching" style="color: #475569; text-decoration: none; padding: 0.3rem 0.5rem; border-radius: 6px; transition: all 0.2s;">4. AI Headhunter &amp; Matching</a>
                <a href="#syndication" style="color: #475569; text-decoration: none; padding: 0.3rem 0.5rem; border-radius: 6px; transition: all 0.2s;">5. Job Syndication &amp; Feeds</a>
                <a href="#intellectual-property" style="color: #475569; text-decoration: none; padding: 0.3rem 0.5rem; border-radius: 6px; transition: all 0.2s;">6. Intellectual Property</a>
                <a href="#disclaimers" style="color: #475569; text-decoration: none; padding: 0.3rem 0.5rem; border-radius: 6px; transition: all 0.2s;">7. Disclaimers &amp; AI Output</a>
                <a href="#liability" style="color: #475569; text-decoration: none; padding: 0.3rem 0.5rem; border-radius: 6px; transition: all 0.2s;">8. Limitation of Liability</a>
                <a href="#termination" style="color: #475569; text-decoration: none; padding: 0.3rem 0.5rem; border-radius: 6px; transition: all 0.2s;">9. Termination &amp; Suspension</a>
                <a href="#contact-legal" style="color: #475569; text-decoration: none; padding: 0.3rem 0.5rem; border-radius: 6px; transition: all 0.2s;">10. Contact &amp; Inquiries</a>
            </nav>

            <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e2e8f0; font-size: 0.8rem; color: #64748b;">
                Related Legal Documents:<br>
                &bull; <a href="{{ route('privacy') }}" style="color: #4f46e5;">Privacy Policy</a><br>
                &bull; <a href="{{ route('cookies') }}" style="color: #4f46e5;">Cookie Policy</a>
            </div>
        </div>

        <!-- Main Terms Content -->
        <div class="card" style="padding: 2.5rem; background: #ffffff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.04); font-size: 0.95rem; line-height: 1.7; color: #334155;">
            
            <section id="acceptance" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; color: #1e1b4b; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem; margin-top: 0;">
                    1. Acceptance of Terms
                </h2>
                <p>
                    These Terms of Service ("Terms") constitute a legally binding agreement between you ("User", "you", or "your") and <strong>AutoApply &amp; AI Looping Intelligence Systems</strong> ("AutoApply", "we", "us", or "our"). By accessing or using our website, web application, mobile application (APK), APIs, or related services (collectively, the "Platform"), you acknowledge that you have read, understood, and agree to be bound by these Terms.
                </p>
                <p>
                    If you do not agree to these Terms, you must not access or use our Platform. If you are using the Platform on behalf of a company, employer, or legal entity, you represent and warrant that you have full legal authority to bind that entity to these Terms.
                </p>
            </section>

            <section id="services" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; color: #1e1b4b; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem;">
                    2. Description of Services
                </h2>
                <p>
                    AutoApply provides an enterprise recruitment and career automation ecosystem featuring:
                </p>
                <ul style="padding-left: 1.5rem; margin-bottom: 1rem;">
                    <li><strong>Candidate Career Studio:</strong> Dynamic ATS resume creation, AI tailoring, social leadership campaigns, and interactive technical interview simulations.</li>
                    <li><strong>Employer Recruitment Suite:</strong> Multi-platform 1-click job syndication across LinkedIn, Naukri, and Indeed ATS feeds.</li>
                    <li><strong>Autonomous AI Headhunter Agent:</strong> Cognitive talent discovery, resume affinity ranking, personalized recruiter pitch drafting, and 1-click shortlist linking.</li>
                    <li><strong>Mobile Client App:</strong> Cross-platform Android application providing mobile parity for candidate discovery and employer management.</li>
                </ul>
            </section>

            <section id="accounts" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; color: #1e1b4b; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem;">
                    3. Accounts, Dual Roles &amp; Security
                </h2>
                <p>
                    To access certain core features, you must register an account. During registration, you must specify your role as either an individual <strong>Candidate</strong> or an <strong>Employer/Company</strong>.
                </p>
                <ul style="padding-left: 1.5rem;">
                    <li><strong>Single-Device Session Policy:</strong> To prevent unauthorized account sharing and credential abuse, logging in from a new browser or device terminates existing active sessions in accordance with our single-session authentication protocol.</li>
                    <li><strong>Credential Confidentiality:</strong> You are strictly responsible for maintaining the confidentiality of your login credentials and API tokens. You agree to notify us immediately at <a href="mailto:security@ailooping.com" style="color: #4f46e5;">security@ailooping.com</a> of any unauthorized access.</li>
                </ul>
            </section>

            <section id="ai-matching" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; color: #1e1b4b; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem;">
                    4. AI Autonomous Headhunter &amp; Candidate Matching
                </h2>
                <p>
                    Our AI Headhunter Agent and candidate matching engines utilize machine learning models (including Google Gemini 2.5 Flash and Groq Llama 3) to evaluate affinity between candidate profile data and job specifications.
                </p>
                <p>
                    <strong>Human-in-the-Loop Requirement:</strong> While our AI agent suggests match scores (0-100), generates match rationales, and drafts outreach pitches, all employment actions—including sending outreach invitations, scheduling interviews, and making hiring decisions—are governed and initiated by human recruiters. Match scores are advisory metrics and do not constitute an automated hiring or employment guarantee.
                </p>
            </section>

            <section id="syndication" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; color: #1e1b4b; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem;">
                    5. Job Syndication &amp; Third-Party Platform Terms
                </h2>
                <p>
                    When employers distribute job postings through our 1-click syndication to external platforms (LinkedIn, Naukri, Indeed), you agree to comply with the respective third-party terms of service and developer policies. AutoApply is not responsible for external content takedowns, API suspensions, or policy enforcement actions enacted by third-party job boards.
                </p>
            </section>

            <section id="intellectual-property" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; color: #1e1b4b; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem;">
                    6. Intellectual Property &amp; Content Ownership
                </h2>
                <p>
                    <strong>Your Content:</strong> You retain full copyright and ownership of all resumes, cover letters, company logos, and job descriptions uploaded to the Platform. By submitting your content, you grant AutoApply a worldwide, non-exclusive, royalty-free license to parse, index, format, and display your content solely for the purpose of operating and delivering Platform services to you.
                </p>
                <p>
                    <strong>Platform IP:</strong> All code, algorithms, visual designs, trademarks, and documentation comprising AutoApply &amp; AI Looping are the exclusive intellectual property of AutoApply Systems and are protected under international copyright and intellectual property laws.
                </p>
            </section>

            <section id="disclaimers" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; color: #1e1b4b; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem;">
                    7. Disclaimers &amp; AI Output Accuracy
                </h2>
                <p style="background: #f8fafc; border-left: 4px solid #64748b; padding: 1rem 1.25rem; font-size: 0.9rem; border-radius: 6px;">
                    THE PLATFORM AND ALL AI-GENERATED CONTENT (INCLUDING RESUME TAILORING, MATCH SCORES, OUTREACH PITCHES, AND INTERVIEW SIMULATIONS) ARE PROVIDED ON AN "AS IS" AND "AS AVAILABLE" BASIS WITHOUT WARRANTIES OF ANY KIND, EXPRESS OR IMPLIED.
                </p>
                <p>
                    While our multi-tier AI evaluation pipeline strives for high precision, AutoApply does not warrant that AI-generated suggestions, interview questions, or cold email drafts will be error-free or guarantee employment, job interviews, or hiring placement. Users are responsible for reviewing and verifying all generated materials prior to distribution.
                </p>
            </section>

            <section id="liability" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; color: #1e1b4b; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem;">
                    8. Limitation of Liability
                </h2>
                <p>
                    To the maximum extent permitted by applicable law, in no event shall AutoApply, its affiliates, directors, employees, or licensors be liable for any indirect, incidental, special, consequential, or punitive damages, including loss of profits, data, employment opportunities, or goodwill, arising out of or related to your use of or inability to use the Platform.
                </p>
            </section>

            <section id="termination" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; color: #1e1b4b; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem;">
                    9. Termination &amp; Suspension
                </h2>
                <p>
                    We reserve the right to suspend or terminate your account or access to the Platform immediately, without prior notice or liability, if you violate these Terms, engage in scraping without authorization, post fraudulent job opportunities, or abuse our automated services.
                </p>
            </section>

            <section id="contact-legal">
                <h2 style="font-size: 1.35rem; font-weight: 800; color: #1e1b4b; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem;">
                    10. Contact &amp; Legal Notices
                </h2>
                <p>
                    For inquiries, clarifications, or legal notices concerning these Terms of Service, please contact our Legal &amp; Governance department:
                </p>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem 1.5rem; font-size: 0.9rem;">
                    <strong>AutoApply &amp; AI Looping Legal Department</strong><br>
                    Email: <a href="mailto:denbamania2005@gmail.com" style="color: #4f46e5; font-weight: 600;">legal@ailooping.com</a><br>
                    Online Form: <a href="{{ route('contact') }}" style="color: #4f46e5; font-weight: 600;">Contact Us Form (Select "Legal &amp; Compliance")</a><br>
                    Address: Thaltej, Ahmedabad, 380063, India
                </div>
            </section>

        </div>

    </div>

</div>
@endsection
