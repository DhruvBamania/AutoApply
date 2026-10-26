@extends('layouts.app')

@section('content')
<div style="max-width: 1100px; margin: 0 auto; display: flex; flex-direction: column; gap: 2rem;">

    <!-- Legal Header Card -->
    <div class="card" style="background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(240,253,250,0.98) 100%); border-left: 6px solid #0d9488; padding: 2.5rem 1.75rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
            <div>
                <span class="badge" style="background: #ccfbf1; color: #115e59; font-size: 0.8rem; padding: 0.35rem 0.75rem; border-radius: 20px; font-weight: 700; margin-bottom: 0.5rem; display: inline-block;">
                    GDPR &amp; CCPA Compliant Privacy Notice
                </span>
                <h1 style="font-size: clamp(1.8rem, 3.2vw, 2.4rem); font-weight: 800; color: #134e4a; margin: 0 0 0.5rem 0;">
                    Privacy Policy
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
            <h3 style="font-size: 0.9rem; font-weight: 700; color: #0f766e; text-transform: uppercase; letter-spacing: 0.8px; margin: 0 0 1rem 0;">
                Privacy Sections
            </h3>
            <nav style="display: flex; flex-direction: column; gap: 0.6rem; font-size: 0.88rem;">
                <a href="#overview" style="color: #475569; text-decoration: none; padding: 0.3rem 0.5rem; border-radius: 6px;">1. Overview &amp; Scope</a>
                <a href="#data-collected" style="color: #475569; text-decoration: none; padding: 0.3rem 0.5rem; border-radius: 6px;">2. Information We Collect</a>
                <a href="#processing-purpose" style="color: #475569; text-decoration: none; padding: 0.3rem 0.5rem; border-radius: 6px;">3. How We Use Data</a>
                <a href="#ai-subprocessors" style="color: #475569; text-decoration: none; padding: 0.3rem 0.5rem; border-radius: 6px;">4. AI Sub-Processors &amp; LLMs</a>
                <a href="#data-sharing" style="color: #475569; text-decoration: none; padding: 0.3rem 0.5rem; border-radius: 6px;">5. Information Sharing</a>
                <a href="#data-retention" style="color: #475569; text-decoration: none; padding: 0.3rem 0.5rem; border-radius: 6px;">6. Data Retention</a>
                <a href="#user-rights" style="color: #475569; text-decoration: none; padding: 0.3rem 0.5rem; border-radius: 6px;">7. Your GDPR &amp; CCPA Rights</a>
                <a href="#security" style="color: #475569; text-decoration: none; padding: 0.3rem 0.5rem; border-radius: 6px;">8. Security Measures</a>
                <a href="#dpo" style="color: #475569; text-decoration: none; padding: 0.3rem 0.5rem; border-radius: 6px;">9. Data Protection Officer</a>
            </nav>

            <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e2e8f0; font-size: 0.8rem; color: #64748b;">
                Related Legal Documents:<br>
                &bull; <a href="{{ route('terms') }}" style="color: #0d9488;">Terms of Service</a><br>
                &bull; <a href="{{ route('cookies') }}" style="color: #0d9488;">Cookie Policy</a>
            </div>
        </div>

        <!-- Main Privacy Content -->
        <div class="card" style="padding: 2.5rem; background: #ffffff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.04); font-size: 0.95rem; line-height: 1.7; color: #334155;">
            
            <section id="overview" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; color: #134e4a; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem; margin-top: 0;">
                    1. Overview &amp; Scope
                </h2>
                <p>
                    At <strong>AutoApply &amp; AI Looping</strong>, we are committed to upholding the highest standards of data privacy, algorithmic transparency, and security. This Privacy Policy details how we collect, store, process, and safeguard your personal information when you use our website, web application, mobile applications, APIs, and recruitment automation tools.
                </p>
                <p>
                    We operate in compliance with the European Union General Data Protection Regulation (<strong>GDPR</strong>), the California Consumer Privacy Act (<strong>CCPA</strong>), and international privacy frameworks.
                </p>
            </section>

            <section id="data-collected" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; color: #134e4a; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem;">
                    2. Information We Collect
                </h2>
                <p>Depending on whether you use the Platform as a candidate or an employer, we collect the following categories of information:</p>
                <ul style="padding-left: 1.5rem;">
                    <li><strong>Account &amp; Identity Data:</strong> Full name, email address, password hash, role selection (candidate or company), and company name for employers.</li>
                    <li><strong>Candidate Profile &amp; Resume Data:</strong> Uploaded resume files (PDF, DOCX), extracted resume text, work experience, education, technical skills, certifications, and portfolio links.</li>
                    <li><strong>Recruiter &amp; Job Posting Data:</strong> Job titles, role descriptions, required skills, salary ranges, location requirements, and recruiter notes.</li>
                    <li><strong>Communication &amp; Contact Records:</strong> Messages submitted via the Contact Us form, email correspondence, and candidate responses to AI Headhunter invitations.</li>
                    <li><strong>Technical &amp; Log Data:</strong> IP address, device user-agent string, operating system, session timestamps, and authentication tokens.</li>
                </ul>
            </section>

            <section id="processing-purpose" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; color: #134e4a; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem;">
                    3. How We Process &amp; Use Your Information
                </h2>
                <p>We process your data strictly under valid lawful bases (contract fulfillment, legitimate interest, and explicit user consent):</p>
                <ul style="padding-left: 1.5rem;">
                    <li><strong>AI Semantic Matching:</strong> Evaluating candidate resume skills against active job descriptions to compute affinity scores and identify qualifying skill matches.</li>
                    <li><strong>Autonomous AI Headhunter Sourcing:</strong> Recommending top-tier candidate profiles to hiring teams and generating personalized outreach pitch drafts.</li>
                    <li><strong>Multi-Platform Syndication:</strong> Formatting and distributing employer job posts across connected networks (LinkedIn, Naukri, Indeed ATS).</li>
                    <li><strong>Interview Preparation Studio:</strong> Generating personalized technical interview questions, system design challenges, and live AI feedback tailored to candidate experience.</li>
                    <li><strong>Account Security:</strong> Enforcing single-session security, preventing brute-force attacks, and authenticating mobile requests via Laravel Sanctum.</li>
                </ul>
            </section>

            <section id="ai-subprocessors" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; color: #134e4a; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem;">
                    4. AI Sub-Processors &amp; Large Language Models (LLMs)
                </h2>
                <p>
                    Our cognitive matchmaking, resume tailoring, and interview generation utilize third-party enterprise AI providers:
                </p>
                <div style="background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 10px; padding: 1.25rem 1.5rem; margin-bottom: 1rem;">
                    <strong style="color: #0f766e; display: block; margin-bottom: 0.5rem;">Zero Data-Training Guarantee:</strong>
                    Under our enterprise agreements with <strong>Google Cloud (Gemini 2.5 Flash)</strong> and <strong>Groq Cloud (Llama 3.3)</strong>, your submitted resumes, candidate profiles, and company hiring criteria are processed ephemerally. <em>None of your private career or recruitment data is retained or used to train public machine learning models.</em>
                </div>
            </section>

            <section id="data-sharing" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; color: #134e4a; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem;">
                    5. Information Sharing &amp; Third-Party Disclosure
                </h2>
                <p>
                    <strong>We do not sell, rent, or monetize your personal data.</strong> Information is shared solely in the following operational contexts:
                </p>
                <ul style="padding-left: 1.5rem;">
                    <li><strong>Between Candidates &amp; Employers:</strong> When a candidate applies for a job posting or accepts an AI Headhunter invitation, their profile and resume details become visible to the prospective employer's hiring dashboard.</li>
                    <li><strong>Connected Job Platforms:</strong> When an employer explicitly publishes a job, the job metadata is transmitted to the chosen job board APIs (LinkedIn, Naukri, Indeed).</li>
                    <li><strong>Email Delivery Infrastructure:</strong> Transactional alerts, shortlist notifications, and contact form confirmations are transmitted via authenticated Google SMTP servers.</li>
                </ul>
            </section>

            <section id="data-retention" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; color: #134e4a; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem;">
                    6. Data Retention Policy
                </h2>
                <p>
                    We retain personal information for as long as your account remains active or as needed to provide you with recruitment services. Inactive guest sessions and temporary resume tailoring caches are automatically purged after 30 days. You may request immediate deletion of your account and all associated data at any time.
                </p>
            </section>

            <section id="user-rights" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; color: #134e4a; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem;">
                    7. Your Privacy Rights (GDPR &amp; CCPA)
                </h2>
                <p>Regardless of your geographic location, AutoApply grants all registered users the following privacy rights:</p>
                <ul style="padding-left: 1.5rem;">
                    <li><strong>Right to Access:</strong> You may request an export of all personal data held about you in a structured, readable format.</li>
                    <li><strong>Right to Rectification:</strong> You may update or correct your profile, resume, and contact information directly in your account dashboard.</li>
                    <li><strong>Right to Erasure ("Right to be Forgotten"):</strong> You may request complete deletion of your user account, uploaded resumes, and matching logs.</li>
                    <li><strong>Right to Restrict or Object to AI Processing:</strong> You may opt out of automated talent scouting by updating your profile visibility settings.</li>
                    <li><strong>Non-Discrimination:</strong> We will never discriminate against you, alter service quality, or deny features for exercising your privacy rights.</li>
                </ul>
            </section>

            <section id="security" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; color: #134e4a; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem;">
                    8. Security Measures
                </h2>
                <p>
                    We implement defense-in-depth security architectures to protect your data against unauthorized access, alteration, or disclosure:
                </p>
                <ul style="padding-left: 1.5rem;">
                    <li><strong>TLS 1.3 Encryption:</strong> All data in transit across our web portals, REST APIs, and mobile app channels is encrypted using modern TLS 1.3 protocols.</li>
                    <li><strong>Credential Protection:</strong> Sensitive API tokens and OAuth refresh secrets are stored using AES-256-CBC database encryption.</li>
                    <li><strong>Automated Single-Session Control:</strong> Login sessions are dynamically validated with weekly sliding windows and superseded device revocation.</li>
                </ul>
            </section>

            <section id="dpo">
                <h2 style="font-size: 1.35rem; font-weight: 800; color: #134e4a; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem;">
                    9. Contacting Our Data Protection Officer (DPO)
                </h2>
                <p>
                    To exercise any of your privacy rights, request data deletion, or ask questions regarding our privacy practices, please contact our Data Protection Office:
                </p>
                <div style="background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 10px; padding: 1.25rem 1.5rem; font-size: 0.9rem;">
                    <strong>AutoApply &amp; AI Looping Data Protection Office</strong><br>
                    Email: <a href="mailto:privacy@ailooping.com" style="color: #0f766e; font-weight: 600;">privacy@ailooping.com</a> &bull; Direct DPO: <a href="mailto:dpo@ailooping.com" style="color: #0f766e; font-weight: 600;">dpo@ailooping.com</a><br>
                    Online Request: <a href="{{ route('contact') }}" style="color: #0f766e; font-weight: 600;">Contact Us Form (Select "Legal, GDPR &amp; Privacy Compliance")</a><br>
                    Response SLA: Within 10 business days for all formal data access or deletion requests.
                </div>
            </section>

        </div>

    </div>

</div>
@endsection
