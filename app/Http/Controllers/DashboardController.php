<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $campaigns = \App\Models\Campaign::where('user_id', auth()->id())->get(); 
        return view('dashboard', compact('campaigns'));
    }

    public function uploadResume(Request $request, \App\Services\ResumeParserService $parser)
    {
        $request->validate([
            'resume' => 'required|file|mimes:pdf|max:10240', // max 10MB
        ]);

        $parser->parseAndSave($request->file('resume'), auth()->user());

        return redirect()->back()->with('success', 'Resume parsed successfully!');
    }
}
