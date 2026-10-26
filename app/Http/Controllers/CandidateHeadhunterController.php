<?php

namespace App\Http\Controllers;

use App\Models\HeadhunterMatch;
use App\Services\AiHeadhunterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CandidateHeadhunterController extends Controller
{
    protected AiHeadhunterService $headhunterService;

    public function __construct(AiHeadhunterService $headhunterService)
    {
        $this->headhunterService = $headhunterService;
    }

    /**
     * Display scouted invitations received by candidate.
     */
    public function index()
    {
        $user = Auth::user();

        $invitations = HeadhunterMatch::where(function ($q) use ($user) {
            $q->where('candidate_user_id', $user->id)
              ->orWhere('candidate_email', $user->email);
        })
        ->whereIn('status', ['invited', 'accepted', 'declined'])
        ->with('jobPosting.user.companyProfile')
        ->latest('invited_at')
        ->get();

        return view('candidate.headhunter.index', compact('invitations'));
    }

    /**
     * Candidate accepts or declines headhunter invitation.
     */
    public function respond(Request $request, $id)
    {
        $user = Auth::user();

        $match = HeadhunterMatch::where(function ($q) use ($user) {
            $q->where('candidate_user_id', $user->id)
              ->orWhere('candidate_email', $user->email);
        })->with('jobPosting.user.companyProfile')->findOrFail($id);

        $request->validate([
            'action' => 'required|in:accepted,declined',
            'note' => 'nullable|string|max:1000',
        ]);

        $this->headhunterService->respondToInvitation(
            $match,
            $request->action,
            $request->input('note')
        );

        $companyName = $match->jobPosting->user->companyProfile->company_name ?? 'the employer';

        if ($request->action === 'accepted') {
            return back()->with('success', "🎉 You accepted the invitation for '{$match->jobPosting->title}'! You are now connected with {$companyName} and fast-tracked into their shortlist.");
        }

        return back()->with('info', "You declined the invitation for '{$match->jobPosting->title}'. We will inform {$companyName}.");
    }
}
