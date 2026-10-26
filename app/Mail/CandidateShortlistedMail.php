<?php

namespace App\Mail;

use App\Models\JobInquiry;
use App\Models\JobPosting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CandidateShortlistedMail extends Mailable
{
    use Queueable, SerializesModels;

    public JobInquiry $inquiry;
    public JobPosting $job;
    public string $companyName;
    public string $recruiterEmail;

    /**
     * Create a new message instance.
     */
    public function __construct(JobInquiry $inquiry, JobPosting $job)
    {
        $this->inquiry = $inquiry;
        $this->job = $job;
        $this->companyName = $job->user->companyProfile->company_name ?? $job->user->name ?? 'Our Company';
        $this->recruiterEmail = $job->user->email ?? config('mail.from.address');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "⭐ You're Shortlisted! Interview Invitation for {$this->job->title} at {$this->companyName}",
            replyTo: [
                new \Illuminate\Mail\Mailables\Address($this->recruiterEmail, $this->companyName),
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.candidate_shortlisted',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
