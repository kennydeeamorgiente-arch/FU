<div class="organization-form">
  <form id="form-delete-user">
    <input type="hidden" name="user-id" value="<?= $user['user_id'] ?>">
    <h4 style="grid-column: span 2">Are you sure you want to delete user <span class="user-email"><?= $user['email'] ?></span>?</h4>
    <div class="modal-footer" style="grid-column: span 2">
      <button class="btn-modal-cancel" id="modalCancelAddBtn" type="button">Cancel</button>
      <button class="btn-modal-confirm" id="modalConfirmAddBtn" type="submit">Yes</button>
    </div>
  </form>
</div>

