<div class="container admin-dashboard-bento">
  <section class="admin-page-title-card">
    <div class="admin-page-title-content">
      <h1>Dashboard Overview</h1>
      <p>Quick view of queue health, schedules, and approval workload.</p>
    </div>
  </section>

  <div class="bento-grid">
    <section class="bento-card bento-calendar">
      <div class="bento-title-row">
        <div>
          <h2 class="calendar-of-activities-header">Calendar of Activities</h2>
          <p>See event timelines, approvals, and workload at a glance.</p>
        </div>
        <span class="bento-chip">Live</span>
      </div>
      <div id="calendar"></div>
    </section>

    <section class="bento-card bento-stats">
      <h3>Workflow Snapshot</h3>
      <p class="bento-section-note">Live status totals across the approval flow.</p>
      <div class="bento-stats-grid">
        <article class="stat-card stat-pending">
          <h4 id="dashboard-stat-pending">0</h4>
          <p>Pending</p>
        </article>
        <article class="stat-card stat-progress">
          <h4 id="dashboard-stat-progress">0</h4>
          <p>In-Progress</p>
        </article>
        <article class="stat-card stat-awaiting">
          <h4 id="dashboard-stat-awaiting">0</h4>
          <p>Awaiting Docs</p>
        </article>
        <article class="stat-card stat-verification">
          <h4 id="dashboard-stat-verification">0</h4>
          <p>For Verification</p>
        </article>
        <article class="stat-card stat-completed">
          <h4 id="dashboard-stat-completed">0</h4>
          <p>Completed</p>
        </article>
      </div>
    </section>

    <section class="bento-card bento-focus">
      <h3>Current Focus</h3>
      <div class="focus-body">
        <p id="dashboard-focus-name">No upcoming events</p>
        <small id="dashboard-focus-meta">Waiting for event submissions</small>
      </div>
      <button type="button" id="dashboard-focus-open" class="bento-action-btn" data-id="">Open Details</button>
    </section>

    <section class="bento-card bento-upcoming">
      <h3><i class="fa-solid fa-list-check"></i> Activity Feed</h3>
      <div class="bento-activity-groups">
        <div class="activity-group incoming-group">
          <h4><i class="fa-solid fa-calendar-day"></i> Incoming (7 Days)</h4>
          <ul id="dashboard-incoming-list">
            <li class="empty"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</li>
          </ul>
        </div>
        <div class="activity-group pending-group">
          <h4><i class="fa-solid fa-hourglass-half"></i> Pending Approval</h4>
          <ul id="dashboard-pending-list">
            <li class="empty"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</li>
          </ul>
        </div>
      </div>
    </section>
  </div>
</div>
