<?php $current = uri_string();

$session = session();
$org_id = $session->get("org_id")
  ?>

<header>
  <div class="header-left">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
      <i class="fa-solid fa-bars"></i>
    </button>
    <div class="title">
      <h2>FU SOMS</h2>
    </div>
  </div>
  <nav>
    <ul>
      <li>
        <a href="<?= base_url('organization/homepage') ?>"
          class="<?= $current == 'organization/homepage' ? 'active' : '' ?>">Home</a>
      </li>
      <li>
        <a href="<?= base_url('organization/leaderboards') ?>"
          class="<?= $current == 'organization/leaderboards' ? 'active' : '' ?>">Leaderboard</a>
      </li>
      <li>
        <a href="<?= base_url('organization/track-event') ?>"
          class="<?= $current == 'organization/track-event' ? 'active' : '' ?>">My Events</a>
      </li>
      <li>
        <a href="<?= base_url('organization/host-event') ?>"
          class="<?= $current == 'organization/host-event' ? 'active' : '' ?>">Host an Event</a>
      </li>
      <li>
        <a href="<?= base_url('organization/log-event') ?>"
          class="<?= $current == 'organization/log-event' ? 'active' : '' ?>">Log an Event</a>
      </li>
    </ul>
  </nav>
  <div class="profile-settings">
    <?php $user_id = $session->get("user_id"); ?>
    <div class="notification-icon" onclick="toggleNotificationDropdown(event)" style="margin-right: 15px; cursor: pointer; position: relative; display: inline-block;">
      <i class="fa-solid fa-bell fa-xl"></i>
      <span class="notification-badge" style="display: none; position: absolute; top: -5px; right: -5px; background: #ff0000; color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 11px; font-weight: bold; line-height: 18px; text-align: center;">0</span>
    </div>
    <i class="fa-solid fa-user fa-xl" id="user" style="cursor: pointer;"></i>
  </div>
  
  <div class="notification-container hide" id="notificationDropdown" data-user-id="<?= $user_id ?>" style="position: absolute; top: 60px; right: 20px; background: white; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); width: 350px; max-height: 450px; overflow: hidden; z-index: 1000;">
    <div class="notification-header" style="padding: 15px; border-bottom: 1px solid #eee; background-color: #f8f9fa; position: sticky; top: 0;">
      <h4 style="margin: 0; color: #333; font-size: 16px;">Notifications</h4>
    </div>
    <div class="notification-list" id="notificationList" style="max-height: 380px; overflow-y: auto; padding: 0;">
      <div class="notification-item" style="padding: 15px; text-align: center; color: #999;">
        <p style="margin: 0;">Loading notifications...</p>
      </div>
    </div>
  </div>
  
  <div class="profile-container hide">
    <div class="img-box">
      <img src="<?= base_url('img/foundationu_logo.png') ?>" alt="Foundation University Logo">
    </div>
    <h4><a href="#" data-org="<?= $org_id ?>">
        <p>Profile</p>
      </a></h4>
    <h4><a href="#">Log out</a></h4>
  </div>
</header>

<!-- Scripts are loaded in the main layout to avoid duplicate bindings -->