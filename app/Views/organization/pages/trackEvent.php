<?php
$session = session();
$org_id = $session->get("org_id");
?>

<div class="container">
    <section class="org-page-title-card">
      <div class="org-page-title-content">
        <h1>My Events</h1>
        <p>View the status and approval progress of your submitted events.</p>
      </div>
      <div class="org-page-meta">
        <span class="meta-pill">Track</span>
        <span class="meta-pill">Paginated</span>
      </div>
    </section>

    <section class="org-page-table-card">
      <div class="event-org-table">
      <div class="table-header">
        <h3>Track Your Events</h3>
       <div class="table-filter date-filter">
        <label for="demo" class="date-filter-label">
          <i class="fa-regular fa-calendar-days" aria-hidden="true"></i>
          Date Range
        </label>
        <div class="date-filter-input-wrap">
          <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
          <input id="demo" type="text" name="daterange" placeholder="Select date range" aria-label="Filter events by date range" autocomplete="off" readonly aria-readonly="true" inputmode="none" />
        </div>
        </div>

      </div>

      <table id="myEventTable">
        <thead>
          <th>Event Date</th>
          <th>Date Submitted</th>
          <th>Event Name</th>
          <th>Approval Level</th>
          <th>Event Status</th>
          <th>Actions</th> 
        </thead>
      <tbody id="track-events-tbody" data-id="<?= $org_id ?>"></tbody>
      </table>
    </div>
    </section>
</div>

<!-- Modal is provided globally in app/Views/main.php -->
