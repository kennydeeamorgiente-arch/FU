<div class="container">
        <section class="admin-page-title-card">
          <div class="admin-page-title-content">
            <h1>User Management</h1>
            <p>Create users, assign roles, and manage account access.</p>
            <div class="admin-page-meta">
              <span class="meta-pill">Role Based</span>
              <span class="meta-pill">Paginated</span>
            </div>
          </div>
        </section>

        <section class="admin-page-toolbar-card">
          <div class="admin-toolbar-grid">
            <div class="admin-toolbar-input">
              <i class="fa-solid fa-magnifying-glass"></i>
              <input type="text" id="users-search-input" placeholder="Search username, email, or position">
            </div>
            <select id="users-role-filter" class="admin-toolbar-select">
              <option value="">All Roles</option>
              <option value="Student">Student</option>
              <option value="Club Adviser">Club Adviser</option>
              <option value="SAO">SAO</option>
              <option value="OSL">OSL</option>
              <option value="Vice Chancellor">Vice Chancellor</option>
              <option value="OUC">OUC</option>
            </select>
            <button type="button" id="users-clear-btn" class="admin-toolbar-btn subtle">Clear</button>
            <button class="admin-toolbar-btn primary" id="addUserBtn">
              <i class="fa-solid fa-plus"></i>
              <span>New User</span>
            </button>
          </div>
        </section>

        <section class="admin-page-table-card">
          <div class="table-header">
            <h3>User Accounts</h3>
            <div class="table-actions">
              <span class="table-hint">Click row for quick view</span>
            </div>
          </div>
          <table class="org-table" id="users-table">
            <thead>
              <tr>
                <th>
                  User
                </th>
                <th>User_type</th>
                <th>Organization</th>
                <th>Position</th>
                <th>Created AT</th>
                <th class="actions-holder">Actions</th>
              </tr>
            </thead>

            <!-- para diko mag libog -->
            <tbody id="users-tbody">
              <!-- makuha rani sa get-users.php -->
            </tbody>

          </table>
        </section>
</div>
