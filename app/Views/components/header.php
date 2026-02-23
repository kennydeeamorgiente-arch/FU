<?php 
$session = session();
$user_email = $session->get("email");
$user_id = $session->get("user_id");
$accessId = $session->get('access_id');
$org_id = $session->get('org_id');
$userLabel = $user_email ?? 'User';
$userInitial = strtoupper(substr($userLabel, 0, 1));
$userRoleLabel = $accessId == 0 ? 'Organization Account' : 'Administrator';
?>

<!-- Header Component -->
<header class="admin-header">
  <div class="header-left">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
      <i class="fa-solid fa-bars"></i>
    </button>
    <h3 id="pageHeader">Dashboard</h3>
  </div>
  
  <div class="header-icons">
    <div class="notification-icon" onclick="toggleNotificationDropdown(event)">
      <i class="fa-solid fa-bell"></i>
      <span class="notification-badge" style="display: none;">0</span>
    </div>
    <div class="user-icon profile-trigger" onclick="toggleUserDropdown(event)">
      <span class="profile-avatar"><?= esc($userInitial) ?></span>
      <span class="profile-meta">
        <span class="profile-name"><?= esc($userLabel) ?></span>
        <span class="profile-role"><?= esc($userRoleLabel) ?></span>
      </span>
      <i class="fa-solid fa-chevron-down profile-caret"></i>
    </div>
  </div>
  
  <div class="notification-container hide" id="notificationDropdown" data-user-id="<?= $user_id ?>">
    <div class="notification-header">
      <h4>Notifications</h4>
    </div>
    <div class="notification-list" id="notificationList">
      <div class="notification-item">
        <p>Loading notifications...</p>
      </div>
    </div>
  </div>
  
  <div class="user-dropdown hide" id="userDropdown">
    <div class="user-header">
      <div class="user-header-row">
        <div class="user-avatar-large"><?= esc($userInitial) ?></div>
        <div class="user-details">
          <h4><?= esc($userLabel) ?></h4>
          <p><?= esc($userRoleLabel) ?></p>
        </div>
      </div>
    </div>
    <div class="profile-menu">
      <?php if ($accessId == 0 && !empty($org_id)): ?>
        <div class="menu-item profile-link" data-link="organization/profile/<?= esc($org_id) ?>" data-org-id="<?= esc($org_id) ?>" onclick="navigateToProfile(this)">
          <i class="fa-solid fa-user"></i>
          <span>Profile</span>
        </div>
      <?php endif; ?>
      <div class="menu-item" data-logout-url="<?= base_url('user/logout') ?>" onclick="handleLogout(this)">
        <i class="fa-solid fa-right-from-bracket"></i>
        <span>Log out</span>
      </div>
    </div>
  </div>
</header>

