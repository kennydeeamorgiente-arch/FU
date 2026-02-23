<div class="organization-form">
  <form id="form-edit-user">

    <input type="hidden" name="user-id" value="<?= $user['user_id'] ?>">
    <div class="form-input">
      <label for="user-email">Email <i class="fa-solid fa-pencil edit-icon" data-target="user-email"></i></label>
      <input type="email" name="user-email" id="user-email" value="<?= $user['email'] ?>" required readonly>
    </div>
    <div class="form-input">
      <label for="user-position">Position <i class="fa-solid fa-pencil edit-icon"
          data-target="user-position"></i></label>
      <input type="text" name="user-position" id="user-position" value="<?= $user['position'] ?? '' ?>" required
        readonly>
    </div>
    <div class="form-input">
      <label for="user-access-level">Access Level <i class="fa-solid fa-pencil edit-icon"
          data-target="user-access-level"></i></label>
      <select name="user-access-level" id="user-access-level" required readonly>
        <?php foreach ($accessLevels as $accessLevel): ?>
          <option value="<?= $accessLevel['access_id'] ?>" <?= ($user['access_id'] == $accessLevel['access_id']) ? 'selected' : '' ?>>
            <?= $accessLevel['access_name'] ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-input">
      <label for="user-organization">Organization <i class="fa-solid fa-pencil edit-icon"
          data-target="user-organization"></i></label>
      <select name="user-organization" id="user-organization" required readonly>
        <option value="">No Organization</option>
        <?php foreach ($organizations as $org): ?>
          <option value="<?= $org['org_id'] ?>" <?= ($user['org_id'] == $org['org_id']) ? 'selected' : '' ?>>
            <?= $org['org_name'] ?? 'Unknown Organization' ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="modal-footer" style="grid-column: span 2">
      <button class="btn-modal-cancel" id="modalCancelAddBtn" type="button">Cancel</button>
      <button class="btn-modal-confirm" id="modalConfirmAddBtn" type="submit">Save Changes</button>
    </div>
  </form>
</div>