<?php if (isset($user) && $user): ?>
    <div class="user-details-container" style="padding: 10px;">
        <div class="info-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold; color: #666;">Email:</label>
            <div style="font-size: 16px;"><?= esc($user['email']) ?></div>
        </div>
        
        <div class="info-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold; color: #666;">Position:</label>
            <div style="font-size: 16px;"><?= esc($user['position']) ?></div>
        </div>
        
        <div class="info-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold; color: #666;">Access Level:</label>
            <div style="font-size: 16px;">
                <span class="badge badge-access" style="background-color: #e2e8f0; color: #475569; padding: 4px 8px; border-radius: 4px;">
                    <?= esc($user['access_name'] ?? 'N/A') ?>
                </span>
            </div>
        </div>
        
        <div class="info-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold; color: #666;">Organization:</label>
            <div style="font-size: 16px;">
                <?= esc($user['org_name'] ?? 'None') ?>
            </div>
        </div>
        
        <div class="info-group" style="margin-bottom: 15px;">
            <label style="font-weight: bold; color: #666;">Created At:</label>
            <div style="font-size: 16px;">
                <?= date('F j, Y, g:i a', strtotime($user['created_at'])) ?>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-danger">User not found.</div>
<?php endif; ?>

<div class="modal-footer" style="padding: 0; margin-top: 20px; border: none;">
    <button type="button" class="btn-modal-cancel" id="modalCancelBtn">Close</button>
</div>

<script>
    // Re-bind close buttons since this is loaded dynamically
    $("#modalCancelBtn").on("click", function() {
        $("#orgModal").removeClass("show");
    });
</script>