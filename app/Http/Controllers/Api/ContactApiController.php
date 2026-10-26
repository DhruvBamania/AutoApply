<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendContactInquiryEmailsJob;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactApiController extends Controller
{
    /**
     * Submit contact form inquiry from mobile app or public API.
     */
    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:30',
            'category' => 'nullable|string|in:general,candidate_support,company_inquiry,enterprise_sales,bug_report,legal',
            'subject' => 'required|string|min:4|max:200',
            'message' => 'required|string|min:10|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = $request->user()?->id;

        $contactMessage = ContactMessage::create([
            'user_id' => $userId,
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'category' => $request->input('category', 'general'),
            'subject' => $request->input('subject'),
            'message' => $request->input('message'),
            'status' => 'unread',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string)$request->userAgent(), 0, 500),
        ]);

        // Dispatch background queued emails
        SendContactInquiryEmailsJob::dispatchAfterResponse($contactMessage);

        return response()->json([
            'success' => true,
            'message' => "Your inquiry has been logged as Ticket #{$contactMessage->ticket_id}. Our team has received your message.",
            'ticket_id' => $contactMessage->ticket_id,
            'data' => [
                'id' => $contactMessage->id,
                'ticket_id' => $contactMessage->ticket_id,
                'name' => $contactMessage->name,
                'email' => $contactMessage->email,
                'category' => $contactMessage->category,
                'category_label' => $contactMessage->category_label,
                'subject' => $contactMessage->subject,
                'status' => $contactMessage->status,
                'created_at' => $contactMessage->created_at->toIso8601String(),
            ],
        ], 201);
    }
}
