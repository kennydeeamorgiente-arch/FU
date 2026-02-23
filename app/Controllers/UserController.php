<?php
namespace App\Controllers;

use App\Models\UserModel;


class UserController extends BaseController
{
    public function index()
    {
        $session = session();
        $user_id = $session->get('user_id');

        // Redirect to login if user is not logged in
        if (empty($user_id)) {
            return redirect()->to('/login');
        }

        // Return main layout - navigation.js will load the appropriate page
        return view('main');
    }

    public function login()
    {
        return view('login');
    }

    public function verifyLogin()
    {
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $session = session();
        $userModel = new UserModel();

        $user = $userModel->getUserByEmail($email);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Email not found.'
            ]);
        }

        if (!password_verify($password, $user['password'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid password.'
            ]);
        }

        // ✅ success
        $session->set([
            'user_id' => $user['user_id'],
            'email' => $user['email'],
            'access_id' => $user['access_id'],
            'org_id' => $user['org_id'],
            'access_name' => $user['access_name'] ?? null,
            'logged_in' => true
        ]);

        if ($user['access_id'] == 0) {
            return $this->response->setJSON([
                'success' => true,
                'redirect' => base_url('/organization/homepage')
            ]);
        } else {
            return $this->response->setJSON([
                'success' => true,
                'redirect' => base_url('/admin/dashboard')
            ]);
        }

    }



    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('/'));//balik login natural
    }
}

?>