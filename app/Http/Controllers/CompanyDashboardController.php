<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $company = $user->companyProfile;
        $jobsCount = $user->jobPostings()->count();

        return view('company.dashboard', compact('user', 'company', 'jobsCount'));
    }

    public function updateWhatsApp(Request $request)
    {
        $user = $request->user() ?: Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }
        $request->validate([
            'whatsapp_number' => 'nullable|string|max:25',
        ]);

        $profile = $user->companyProfile ?: new \App\Models\CompanyProfile(['user_id' => $user->id]);
        $profile->whatsapp_number = $request->whatsapp_number;
        $profile->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'WhatsApp contact number updated successfully.',
                'company' => $profile,
            ]);
        }

        return back()->with('success', 'WhatsApp contact number updated successfully.');
    }
}
