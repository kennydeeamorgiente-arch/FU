<div class="organization-form">
  <form id="form-add-user">
    <?= csrf_field() ?>
    <div class="form-input">
      <label for="user-email">Email</label>
      <input type="email" name="user-email" id="user-email" required>
    </div>
    <div class="form-input">
      <label for="user-password">Password</label>
      <input type="password" name="user-password" id="user-password" required>
    </div>
    <div class="form-input">
      <label for="user-position">Position</label>
      <input type="text" name="user-position" id="user-position" required>
    </div>
    <div class="form-input">
      <label for="user-access-level">Access Level</label>
      <select name="user-access-level" id="user-access-level" required>
        <?php foreach ($accessLevels as $accessLevel): ?>
          <option value="<?= $accessLevel['access_id'] ?>">
            <?= $accessLevel['access_name'] ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-input">
      <label for="user-organization">Organization (Optional)</label>
      <select name="user-organization" id="user-organization">
        <option value="">No Organization</option>
        <?php foreach ($organizations as $org): ?>
          <option value="<?= $org['org_id'] ?>">
            <?= $org['org_name'] ?? 'Unknown Organization' ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="modal-footer" style="grid-column: span 2">
      <button class="btn-modal-cancel" id="modalCancelAddBtn" type="button">Cancel</button>
      <button class="btn-modal-confirm" id="modalConfirmAddBtn" type="submit">Confirm</button>
    </div>
  </form>
</div>