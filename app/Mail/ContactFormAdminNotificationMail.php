<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactFormAdminNotificationMail extends Mailable
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
            subject: "[{$this->contactMessage->ticket_id}] New {$this->contactMessage->category_label}: {$this->contactMessage->subject}",
            replyTo: [
                new \Illuminate\Mail\Mailables\Address($this->contactMessage->email, $this->contactMessage->name)
            ]
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact_admin_notification',
            with: [
                'msg' => $this->contactMessage,
            ],
        );
    }
}
