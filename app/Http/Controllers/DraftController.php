<?php

namespace App\Http\Controllers;

use App\Models\EmailDraft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class DraftController extends Controller
{
    /**
     * Updates the body of an email draft manually.
     */
    public function update(Request $request, $id)
    {
        $request->validate(['body' => 'required|string']);
        
        $draft = EmailDraft::findOrFail($id);
        $draft->update(['body' => $request->body]);
        
        return back()->with('success', 'Draft manually updated.');
    }

    /**
     * Approves the draft and dispatches the email.
     */
    public function send($id)
    {
        $draft = EmailDraft::with('lead')->findOrFail($id);
        $user = auth()->user();
        
        // Send the actual email
        Mail::raw($draft->body, function ($message) use ($draft, $user) {
            $message->to($draft->lead->founder_email)
                    ->from($user->email, $user->name)
                    ->replyTo($user->email, $user->name)
                    ->subject($draft->subject);
        });

        // Mark as approved and emailed
        $draft->update(['is_approved' => true]);
        $draft->lead->update(['status' => 'emailed']);

        return back()->with('success', 'Email approved and sent!');
    }
}
