<div class="organization-form">
  <form id="form-edit-organization" enctype="multipart/form-data">

    <input type="hidden" name="org-id" value="<?= $org['org_id'] ?>">
    <div class="form-input">
      <label for="organization-name">Organization Name </label>
      <input type="text" name="organization-name" id="organization-name" value="<?= $org['org_name'] ?>" required>
    </div>
    <div class="form-input">
      <label for="organization-fb-link">Facebook Link </label>
      <input type="text" name="organization-fb-link" id="organization-fb-link" value="<?= $org['facebook_link'] ?>"
        required>
    </div>
    <div class="form-input">
      <label for="organization-adviser">Organization Adviser </label>
      <input type="text" name="organization-adviser" id="organization-adviser" value="<?= $org['adviser'] ?>" required>
    </div>
    <div class="form-input">
      <label for="organization-type">Organization Type </label>
      <select name="organization-type" id="organization-type" required>
        <option value="Academic">Academic</option>
        <option value="Non-Academic">Non-Academic</option>
        <option value="Religious">Religious</option>
      </select>
    </div>
    <div class="form-input">
      <label for="organization-logo">Organization Logo </label>
      <input type="hidden" name="existing-organization-logo" value="<?= $org['logo'] ?>">
      <input type="file" name="new-organization-logo" id="organization-logo" required>
    </div>
    <div class="form-input">
      <label for="organization-count-members"># of Members </label>
      <input type="number" name="organization-count-members" id="organization-count-members"
        value="<?= $org['org_num_members'] ?>" required>
    </div>
    <div class="form-input" style="grid-column: span 2">
      <label for="organization-overview">Organization Overview </label>
      <textarea name="organization-overview" id="organization-overview" required><?= $org['description'] ?></textarea>
    </div>

    <div class="modal-footer" style="grid-column: span 2">
      <button class="btn-modal-cancel" id="modalCancelAddBtn" type="button">Cancel</button>
      <button class="btn-modal-confirm" id="modalConfirmAddBtn" type="submit">Save Changes</button>
    </div>
  </form>
</div>