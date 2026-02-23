<div class="container">
    <section class="org-page-title-card">
      <div class="org-page-title-content">
        <h1>Leaderboards</h1>
        <p>Review organization rankings across selected academic periods.</p>
      </div>
      <div class="org-page-meta">
        <span class="meta-pill">Ranking</span>
        <span class="meta-pill">Updated Live</span>
      </div>
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
    <section class="org-page-table-card">
      <div class="event-org-table">
      <div class="table-header">
        <h3>Leaderboard</h3>
        <div class="table-filter">
          <select id="leaderboard-filter" class="leaderboard-filter-select" aria-label="Filter leaderboard period">
            <option value="annually" selected>Annually</option>
            <option value="1st_semester">1st Semester</option>
            <option value="2nd_semester">2nd Semester</option>
          </select>
        </div>
      </div>

      <table id="org-leaderboard-table">
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
