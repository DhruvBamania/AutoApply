<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $accounts = $user->connectedAccounts->keyBy('platform');
        $indeedFeedUrl = $user->isCompany() ? route('feed.indeed', $user->id) : null;

        return view('integrations.index', compact('accounts', 'indeedFeedUrl'));
    }

    public function storeCredentials(Request $request, $platform)
    {
        if (!in_array($platform, ['linkedin', 'naukri', 'indeed'])) {
            return back()->with('error', 'Invalid platform.');
        }

        $connectionType = $request->input('connection_type', 'credentials');

        // Sandbox / Test Recruiter mode (for testing without live paid accounts)
        if ($connectionType === 'sandbox') {
            auth()->user()->connectedAccounts()->updateOrCreate(
                ['platform' => $platform],
                [
                    'connection_type' => 'sandbox',
                    'account_identifier' => ucfirst($platform) . ' Sandbox (Test Recruiter)',
                    'password' => encrypt('sandbox_secret'),
                    'access_token' => encrypt('sandbox_token'),
                ]
            );

            return back()->with('success', ucfirst($platform) . ' Test / Sandbox mode activated! You can now test job postings without any live account.');
        }

        if ($connectionType === 'feed' && $platform === 'indeed') {
            auth()->user()->connectedAccounts()->updateOrCreate(
                ['platform' => 'indeed'],
                [
                    'connection_type' => 'feed',
                    'account_identifier' => 'Indeed ATS XML Feed',
                    'password' => null,
                    'access_token' => null,
                ]
            );

            return back()->with('success', 'Indeed ATS XML Feed connected and activated.');
        }

        if ($connectionType === 'api_key') {
            $request->validate([
                'api_key' => 'required|string',
                'client_id' => 'nullable|string',
            ]);

            auth()->user()->connectedAccounts()->updateOrCreate(
                ['platform' => $platform],
                [
                    'connection_type' => 'api_key',
                    'account_identifier' => $request->input('client_id', ucfirst($platform) . ' API Key'),
                    'access_token' => encrypt($request->api_key),
                    'password' => null,
                ]
            );

            return back()->with('success', ucfirst($platform) . ' API Key connected successfully.');
        }

        // Credentials mode
        $request->validate([
            'account_identifier' => 'required|string',
            'password' => 'required|string',
        ]);

        auth()->user()->connectedAccounts()->updateOrCreate(
            ['platform' => $platform],
            [
                'connection_type' => 'credentials',
                'account_identifier' => $request->account_identifier,
                'password' => encrypt($request->password),
                'access_token' => null,
            ]
        );

        return back()->with('success', ucfirst($platform) . ' employer credentials saved securely.');
    }

    public function disconnect($platform)
    {
        if (!in_array($platform, ['linkedin', 'naukri', 'indeed'])) {
            return back()->with('error', 'Invalid platform.');
        }

        auth()->user()->connectedAccounts()->where('platform', $platform)->delete();

        return back()->with('success', ucfirst($platform) . ' disconnected successfully.');
    }
}
