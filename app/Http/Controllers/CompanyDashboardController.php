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
}
