<div class="container">
        <section class="admin-page-title-card">
          <div class="admin-page-title-content">
            <h1>Organization Management</h1>
            <p>Create and maintain organization profiles, advisers, and status.</p>
          </div>
        </section>

        <section class="admin-page-toolbar-card">
          <div class="admin-toolbar-grid">
            <div class="admin-toolbar-input">
              <i class="fa-solid fa-magnifying-glass"></i>
              <input type="text" id="org-search-input" placeholder="Search organization or adviser">
            </div>
            <select id="org-type-filter" class="admin-toolbar-select">
              <option value="">All Types</option>
              <option value="Academic">Academic</option>
              <option value="Non-Academic">Non-Academic</option>
              <option value="Cultural">Cultural</option>
              <option value="Sports">Sports</option>
              <option value="Religious">Religious</option>
            </select>
            <button type="button" id="org-clear-btn" class="admin-toolbar-btn subtle">Clear</button>
            <button class="admin-toolbar-btn primary" id="addOrgBtn">
              <i class="fa-solid fa-plus"></i>
              <span>New Org</span>
            </button>
          </div>
        </section>

        <section class="admin-page-table-card">
          <div class="table-header">
            <h3>Organization Directory</h3>
            <div class="table-actions">
              <span class="table-hint">Click row to view full profile</span>
            </div>
          </div>
          <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
              <?= session()->getFlashdata('error') ?>
            </div>
          <?php endif; ?>

          <table class="org-table" id="organizations-table">
            <thead>
              <tr>
                <th>
                  Organization
                </th>
                <th>Facebook Link</th>
                <th>Adviser</th>
                <th>Org Type</th>
                <th>Members Count</th>
                <th>Events</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="org-table-body">

            </tbody>
          </table>
        </section>
</div>
