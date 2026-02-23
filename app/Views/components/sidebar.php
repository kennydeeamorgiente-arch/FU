<?php
$session = session();
$accessId = $session->get('access_id') ?? 0;
// access_id > 0 = Admin, access_id == 0 = Organization
?>
<aside class="sidebar" data-access-id="<?= $accessId ?>">
  <div>
    <div class="sidebar-header">
      <div class="logo"> <img src="<?= base_url('img/placeholder_me.png') ?>" alt=" "> FU SOEMS</div>
      <button class="sidebar-close" id="sidebarClose" aria-label="Close sidebar">
        <i class="fa-solid fa-times"></i>
      </button>
    </div>
    <ul>
      <!-- Common items for all users -->
      <li><a href="#" data-link="dashboard" class="nav-link"><i class="fa-solid fa-house"></i> <?= $accessId == 0 ? 'Home' : 'Dashboard' ?></a></li>
      <li><a href="#" data-link="leaderboards" class="nav-link"><i class="fa-solid fa-ranking-star"></i> Leaderboards</a></li>

      <?php if ($accessId == 0): ?>
        <!-- Organization user items (access_id == 0) -->
        <li><a href="#" data-link="organization/track-event" class="nav-link"><i class="fa-solid fa-calendar-check"></i> My Events</a></li>
        <li><a href="#" data-link="organization/host-event" class="nav-link"><i class="fa-solid fa-calendar-plus"></i> Host Event</a></li>
        <li><a href="#" data-link="organization/log-event" class="nav-link"><i class="fa-solid fa-file-lines"></i> Log an Activity</a></li>
      <?php else: ?>
        <!-- Admin user items (access_id > 0) -->
        <li><a href="#" data-link="admin/manage-events" class="nav-link"><i class="fa-solid fa-calendar-check"></i> Manage Events</a></li>
        <li><a href="#" data-link="admin/organizations" class="nav-link"><i class="fa fa-users"></i> Organizations</a></li>
        <li><a href="#" data-link="admin/users" class="nav-link"><i class="fa-solid fa-people-group"></i> Users</a></li>
      <?php endif; ?>
    </ul>
  </div>
</aside>
