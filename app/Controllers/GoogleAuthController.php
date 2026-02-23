<?php

namespace App\Controllers;

use Google\Client;

class GoogleAuthController extends BaseController
{
    public function login()
    {
        $client = new Client();
        $client->setClientId(getenv('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(getenv('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(base_url('google/callback'));
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        $client->setScopes([
            'https://www.googleapis.com/auth/drive.file',
            'https://www.googleapis.com/auth/userinfo.email'
        ]);

        return redirect()->to($client->createAuthUrl());
    }

    public function callback()
    {
        $client = new Client();
        $client->setClientId(getenv('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(getenv('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(base_url('google/callback'));

        $token = $client->fetchAccessTokenWithAuthCode(
            $this->request->getGet('code')
        );

        $client->setAccessToken($token);

        $oauth = new \Google\Service\Oauth2($client);
        $user  = $oauth->userinfo->get();

        // Example: attach to logged-in organization
        $orgId = session()->get('org_id');

        $orgModel = new \App\Models\OrganizationModel();
        $orgModel->update($orgId, [
            'gdrive_email'          => $user->email,
            'gdrive_access_token'   => json_encode($token),
            'gdrive_refresh_token'  => $token['refresh_token'] ?? null,
            'gdrive_connected'      => 1
        ]);

        return redirect()->to('/organization/settings')
            ->with('success', 'Google Drive connected successfully');
    }
}
