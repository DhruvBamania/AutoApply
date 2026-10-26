<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function show($id)
    {
        $campaign = Campaign::with('leads.emailDrafts')->findOrFail($id);

        $leads = $campaign->leads;
        
        $pendingCount = $leads->where('status', 'pending')->count();
        $draftedCount = $leads->where('status', 'drafted')->count();
        $emailedCount = $leads->where('status', 'emailed')->count();

        return view('campaigns.show', compact(
            'campaign', 'leads', 'pendingCount', 'draftedCount', 'emailedCount'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'job_title_target' => 'required|string|max:255',
        ]);

        $campaign = auth()->user()->campaigns()->create([
            'title' => $request->title,
            'job_title_target' => $request->job_title_target,
            'status' => 'running',
        ]);

        return redirect()->route('campaigns.show', $campaign->id);
    }

    public function generateLeads($id, \App\Services\LeadGenerationService $leadGenService)
    {
        $campaign = Campaign::findOrFail($id);
        
        // This simulates generating leads via proxycurl
        $leadGenService->generateForCampaign($campaign);

        // After generating leads, we can dispatch the AI Job for each new pending lead
        foreach($campaign->leads()->where('status', 'pending')->get() as $lead) {
            \App\Jobs\ProcessLeadEmailJob::dispatch($lead);
        }

        return redirect()->back()->with('success', 'Leads generated and AI drafting started!');
    }

    public function stop($id)
    {
        $campaign = Campaign::findOrFail($id);
        $newStatus = $campaign->status === 'paused' ? 'running' : 'paused';
        $campaign->update(['status' => $newStatus]);

        return redirect()->back()->with('success', "Campaign is now {$newStatus}.");
    }
}
