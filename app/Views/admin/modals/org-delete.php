<div class="organization-form">
  <form id="form-delete-organization">
    <input type="hidden" name="org-id" value="<?= $org['org_id'] ?>">
    <h3 style="grid-column: span 2">Are you sure you want to delete <?= $org['org_name'] ?>?</h3>
    <div class="modal-footer" style="grid-column: span 2">
      <button class="btn-modal-cancel" id="modalCancelAddBtn" type="button">Cancel</button>
      <button class="btn-modal-confirm" id="modalConfirmAddBtn" type="submit">Yes</button>
    </div>
  </form>

</div>