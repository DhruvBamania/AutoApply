<?php

namespace App\Services;

use App\Models\JobPosting;
use App\Models\JobInquiry;
use App\Models\HeadhunterMatch;
use App\Models\WhatsAppMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwilioWhatsAppService
{
    protected ?string $accountSid;
    protected ?string $authToken;
    protected string $fromNumber;

    public function __construct()
    {
        $this->accountSid = config('services.twilio.sid');
        $this->authToken = config('services.twilio.token');
        $this->fromNumber = config('services.twilio.whatsapp_from', '+14155238886');
    }

    /**
     * Check if active Twilio credentials have been configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->accountSid) && !empty($this->authToken) && !str_starts_with($this->accountSid, 'AC_dummy');
    }

    /**
     * Format a telephone number into standard Twilio WhatsApp format: whatsapp:+E164
     */
    public function formatWhatsAppAddress(string $phone): string
    {
        // Strip everything except numbers and plus sign
        $cleaned = preg_replace('/[^0-9+]/', '', trim($phone));

        if (!str_starts_with($cleaned, '+')) {
            // Default to India (+91) if 10 digits provided, otherwise prepend +
            if (strlen($cleaned) === 10) {
                $cleaned = '+91' . $cleaned;
            } else {
                $cleaned = '+' . $cleaned;
            }
        }

        return 'whatsapp:' . $cleaned;
    }

    /**
     * Send a WhatsApp message via Twilio Programmable Messaging API.
     */
    public function sendRawMessage(string $toPhone, string $messageText, array $context = []): array
    {
        $toWhatsApp = $this->formatWhatsAppAddress($toPhone);
        $fromWhatsApp = str_starts_with($this->fromNumber, 'whatsapp:')
            ? $this->fromNumber
            : 'whatsapp:' . (str_starts_with($this->fromNumber, '+') ? $this->fromNumber : '+' . $this->fromNumber);

        // If in testing or credentials are not yet configured, record as simulated
        if (app()->environment('testing') || !$this->isConfigured()) {
            $simulatedSid = 'SM_simulated_' . bin2hex(random_bytes(10));
            Log::info("Twilio WhatsApp Simulated Dispatch to {$toWhatsApp}: {$messageText}");

            $msgRecord = WhatsAppMessage::create([
                'job_posting_id' => $context['job_posting_id'] ?? null,
                'job_inquiry_id' => $context['job_inquiry_id'] ?? null,
                'headhunter_match_id' => $context['headhunter_match_id'] ?? null,
                'direction' => $context['direction'] ?? 'candidate_to_company',
                'from_number' => $fromWhatsApp,
                'to_number' => $toWhatsApp,
                'sender_name' => $context['sender_name'] ?? 'Candidate',
                'sender_role' => $context['sender_role'] ?? 'candidate',
                'message' => $messageText,
                'twilio_message_sid' => $simulatedSid,
                'status' => 'simulated',
                'error_message' => null,
            ]);

            return [
                'success' => true,
                'sid' => $simulatedSid,
                'status' => 'simulated',
                'record' => $msgRecord,
            ];
        }

        // Live Twilio REST API Request
        try {
            $endpoint = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json";

            $response = Http::withBasicAuth($this->accountSid, $this->authToken)
                ->asForm()
                ->post($endpoint, [
                    'From' => $fromWhatsApp,
                    'To' => $toWhatsApp,
                    'Body' => $messageText,
                ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $sid = $responseData['sid'] ?? null;

                $msgRecord = WhatsAppMessage::create([
                    'job_posting_id' => $context['job_posting_id'] ?? null,
                    'job_inquiry_id' => $context['job_inquiry_id'] ?? null,
                    'headhunter_match_id' => $context['headhunter_match_id'] ?? null,
                    'direction' => $context['direction'] ?? 'candidate_to_company',
                    'from_number' => $fromWhatsApp,
                    'to_number' => $toWhatsApp,
                    'sender_name' => $context['sender_name'] ?? 'Candidate',
                    'sender_role' => $context['sender_role'] ?? 'candidate',
                    'message' => $messageText,
                    'twilio_message_sid' => $sid,
                    'status' => 'sent',
                ]);

                return [
                    'success' => true,
                    'sid' => $sid,
                    'status' => 'sent',
                    'record' => $msgRecord,
                ];
            }

            $errorBody = $response->json();
            $errorMessage = $errorBody['message'] ?? 'Twilio API returned error code ' . $response->status();

            Log::error("Twilio WhatsApp API Error: " . json_encode($errorBody));

            $msgRecord = WhatsAppMessage::create([
                'job_posting_id' => $context['job_posting_id'] ?? null,
                'job_inquiry_id' => $context['job_inquiry_id'] ?? null,
                'headhunter_match_id' => $context['headhunter_match_id'] ?? null,
                'direction' => $context['direction'] ?? 'candidate_to_company',
                'from_number' => $fromWhatsApp,
                'to_number' => $toWhatsApp,
                'sender_name' => $context['sender_name'] ?? 'Candidate',
                'sender_role' => $context['sender_role'] ?? 'candidate',
                'message' => $messageText,
                'status' => 'failed',
                'error_message' => $errorMessage,
            ]);

            return [
                'success' => false,
                'error' => $errorMessage,
                'status' => 'failed',
                'record' => $msgRecord,
            ];
        } catch (\Throwable $e) {
            Log::error("Twilio WhatsApp Dispatch Exception: " . $e->getMessage());

            $msgRecord = WhatsAppMessage::create([
                'job_posting_id' => $context['job_posting_id'] ?? null,
                'job_inquiry_id' => $context['job_inquiry_id'] ?? null,
                'headhunter_match_id' => $context['headhunter_match_id'] ?? null,
                'direction' => $context['direction'] ?? 'candidate_to_company',
                'from_number' => $fromWhatsApp,
                'to_number' => $toWhatsApp,
                'sender_name' => $context['sender_name'] ?? 'Candidate',
                'sender_role' => $context['sender_role'] ?? 'candidate',
                'message' => $messageText,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'failed',
                'record' => $msgRecord,
            ];
        }
    }

    /**
     * Candidate sends WhatsApp message to the company regarding a job posting.
     */
    public function sendCandidateInquiry(JobPosting $job, array $data): array
    {
        $companyUser = $job->user;
        $companyProfile = $companyUser->companyProfile;

        // Determine destination company WhatsApp phone
        $companyWhatsApp = $job->whatsapp_number 
            ?: ($companyProfile->whatsapp_number ?? null);

        // Fallback to user phone or default if not set
        if (!$companyWhatsApp) {
            $companyWhatsApp = '+919876543210'; // Fallback demo business contact
        }

        $candidateName = $data['candidate_name'] ?? 'Anonymous Candidate';
        $candidatePhone = $data['candidate_phone'] ?? 'Not provided';
        $candidateEmail = $data['candidate_email'] ?? 'Not provided';
        $inquiryMessage = $data['message'] ?? 'Interested in this job posting.';
        $companyName = $companyProfile->company_name ?? $companyUser->name;

        // Structured WhatsApp message delivered to company
        $formattedMessage = "💬 *New WhatsApp Candidate Inquiry*\n"
            . "🏢 *Company:* {$companyName}\n"
            . "💼 *Job Role:* {$job->title}\n"
            . "👤 *Candidate:* {$candidateName}\n"
            . "📱 *Phone:* {$candidatePhone}\n"
            . "✉️ *Email:* {$candidateEmail}\n\n"
            . "📝 *Candidate Message:*\n\"{$inquiryMessage}\"\n\n"
            . "⚡ *Sent via AI Looping Portal*";

        // Create unified JobInquiry in database so company sees it in Inquiries Hub
        $inquiry = JobInquiry::create([
            'job_posting_id' => $job->id,
            'platform' => 'whatsapp',
            'type' => 'query',
            'candidate_name' => $candidateName,
            'candidate_email' => $candidateEmail,
            'candidate_phone' => $candidatePhone,
            'candidate_headline' => $data['candidate_headline'] ?? 'WhatsApp Applicant',
            'message' => $inquiryMessage,
            'status' => 'new',
        ]);

        $dispatchResult = $this->sendRawMessage($companyWhatsApp, $formattedMessage, [
            'job_posting_id' => $job->id,
            'job_inquiry_id' => $inquiry->id,
            'direction' => 'candidate_to_company',
            'sender_name' => $candidateName,
            'sender_role' => 'candidate',
        ]);

        return array_merge($dispatchResult, [
            'inquiry' => $inquiry,
            'company_whatsapp' => $companyWhatsApp,
        ]);
    }

    /**
     * Company sends a WhatsApp reply to a candidate.
     */
    public function sendCompanyReply(JobInquiry $inquiry, string $replyText, $companyUser): array
    {
        $candidatePhone = $inquiry->candidate_phone;
        if (empty($candidatePhone)) {
            return [
                'success' => false,
                'error' => 'Candidate did not provide a phone number to message via WhatsApp.',
            ];
        }

        $job = $inquiry->jobPosting;
        $companyName = $companyUser->companyProfile->company_name ?? $companyUser->name;

        $formattedMessage = "🏢 *Update from {$companyName}*\n"
            . "Regarding your inquiry for *{$job->title}*:\n\n"
            . "💬 *Employer Message:*\n\"{$replyText}\"\n\n"
            . "Best regards,\n{$companyName} Hiring Team";

        $result = $this->sendRawMessage($candidatePhone, $formattedMessage, [
            'job_posting_id' => $job->id,
            'job_inquiry_id' => $inquiry->id,
            'direction' => 'company_to_candidate',
            'sender_name' => $companyName,
            'sender_role' => 'company',
        ]);

        if ($result['success']) {
            $inquiry->update([
                'company_reply' => $replyText,
                'status' => 'replied',
                'replied_at' => now(),
            ]);
        }

        return $result;
    }

    /**
     * Company initiates WhatsApp outreach to a scouted Headhunter match candidate.
     */
    public function sendHeadhunterOutreach(HeadhunterMatch $match, string $customMessage, $companyUser, ?string $candidatePhone = null): array
    {
        $phone = $candidatePhone;
        if (empty($phone)) {
            return [
                'success' => false,
                'error' => 'Candidate phone number is required for WhatsApp outreach.',
            ];
        }

        $job = $match->jobPosting;
        $companyName = $companyUser->companyProfile->company_name ?? $companyUser->name;

        $formattedMessage = "👋 *Hello {$match->candidate_name}!* \n\n"
            . "{$customMessage}\n\n"
            . "🏢 *Company:* {$companyName}\n"
            . "💼 *Position:* {$job->title}\n"
            . "📍 *Location:* {$job->location}\n\n"
            . "Reply to this message directly or on AI Looping to explore this opportunity!";

        $result = $this->sendRawMessage($phone, $formattedMessage, [
            'job_posting_id' => $job->id,
            'headhunter_match_id' => $match->id,
            'direction' => 'company_to_candidate',
            'sender_name' => $companyName,
            'sender_role' => 'company',
        ]);

        if ($result['success']) {
            $match->update([
                'status' => 'invited',
                'invited_at' => now(),
            ]);
        }

        return $result;
    }
}
