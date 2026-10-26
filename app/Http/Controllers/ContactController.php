<?php

namespace App\Http\Controllers;

use App\Jobs\SendContactInquiryEmailsJob;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    /**
     * Show the Contact Us page with Interactive FAQ Accordion.
     */
    public function show()
    {
        $user = Auth::user();

        // Interactive FAQs grouped by category
        $faqs = [
            'candidate' => [
                'title' => 'Candidate & Job Seeker FAQs',
                'icon' => '👤',
                'items' => [
                    [
                        'q' => 'How does the AI Autonomous Headhunter Agent scout me?',
                        'a' => 'Our AI Headhunter analyzes your uploaded resume skills, project history, and experience against active employer job requirements. When an affinity match is detected (typically ≥60%), employers can review your profile and send a personalized outreach pitch directly to your inbox and Scouted Jobs hub.',
                    ],
                    [
                        'q' => 'Is there any charge for candidates to use the resume builder or receive job invitations?',
                        'a' => 'No. All candidate tools—including dynamic resume building, ATS template exports, AI interview preparation studio, and employer outreach invitations—are 100% free for job seekers.',
                    ],
                    [
                        'q' => 'How can I update or delete my resume data?',
                        'a' => 'You can edit or update your resume anytime in the Dynamic Editor or Resume Tailoring studio. To request full deletion of your profile and data in compliance with GDPR/CCPA, submit a "Legal & Compliance" inquiry or contact privacy@ailooping.com.',
                    ],
                ],
            ],
            'company' => [
                'title' => 'Employer & Recruiter Solutions',
                'icon' => '🏢',
                'items' => [
                    [
                        'q' => 'How does 1-Click Multi-Platform Job Syndication work?',
                        'a' => 'When posting a new role, you can select LinkedIn, Naukri, and Indeed. With one click, AutoApply distributes your job post, formats ATS XML feeds for Indeed, and posts live updates to connected accounts via official OAuth APIs.',
                    ],
                    [
                        'q' => 'What happens when a scouted candidate accepts an invitation?',
                        'a' => 'When a candidate clicks "Accept & Connect" on your headhunter outreach, they are automatically placed into your candidate inquiries hub tagged as "shortlisted", allowing you to immediately schedule technical interviews.',
                    ],
                    [
                        'q' => 'Can we try the platform in Sandbox Mode without live corporate API credentials?',
                        'a' => 'Yes! The Integrations tab includes a zero-dependency Sandbox simulator where you can test multi-platform job distribution, comment sync, and applicant inquiries without live API keys.',
                    ],
                ],
            ],
            'technical' => [
                'title' => 'Security, AI & Technical Support',
                'icon' => '🔒',
                'items' => [
                    [
                        'q' => 'What AI models power the matching and interview prep?',
                        'a' => 'We utilize Google Gemini 2.5 Flash as our primary cognitive reasoning engine, paired with Groq Llama 3.3 as an ultra-fast resilient fallback and heuristic semantic matchers to ensure 100% continuous uptime.',
                    ],
                    [
                        'q' => 'Is my corporate hiring and candidate data encrypted?',
                        'a' => 'Yes. All data in transit is protected using TLS 1.3 encryption, and sensitive tokens and database credentials use AES-256-CBC encryption via Laravel Sanctum and secure storage.',
                    ],
                ],
            ],
        ];

        return view('pages.contact', compact('user', 'faqs'));
    }

    /**
     * Handle inbound Contact Us form submission.
     */
    public function submit(Request $request)
    {
        // Anti-spam Honeypot check: If the hidden honeypot field is filled, silently ignore
        if (!empty($request->input('website_hp'))) {
            return redirect()->route('contact')->with('success', 'Thank you! Your message has been received.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:30',
            'category' => 'required|string|in:general,candidate_support,company_inquiry,enterprise_sales,bug_report,legal',
            'subject' => 'required|string|min:4|max:200',
            'message' => 'required|string|min:10|max:5000',
        ]);

        $contactMessage = ContactMessage::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'category' => $validated['category'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => 'unread',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string)$request->userAgent(), 0, 500),
        ]);

        // Dispatch queued background emails (Admin notification + User confirmation receipt)
        SendContactInquiryEmailsJob::dispatchAfterResponse($contactMessage);

        return redirect()->route('contact')->with(
            'success',
            "🎉 Thank you, {$contactMessage->name}! Your inquiry has been logged as Ticket #{$contactMessage->ticket_id}. A confirmation email has been dispatched to {$contactMessage->email}."
        );
    }
}
