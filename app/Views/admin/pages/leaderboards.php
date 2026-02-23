<div class="container">
  <section class="admin-page-title-card">
    <div class="admin-page-title-content">
      <h1>Leaderboards</h1>
      <p>View current organization rankings and total points by period.</p>
      <div class="admin-page-meta">
        <span class="meta-pill">Ranking</span>
        <span class="meta-pill">Paginated</span>
      </div>
    </div>
  </section>

  <section class="admin-page-toolbar-card">
    <div class="admin-toolbar-grid admin-leaderboard-toolbar-grid">
      <div class="admin-toolbar-input">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="admin-leaderboard-search-input" placeholder="Search organization or type">
      </div>
      <select id="admin-leaderboard-filter" class="admin-toolbar-select leaderboard-filter-select" aria-label="Filter leaderboard period">
        <option value="annually" selected>Annually</option>
        <option value="1st_semester">1st Semester</option>
        <option value="2nd_semester">2nd Semester</option>
      </select>
      <button type="button" id="admin-leaderboard-clear-btn" class="admin-toolbar-btn subtle">Clear</button>
    </div>
  </section>

  <section class="admin-page-table-card">
    <div class="table-header">
      <h3>Organization Leaderboard</h3>
      <div class="table-actions">
        <span class="table-hint">Click row to open organization details</span>
      </div>
    </div>

    <table class="org-table" id="admin-leaderboard-table">
      <thead>
        <tr>
          <th>Rank</th>
          <th>Organization Name</th>
          <th>Organization Type</th>
          <th>Points</th>
        </tr>
      </thead>
      <tbody id="admin-leaderboard-body">
      </tbody>
    </table>
  </section>
</div>
