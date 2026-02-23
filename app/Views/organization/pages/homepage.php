<?php
$session = session();
$org_id = $session->get("org_id");
?>

<div class="container">
    <section class="org-page-title-card">
      <div class="org-page-title-content">
        <h1>Dashboard Overview</h1>
        <p>Track your submissions, rankings, and activity updates in one view.</p>
      </div>
      <div class="org-page-meta">
        <span class="meta-pill">Organization</span>
        <span class="meta-pill">Live Data</span>
      </div>
    </section>

    <section class="org-page-overview-card">
      <div class="cards-and-activity">
      <div class="cards">
        <?= view('organization/components/card', [
          'title' => 'Current Ranking',
          'content' => '-',
          'color' => 'red',
          'content_id' => 'current-ranking',
        ]) ?>
      </div>
      <div class="log-activity">
        <div class="left">
          <h3>Log Your Activity</h3>
          <h5>Organizing, Contribution &
            Attending</h5>
        </div>
        <div class="right">
          <button data-link="organization/host-event">Propose an Event</button>
          <button data-link="organization/log-event">Log an Event</button>
          <button data-link="organization/log-event">Contributed to an Event</button>
        </div>
      </div>
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
          <tr>
            <th>Event Date</th>
            <th>Date Submitted</th>
            <th>Event Name</th>
            <th>Approval Level</th>
            <th>Event Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="track-events-tbody" data-id="<?= $org_id ?>"></tbody>
      </table>
    </div>
  </section>
</div>
