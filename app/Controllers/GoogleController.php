<?php
namespace App\Controllers;

use Google\Client;
use App\Models\UserModel;

class GoogleController extends BaseController
{
    private function getClient()
    {
        $client = new Client();
        $client->setClientId(getenv('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(getenv('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(base_url('google/callback'));
        $client->addScope('email');
        $client->addScope('profile');
        return $client;
    }

    public function login()
    {
        $client = $this->getClient();
        return redirect()->to($client->createAuthUrl());
    }

    public function callback()
    {
        try {
            $client = $this->getClient();
            $code = $this->request->getVar('code');
            
            if (!$code) {
                return redirect()->to('/login')->with('error', 'Google authentication failed: No authorization code received.');
            }

            $token = $client->fetchAccessTokenWithAuthCode($code);

            if (isset($token['error'])) {
                return redirect()->to('/login')->with('error', 'Google login failed: ' . ($token['error_description'] ?? 'Unknown error'));
            }

            if (!isset($token['access_token'])) {
                return redirect()->to('/login')->with('error', 'Google login failed: No access token received.');
            }

            $client->setAccessToken($token['access_token']);
            $service = new \Google\Service\Oauth2($client);
            $googleUser = $service->userinfo->get();

            $email = $googleUser->email;
            $firstName = $googleUser->givenName ?? '';
            $lastName = $googleUser->familyName ?? '';

            $userModel = new UserModel();
            $user = $userModel->getUserByEmail($email);

            if (!$user) {
                return redirect()->to('/login')->with('error', 'Your Google account is not registered. Please contact administrator.');
            }

            // Login success — set session
            session()->set([
                'user_id' => $user['user_id'],
                'email' => $user['email'],
                'access_id' => $user['access_id'],
                'org_id' => $user['org_id'],
                'access_name' => $user['access_name'] ?? null,
                'logged_in' => true
            ]);

            // Redirect to dashboard based on access level
            if ($user['access_id'] == 0) {
                return redirect()->to('/organization/homepage');
            } else {
                return redirect()->to('/admin/dashboard');
            }
        } catch (\Exception $e) {
            log_message('error', 'Google callback error: ' . $e->getMessage());
            return redirect()->to('/login')->with('error', 'An error occurred during Google login. Please try again.');
        }
    }
}
