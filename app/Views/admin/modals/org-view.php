<div class="org-view-container">
  <!-- Back Button -->
  <button class="back-to-org" onclick="history.back()">
    <i class="fa-solid fa-arrow-left"></i>
    <span>Back</span>
  </button>

  <!-- Organization Header -->
  <div class="org-view-header">
    <div class="org-view-logo">
      <img src="<?= base_url("uploads/org-logos/{$org['logo']}") ?>" alt="<?= esc($org['org_name']) ?> Logo">
    </div>
    <div class="org-view-name">
      <h1><?= esc($org['org_name']) ?></h1>
      <p class="org-type"><?= esc($org['org_type_name'] ?? 'Organization') ?></p>
    </div>
  </div>

  <!-- Stats Cards -->
  <div class="org-view-stats">
    <div class="org-stat-card">
      <div class="stat-icon ranking">
        <i class="fa-solid fa-trophy"></i>
      </div>
      <div class="stat-content">
        <h3>Ranking</h3>
        <p class="stat-value"><?= $org['org_rank'] ?? '-' ?></p>
      </div>
    </div>
    <div class="org-stat-card">
      <div class="stat-icon points">
        <i class="fa-solid fa-star"></i>
      </div>
      <div class="stat-content">
        <h3>Total Points</h3>
        <p class="stat-value"><?= number_format($org['total_points'] ?? 0) ?></p>
      </div>
    </div>
    <div class="org-stat-card">
      <div class="stat-icon events">
        <i class="fa-solid fa-calendar-check"></i>
      </div>
      <div class="stat-content">
        <h3>Total Events</h3>
        <p class="stat-value"><?= $org['num_events'] ?? 0 ?></p>
      </div>
    </div>
  </div>

  <!-- Basic Information Section -->
  <div class="org-view-info">
    <div class="info-header">
      <h2>Basic Information</h2>
    </div>
    <div class="info-grid">
      <div class="info-item">
        <div class="info-label">
          <i class="fa-solid fa-building"></i>
          <span>Organization Name</span>
        </div>
        <div class="info-value"><?= esc($org['org_name']) ?></div>
      </div>
      <div class="info-item">
        <div class="info-label">
          <i class="fa-solid fa-users"></i>
          <span># of Members</span>
        </div>
        <div class="info-value"><?= esc($org['org_num_members'] ?? 'N/A') ?></div>
      </div>
      <?php if (!empty($org['adviser'])): ?>
      <div class="info-item">
        <div class="info-label">
          <i class="fa-solid fa-user-tie"></i>
          <span>Adviser</span>
        </div>
        <div class="info-value"><?= esc($org['adviser']) ?></div>
      </div>
      <?php endif; ?>
      <?php if (!empty($org['facebook_link'])): ?>
      <div class="info-item">
        <div class="info-label">
          <i class="fa-brands fa-facebook"></i>
          <span>Facebook Page</span>
        </div>
        <div class="info-value">
          <a href="<?= esc($org['facebook_link']) ?>" target="_blank" rel="noopener noreferrer">
            <?= esc($org['facebook_link']) ?>
          </a>
        </div>
      </div>
      <?php endif; ?>
      <div class="info-item full-width">
        <div class="info-label">
          <i class="fa-solid fa-file-lines"></i>
          <span>Organization Overview</span>
        </div>
        <div class="info-value description"><?= esc($org['description'] ?? 'No description available.') ?></div>
      </div>
    </div>
  </div>
</div>