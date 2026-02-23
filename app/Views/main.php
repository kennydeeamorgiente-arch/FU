<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FU EMS</title>

  <?php
  $session = session();
  $user_email = $session->get("email");
  $user_id = $session->get("user_id");
  $accessId = $session->get('access_id');
  ?>

  <!-- All CSS Files -->
  <link rel="stylesheet" href="<?= base_url('css/theme/theme.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/admin/main.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/organization/main.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/card.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/calendar.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/leaderboard.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/event-data.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/organization/homepage.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/organization/events.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/admin/organization.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/admin/manage-events.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/admin/events-view.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/admin/users.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/data-table.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/org-global.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/organization/profile.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/organization/responsive.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/layout-consistency.css') ?>">

  <!-- External CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/searchpanes/2.2.0/css/searchPanes.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/select/1.7.0/css/select.dataTables.min.css">
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

  <!-- JavaScript Libraries -->

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/searchpanes/2.2.1/js/dataTables.searchPanes.min.js"></script>
  <script src="https://cdn.datatables.net/select/1.7.0/js/dataTables.select.min.js"></script>

  <!-- Global Configuration (must be loaded before other scripts) -->
  <script src="<?= base_url('js/config.js') ?>"></script>
</head>

<body>
  <!-- Sidebar Overlay (for mobile) -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <div class="wrapper">
    <!-- Sidebar -->
    <?= $this->include('components/sidebar') ?>

    <!-- Main Area -->
    <div class="main-content">
      <?= $this->include('components/header') ?>

      <!-- Dynamic Content Wrapper -->
      <div id="content-wrapper" class="container">
        <!-- Content will be loaded here via AJAX -->
        <div style="text-align: center; padding: 50px;">
          <i class="fa-solid fa-spinner fa-spin fa-2x"></i>
          <p>Loading...</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for Actions (if needed) -->
  <div id="orgModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 id="modalTitle">Action</h3>
        <span class="modal-close">&times;</span>
      </div>
      <div class="modal-body" id="modalBody">
        <!-- Modal content will be loaded here -->
      </div>
    </div>
  </div>

  <!-- 2. jQuery UI (REQUIRED for autocomplete) -->
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
  <!-- Admin Main JS (for notifications) -->
  <script src="<?= base_url('js/admin/main.js') ?>"></script>

  <!-- Admin Events JS (for event approval buttons) -->
  <script src="<?= base_url('js/admin/events.js') ?>"></script>

  <!-- Sidebar Toggle Script -->
  <script src="<?= base_url('js/components/sidebar.js') ?>"></script>

  <!-- Navigation Script -->
  <script src="<?= base_url('js/components/navigation.js') ?>"></script>


</body>

</html>
