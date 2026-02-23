<div class="organization-form">
  <form id="form-add-organization" enctype="multipart/form-data">
    <div class="form-input">
      <label for="organization-name">Organization Name</label>
      <input type="text" name="organization-name" required>
    </div>
    <div class="form-input">
      <label for="organization-fb-link">Facebook Link (Optional)</label>
      <input type="text" name="organization-fb-link">
    </div>
    <div class="form-input">
      <label for="organization-adviser">Organization Adviser</label>
      <input type="text" name="organization-adviser" required>
    </div>
    <div class="form-input">
      <label for="organization-type">Organization Type</label>
      <select name="organization-type" required>
        <option value="Academic">Academic</option>
        <option value="Non-Academic">Non-Academic</option>
        <option value="Religious">Religious</option>
      </select>
    </div>
    <div class="form-input">
      <label for="organization-logo">Organization Logo</label>
      <input type="file" name="organization-logo" required>
    </div>
    <div class="form-input">
      <label for="organization-count-members"># of Members</label>
      <input type="number" name="organization-count-members" required>
    </div>
    <div class="form-input">
      <label for="organization-count-members">Supporting Documents (Google Drive Link – Bylaws, Registration,
        etc.)</label>
      <input type="number" name="organization-drive-link" required>
    </div>
    <div class="form-input" style="grid-column: span 2">
      <label for="organization-overview">Organization Overview</label>
      <textarea name="organization-overview" required></textarea>
    </div>
    <div class="modal-footer" style="grid-column: span 2">
      <button class="btn-modal-cancel" id="modalCancelAddBtn" type="button">Cancel</button>
      <button class="btn-modal-confirm" id="modalConfirmAddBtn" type="submit">Confirm </button>
    </div>
  </form>

</div>