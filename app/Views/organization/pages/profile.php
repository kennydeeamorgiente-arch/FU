<div class="container">
  <section class="org-page-title-card">
    <div class="org-page-title-content">
      <h1>Organization Profile</h1>
      <p>View your organization details, ranking, and engagement summary.</p>
    </div>
    <div class="org-page-meta">
      <span class="meta-pill">Profile</span>
      <span class="meta-pill">Read Only</span>
    </div>
  </section>

  <div class="profile-header-cards">
    <div class="profile-header">
      <div class="profile-logo">
        <img src="<?= base_url("uploads/org-logos/{$org['logo']}") ?>" alt="<?= esc($org['org_name']) ?>">
      </div>
      <div class="profile-name">
        <h1><?= esc($org['org_name']) ?></h1>
      </div>
    </div>
    <?= view('organization/components/card', [
      'title' => 'Ranking',
      'content' => $org['org_rank'] ?? '-',
      'color' => 'red',
    ]) ?>
    <?= view('organization/components/card', [
      'title' => 'Total Points',
      'content' => $org['total_points'] ?? 0,
      'color' => 'red',
    ]) ?>
    <?= view('organization/components/card', [
      'title' => 'Total Events',
      'content' => $org['num_events'] ?? 0,
      'color' => 'red',
    ]) ?>
  </div>

  <section class="org-page-table-card">
    <div class="event-org-table">
      <div class="table-header">
        <h3>Basic Information</h3>
      </div>
      <div class="org-info-container">
        <div class="org-info-item">
          <h5>Organization Name</h5>
          <p><?= esc($org['org_name']) ?></p>
        </div>
        <div class="org-info-item">
          <h5># of Members</h5>
          <p><?= esc($org['org_num_members'] ?? 'N/A') ?></p>
        </div>
        <div class="org-info-item full-width">
          <h5>Organization Overview</h5>
          <p><?= esc($org['description'] ?? 'No description available.') ?></p>
        </div>
      </div>
    </div>
  </section>
</div>
