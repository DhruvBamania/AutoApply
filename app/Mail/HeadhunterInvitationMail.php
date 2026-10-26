<?php

namespace App\Mail;

use App\Models\HeadhunterMatch;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HeadhunterInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public HeadhunterMatch $match;

    public function __construct(HeadhunterMatch $match)
    {
        $this->match = $match;
    }

    public function envelope(): Envelope
    {
        $companyName = $this->match->jobPosting->user->companyProfile->company_name 
            ?? $this->match->jobPosting->user->name 
            ?? 'Hiring Team';

        $subject = $this->match->outreach_subject 
            ?: "{$companyName} scouted you for '{$this->match->jobPosting->title}'";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.headhunter_invitation',
            with: [
                'match' => $this->match,
                'job' => $this->match->jobPosting,
                'company' => $this->match->jobPosting->user->companyProfile,
            ],
        );
    }
}
