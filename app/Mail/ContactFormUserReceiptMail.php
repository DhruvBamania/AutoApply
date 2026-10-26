<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactFormUserReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public ContactMessage $contactMessage;

    public function __construct(ContactMessage $contactMessage)
    {
        $this->contactMessage = $contactMessage;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "We received your inquiry [{$this->contactMessage->ticket_id}] - AutoApply & AI Looping Support",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact_user_receipt',
            with: [
                'msg' => $this->contactMessage,
            ],
        );
    }
}
