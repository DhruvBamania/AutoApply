<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;

class LinkedInAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('linkedin-openid')
            ->scopes(['openid', 'profile', 'email', 'w_member_social'])
            ->redirect();
    }

    public function callback()
    {
        $redirectRoute = auth()->check() && auth()->user()->isCompany()
            ? 'company.integrations'
            : 'integrations.index';

        try {
            $linkedInUser = Socialite::driver('linkedin-openid')->user();

            auth()->user()->connectedAccounts()->updateOrCreate(
                ['platform' => 'linkedin'],
                [
                    'connection_type' => 'oauth',
                    'account_identifier' => $linkedInUser->getEmail() ?? $linkedInUser->getName() ?? $linkedInUser->getId(),
                    'access_token' => encrypt($linkedInUser->token),
                    'refresh_token' => $linkedInUser->refreshToken ? encrypt($linkedInUser->refreshToken) : null,
                ]
            );

            return redirect()->route($redirectRoute)->with('success', 'LinkedIn account connected securely.');
        } catch (\Exception $e) {
            return redirect()->route($redirectRoute)->with('error', 'Failed to connect LinkedIn: ' . $e->getMessage());
        }
    }
}
