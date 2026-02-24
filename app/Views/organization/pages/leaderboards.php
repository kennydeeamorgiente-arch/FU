<div class="container">
    <section class="org-page-title-card">
      <div class="org-page-title-content">
        <h1>Leaderboards</h1>
        <p>Review organization rankings across selected academic periods.</p>
    </section>

    <!-- Organization Header with Logo, Name, and Ranking -->
    <div class="leaderboard-header-section">
      <div class="leaderboard-org-info">
        <div class="leaderboard-org-logo">
          <?php if (isset($org) && $org && !empty($org['logo'])): ?>
            <img src="<?= base_url("uploads/org-logos/{$org['logo']}") ?>" alt="<?= esc($org['org_name'] ?? 'Organization') ?>">
          <?php else: ?>
            <div class="leaderboard-org-placeholder">
              <i class="fa-solid fa-building"></i>
            </div>
          <?php endif; ?>
        </div>
        <div class="leaderboard-org-name">
          <h2><?= esc($org['org_name'] ?? 'Organization') ?></h2>
        </div>
      </div>
      <div class="leaderboard-ranking-card">
        <?= view('organization/components/card', [
          'title' => 'Current Ranking',
          'content' => '-',
          'color' => 'red',
          'content_id' => 'leaderboard-current-ranking',
        ]) ?>
      </div>
    </div>
    <section class="admin-page-toolbar-card">
      <div class="admin-toolbar-grid admin-leaderboard-toolbar-grid">
        <div class="admin-toolbar-input">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" id="org-leaderboard-search-input" placeholder="Search organization or type">
        </div>
        <select id="leaderboard-filter" class="admin-toolbar-select leaderboard-filter-select" aria-label="Filter leaderboard period">
          <option value="annually" selected>Annually</option>
          <option value="1st_semester">1st Semester</option>
          <option value="2nd_semester">2nd Semester</option>
        </select>
        <button type="button" id="org-leaderboard-clear-btn" class="admin-toolbar-btn subtle">Clear</button>
      </div>
    </section>

    <section class="admin-page-table-card org-page-table-card">
      <div class="event-org-table">
      <div class="table-header">
        <h3>Leaderboard</h3>
        <div class="table-actions">
          <span class="table-hint">Click row to open organization details</span>
        </div>
      </div>

      <table id="org-leaderboard-table" class="org-table clickable-rows">
        <thead>
          <tr>
            <th>Rank</th>
            <th>Organization Name</th>
            <th>Organization Type</th>
            <th>Points</th>
          </tr>
        </thead>
        <tbody id="org-leaderboard-body">
        </tbody>
      </table>
    </div>
    </section>
</div>
