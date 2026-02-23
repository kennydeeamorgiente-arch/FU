<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OrganizationModel;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;


class AdminController extends BaseController
{
  public function login()
  {
    return view('/admin/pages/login');
  }
  public function dashboard()
  {
    // Check if AJAX request - return just content, otherwise return main layout
    if ($this->request->isAJAX()) {
      return view('/admin/pages/dashboard');
    }
    return view('main');
  }
  public function leaderboards()
  {
    if ($this->request->isAJAX()) {
      return view('/admin/pages/leaderboards');
    }
    return view('main');
  }
  public function manageEvents()
  {
    if ($this->request->isAJAX()) {
      return view('/admin/pages/manage-events');
    }
    return view('main');
  }
  public function pendingProposals()
  {
    return view('/admin/pages/pendingProposals');
  }
  public function inProgressEvents()
  {
    return view('/admin/pages/inProgressEvents');
  }
  public function completedEvents()
  {
    return view('/admin/pages/completedEvents');
  }
  public function organizations()
  {
    if ($this->request->isAJAX()) {
      $org = new OrganizationModel();
      $data['orgs'] = $org->getAllOrganizations();
      return view('/admin/pages/organizations', $data);
    }
    return view('main');
  }

  //-----------------------------------------------------------//
  //FOR USER PAGE IN OUR SIDEBAR


  // *just return the view page
  public function users()
  {
    if ($this->request->isAJAX()) {
      return view('/admin/pages/users');
    }
    return view('main');
  }

  // *kwaon ang data sa model nato then e convert ra nato to JSON for ajax
  public function usersAjax()
  {
    $userModel = new \App\Models\UserModel();
    $user_info = $userModel->getAllUserWithDetails();

    return $this->response->setJSON($user_info);
  }

  //-----------------------------------------------------------//



  public function settings()
  {
    if ($this->request->isAJAX()) {
      return view('/admin/pages/settings');
    }
    return view('main');
  }
  public function addOrg($segment, $id = null)
  {

    $valid = ['org-add', 'org-delete', 'org-edit', 'org-view'];

    if (!in_array($segment, $valid)) {
      throw new PageNotFoundException();
    }
    if (!($segment == 'org-add') && $id) {
      $orgModel = new OrganizationModel();
      $org = $orgModel->getOrganizationById($id);
      if (!$org) {
        throw PageNotFoundException::forPageNotFound("Organization Not Found");
      }

      return view('/admin/modals/' . $segment, ['org' => $org]);
    } else {
      return view('/admin/modals/' . $segment);
    }

  }

  public function modifyUser($actions, $id = null)
  {
    $valid = ['user-add', 'user-delete', 'user-edit', 'user-view'];

    if (!in_array($actions, $valid)) {
      throw new PageNotFoundException();
    }
    if (!($actions == 'user-add') && $id) {
      $userModel = new \App\Models\UserModel();
      $user = $userModel->getUserByIdWithDetails($id);
      if (!$user) {
        throw PageNotFoundException::forPageNotFound("User Not Found");
      }

      // Get access levels and organizations for dropdowns
      $accessLevelModel = new \App\Models\AccessLevelModel();
      $organizationModel = new OrganizationModel();

      // Get active organizations (excluding soft deleted) with consistent field name
      $organizations = $organizationModel->select('org_id, Org_Name as org_name')->findAll();

      $data = [
        'user' => $user,
        'accessLevels' => $accessLevelModel->getAllAccessLevels(),
        'organizations' => $organizations
      ];

      return view('/admin/modals/' . $actions, $data);
    } else {
      // For user-add, we still need access levels and organizations
      $accessLevelModel = new \App\Models\AccessLevelModel();
      $organizationModel = new OrganizationModel();

      // Get active organizations (excluding soft deleted)
      $organizations = $organizationModel->select('org_id, Org_Name as org_name')->findAll();

      $data = [
        'accessLevels' => $accessLevelModel->getAllAccessLevels(),
        'organizations' => $organizations
      ];

      return view('/admin/modals/' . $actions, $data);
    }
  }

  public function updateUser()
  {
    $userModel = new \App\Models\UserModel();

    $user_id = $this->request->getPost('user-id');
    $email = $this->request->getPost('user-email');
    $position = $this->request->getPost('user-position');
    $access_id = $this->request->getPost('user-access-level');
    $org_id = $this->request->getPost('user-organization');

    if (!$user_id) {
      return $this->response->setJSON(['status' => 'error', 'message' => 'User ID is required']);
    }

    $success = $userModel->updateUser($user_id, $email, $position, $access_id, $org_id);

    if ($success) {
      return $this->response->setJSON(['status' => 'success', 'message' => 'User updated successfully']);
    } else {
      return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update user']);
    }
  }

  public function deleteUser()
  {
    $userModel = new \App\Models\UserModel();

    $user_id = $this->request->getPost('user-id');

    if (!$user_id) {
      return $this->response->setJSON(['status' => 'error', 'message' => 'User ID is required']);
    }

    $deleted = $userModel->delete($user_id);

    if ($deleted) {
      return $this->response->setJSON([
        'status' => 'success',
        'message' => 'User deleted successfully'
      ]);
    } else {
      return $this->response->setJSON([
        'status' => 'error',
        'message' => 'Failed to delete user'
      ]);
    }
  }

  public function createUser()
  {
    $userModel = new UserModel();
    log_message('debug', print_r($this->request->getPost(), true));
    $email = trim($this->request->getPost('user-email'));
    $password = trim($this->request->getPost('user-password'));
    $position = trim($this->request->getPost('user-position'));
    $access_id = $this->request->getPost('user-access-level');
    $org_id = $this->request->getPost('user-organization');
    
    // Convert empty string to null for org_id
    if ($org_id === '' || $org_id === null) {
      $org_id = null;
    } else {
      $org_id = (int)$org_id;
    }

    // Log for debugging
    log_message('debug', "Email: $email");
    log_message('debug', "Access: $access_id");
    log_message('debug', "Org ID: " . ($org_id ?? 'null'));

    // Validation
    if (empty($email) || empty($password) || empty($position) || $access_id === null || $access_id === '') {
      return $this->response->setJSON([
        'status' => 'error',
        'message' => 'Email, password, position, and access level are required.'
      ]);
    }

    // Check duplicate email (excluding soft deleted)
    $existingUser = $userModel->where('email', $email)->first();
    if ($existingUser) {
      return $this->response->setJSON([
        'status' => 'error',
        'message' => 'Email already exists.'
      ]);
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Data
    $data = [
      'email' => $email,
      'password' => $hashedPassword,
      'position' => $position,
      'access_id' => (int)$access_id,
      'org_id' => $org_id
    ];

    // Insert user using CI4's insert() method
    try {
      $insertedId = $userModel->insert($data);
      if ($insertedId) {
        return $this->response->setJSON([
          'status' => 'success',
          'message' => 'User created successfully.',
          'user_id' => $insertedId
        ]);
      } else {
        // Get validation errors if any
        $errors = $userModel->errors();
        $errorMessage = !empty($errors) ? implode(', ', $errors) : 'Failed to create user.';
        
        log_message('error', 'User creation failed: ' . print_r($errors, true));
        return $this->response->setJSON([
          'status' => 'error',
          'message' => $errorMessage
        ]);
      }
    } catch (\Exception $e) {
      log_message('error', 'Exception creating user: ' . $e->getMessage());
      return $this->response->setJSON([
        'status' => 'error',
        'message' => 'Failed to create user: ' . $e->getMessage()
      ]);
    }
  }

}
?>