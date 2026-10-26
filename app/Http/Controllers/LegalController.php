<?php

namespace App\Http\Controllers;

class LegalController extends Controller
{
    /**
     * Display Terms of Service.
     */
    public function terms()
    {
        return view('pages.legal.terms', [
            'lastUpdated' => 'October 26, 2026',
            'effectiveDate' => 'October 26, 2026',
        ]);
    }

    /**
     * Display Privacy Policy.
     */
    public function privacy()
    {
        return view('pages.legal.privacy', [
            'lastUpdated' => 'October 26, 2026',
            'effectiveDate' => 'October 26, 2026',
        ]);
    }

    /**
     * Display Cookie Policy.
     */
    public function cookies()
    {
        return view('pages.legal.cookies', [
            'lastUpdated' => 'October 26, 2026',
            'effectiveDate' => 'October 26, 2026',
        ]);
    }
}
