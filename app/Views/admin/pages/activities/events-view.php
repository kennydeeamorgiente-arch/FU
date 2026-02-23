<?php
$session = session();
$userId = $session->get("user_id");
$userAccessId = $session->get("access_id");
?>
<div class="events-wrapper">
  <div id="btn-back-to-events" style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
    <button onclick="if(typeof navigateToPage === 'function') { $('a[data-link=\" admin/manage-events\"]').click(); }
      else { window.location.href='<?= base_url('admin/manage-events') ?>' ; }"
      style="padding: 10px 20px; background: #8b0000; color: white; border: none; border-radius: 5px; cursor: pointer;">Back
      to Events</button>
  </div>
  <div class="event-container">
    <h4>Overview - <?= $event["event_name"] ?></h4>
    <div class="event-details overview">
      <div>
        <h5>Event Name</h5>
        <p><?= $event["event_name"] ?></p>
      </div>
      <div>
        <h5>Activity Initiator</h5>
        <p><?= ucfirst($event["activity_initiator"] ?? 'N/A') ?></p>
      </div>
      <div>
        <h5>Organization Name</h5>
        <p><?= $event["organization_name"] ?? $event["org_name"] ?></p>
      </div>
      <div>
        <h5>Semester, Academic Year</h5>
        <p><?= $event["semester_academic_year"] ?? 'N/A' ?></p>
      </div>
      <div>
        <h5>Date Filed</h5>
        <p><?= $event["created_at"] ?></p>
      </div>
      <div>
        <h5>Proposed Date(s)</h5>
        <p><?= $event["event_start_date"] ?>
          <?= $event["event_end_date"] == null ? "" : "- " . $event["event_end_date"] ?>
        </p>
      </div>
      <!-- <div>
        <h5>Event Type</h5>
        <p></p>
      </div> -->
      <div>
        <h5>Nature of Activity (Venue)</h5>
        <p><?= $event["nature_activity"] ?? 'N/A' ?></p>
      </div>
      <div>
        <h5>Type of Activity</h5>
        <p><?= ucfirst($event["type_activity"] ?? 'N/A') ?></p>
      </div>
      <div>
        <h5>Venue</h5>
        <p><?= $event["event_venue"] ?></p>
      </div>
      <div>
        <h5>Status</h5>
        <p><?= $event["status_name"] ?></p>
      </div>
      <div>
        <h5>Current Level</h5>
        <p><?= $event["access_name"] ?></p>
      </div>
      <div>
        <h5>Requested Budget</h5>
        <p><?= $event["event_budget"] ?></p>
      </div>
      <div>
        <h5>Source of Funds</h5>
        <p><?= $event["source_of_funds"] ?? "Department" ?></p>
      </div>

    </div>
    <?php
    // Show approval buttons only if:
    // 1. Event is at user's access level (current_access_id matches user's access_id)
    // 2. User hasn't already approved this event
    // 3. Event status is pending (1) or in-progress (2) through approval workflow
    // Note: status_id = 2 means event is progressing through approval (level 1 approved, now level 2 can approve)
    $canApprove = (
      (int) $userAccessId === (int) ($event['current_access_id'] ?? 0) &&
      in_array((int) ($event["status_id"] ?? 0), [1, 2], true) &&
      !($user_has_approved ?? false)
    );
    if ($canApprove): ?>
      <div class="response-buttons">
        <button id="btn-revision" data-id="<?= $event["event_id"] ?>" data-user="<?= $userId ?>"
          data-name="<?= esc($event["event_name"], "attr") ?>">
          <h5>Return for Revision</h5>
        </button>
        <button id="btn-reject" data-id="<?= $event["event_id"] ?>" data-user="<?= $userId ?>"
          data-name="<?= esc($event["event_name"], "attr") ?>">
          <h5>Reject</h5>
        </button>
        <button id="btn-accept" data-id="<?= $event["event_id"] ?>" data-user="<?= $userId ?>"
          data-name="<?= esc($event["event_name"], "attr") ?>">
          <h5>Approve</h5>
        </button>
      </div>

      <div class="response-remarks">
        <textarea id="remarks"
          placeholder="Add Remarks (Optional for Approval, Required for Revision/Rejection).."></textarea>
        <div class="remarks-buttons">
          <button id="btn-cancel" data-id="<?= $event["event_id"] ?>">Cancel</button>
          <button id="btn-submit" data-id="<?= $event["event_id"] ?>" data-name="<?= esc($event["event_name"], "attr") ?>"
            data-user="<?= $userId ?>">Submit</button>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <div class="event-container">
    <h4>Event Details</h4>
    <div class="event-details overview">
      <div style="grid-column: span 2;">
        <h5>Event Description</h5>
        <p style="white-space: pre-wrap; word-wrap: break-word;">
          <?= esc($event["event_desc"] ?? 'No description provided.') ?>
        </p>
      </div>
      <div>
        <h5>Event Purpose/Objective</h5>
        <p><?= $event["event_purpose"] ?></p>
      </div>
      <div>
        <h5>Alignment to College and
          Year Level Objectives</h5>
        <p><?= $event["event_uni_objectives"] ?></p>
      </div>
      <div>
        <h5>Expected # of Participants</h5>
        <p><?= $event["number_of_participants"] ?></p>
      </div>
      <div>
        <h5>Name of Invited Resource
          Speakers</h5>
        <p><?= $event["name_of_invited_resource_speaker"] ?></p>
      </div>
      <div>
        <h5>Collaborators</h5>
        <?php foreach ($event_collaborators as $ec): ?>
          <p><?= $ec["org_name"] ?></p>
        <?php endforeach; ?>
      </div>
    </div>
    <h4>Budget Breakdown</h4>
    <div class="event-details">
      <table id="budgetTable" style="grid-column: span 2;">
        <tr>
          <th>Quantity</th>
          <th>Unit</th>
          <th>Description</th>
          <th>Purpose</th>
          <th>Unit Price</th>
          <th>Amount</th>
        </tr>
        <?php foreach ($budget_breakdown as $budget): ?>
          <tr>
            <td>
              <input type="text" name="qty" value="<?= $budget["quantity"] ?>" readonly>
            </td>
            <td>
              <input type="text" name="unit" value="<?= $budget["unit"] ?>" readonly>
            </td>
            <td>
              <input type="text" name="desc" value="<?= $budget["description"] ?>" readonly>
            </td>
            <td>
              <input type="text" name="purpose" value="<?= $budget["purpose"] ?>" readonly>
            </td>
            <td>
              <input type="number" name="unit-price" value="<?= $budget["unit_price"] ?>" readonly>
            </td>
            <td>
              <input type="number" name="amount" value="<?= $budget["amount"] ?>" readonly>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
    </div>
    <h4>Attached Files</h4>
    <div class="event-details overview files">

      <?php foreach ($event_uploads as $eu): ?>
        <div>
          <h5><?= $eu["file_type"] ?></h5>
          <a href="<?= base_url('organization/files/event/' . urlencode($eu['file_path'])) ?>" target="_blank">
            <i class="fa-solid fa-file fa-2xl"></i>
          </a>
        </div>
      <?php endforeach; ?>
    </div>

    <h4>Approval History</h4>
    <div class="event-details">

      <table class="history-details">
        <tr>
          <th>
            LEVEL
          </th>
          <th>
            APPROVER
          </th>
          <th>
            STATUS
          </th>
          <th>
            REMARKS
          </th>
          <th>
            DATE
          </th>
        </tr>
        <?php foreach ($event_history as $history): ?>
          <tr>
            <td><?= $history["access_id"] ?></td>
            <td><?= $history["access_name"] ?></td>
            <td><?= $history["status_name"] ?></td>
            <td><?= $history["remarks"] ?></td>
            <td><?= $history["created_at"] ?></td>
          </tr>
        <?php endforeach; ?>
      </table>

    </div>
    <?php
    // Show final approval button only if:
    // 1. User is at the highest access level
    // 2. Event is at the highest access level (ready for final approval)
    // 3. Event status is pending (1) or in-progress (2) through approval workflow
    // 4. User hasn't already approved
    $canFinalApprove = ($event["highest_access_level"] == $userAccessId &&
      $event["current_access_id"] == $event["highest_access_level"] &&
      $event["status_id"] == 4);
    if ($canFinalApprove): ?>
      <div class="response-final-approval">
        <div class="remarks-buttons">
          <button id="btn-approve" data-id="<?= $event["event_id"] ?>" data-name="<?= $event["event_name"] ?>"
            data-user="<?= $userId ?>">Approve</button>

        </div>
        <h5></h5>
      </div>
    <?php endif; ?>
  </div>
  <!-- Modal for Actions -->
  <div id="points-modal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 id="modalTitle">Assign Activity Points</h3>
        <span class="modal-close">&times;</span>
      </div>
      <div class="modal-body" id="modalBody">
        <?php foreach ($points_system as $points): ?>
          <label>
            <input type="radio" name="points" value="<?= $points["id"] ?>">
            <strong><?= $points["points"] ?> Points</strong>
            <?= $points["description"] ?><br>
          </label><br>
        <?php endforeach; ?>
        <div class="response-buttons">
          <div class="btn-modal-cancel">Cancel</div>
          <div id="btn-points-confirm" class="btn-modal-confirm">Confirm</div>
        </div>
      </div>
    </div>
  </div>
</div>
