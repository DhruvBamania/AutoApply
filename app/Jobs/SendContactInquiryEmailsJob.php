<?php

namespace App\Jobs;

use App\Mail\ContactFormAdminNotificationMail;
use App\Mail\ContactFormUserReceiptMail;
use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendContactInquiryEmailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    protected ContactMessage $contactMessage;

    public function __construct(ContactMessage $contactMessage)
    {
        $this->contactMessage = $contactMessage;
    }

    public function handle(): void
    {
        $adminEmail = config('mail.admin_address') 
            ?: env('ADMIN_EMAIL') 
            ?: config('mail.from.address', 'support@ailooping.com');

        // 1. Dispatch Admin Notification
        try {
            Mail::to($adminEmail)->send(new ContactFormAdminNotificationMail($this->contactMessage));
            Log::info('Contact form admin notification email sent', [
                'ticket_id' => $this->contactMessage->ticket_id,
                'admin_email' => $adminEmail,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed sending contact form admin notification', [
                'ticket_id' => $this->contactMessage->ticket_id,
                'error' => $e->getMessage(),
            ]);
        }

        // 2. Dispatch User Confirmation Receipt
        try {
            if (!empty($this->contactMessage->email)) {
                Mail::to($this->contactMessage->email)->send(new ContactFormUserReceiptMail($this->contactMessage));
                Log::info('Contact form user receipt email sent', [
                    'ticket_id' => $this->contactMessage->ticket_id,
                    'user_email' => $this->contactMessage->email,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Failed sending contact form user receipt email', [
                'ticket_id' => $this->contactMessage->ticket_id,
                'user_email' => $this->contactMessage->email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
