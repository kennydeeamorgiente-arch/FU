<?php
$session = session();
$userId = $session->get("user_id");
$userAccessId = $session->get("access_id");
?>
<div class="events-wrapper">
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
    </div>

    <?php
    // Show approval buttons only if:
    // 1. Event is at user's access level (current_access_id matches user's access_id)
    // 2. User hasn't already approved this event
    // 3. Event status is pending (1) or in-progress (2) through approval workflow
    // Note: status_id = 2 means event is progressing through approval (level 1 approved, now level 2 can approve)
    $canApprove = ($userAccessId == $event["current_access_id"] &&
      in_array($event["status_id"], [1, 2]) &&
      !$user_has_approved);
    if ($canApprove): ?>
      <div class="response-buttons">
        <button id="btn-revision" data-id="<?= $event["event_id"] ?>">
          <h5>Return for Revision</h5>
        </button>
        <button id="btn-reject" data-id="<?= $event["event_id"] ?>">
          <h5>Reject</h5>
        </button>
        <button id="btn-accept" data-id="<?= $event["event_id"] ?>" data-user="<?= $userId ?>">
          <h5>Approve</h5>
        </button>
      </div>

      <div class="response-remarks">
        <textarea id="remarks"
          placeholder="Add Remarks (Optional for Approval, Required for Revision/Rejection).."></textarea>
        <div class="remarks-buttons">
          <button id="btn-cancel" data-id="<?= $event["event_id"] ?>">Cancel</button>
          <button id="btn-submit" data-id="<?= $event["event_id"] ?>" data-name="<?= $event["event_name"] ?>"
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
          <?= esc($event["event_desc"] ?? 'No description provided.') ?></p>
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
        <h5>Number of Participants</h5>
        <p><?= $event["number_of_participants"] ?></p>
      </div>
      <div>
        <h5>Has Resource Speaker?</h5>
        <p><?= $event["has_invited_speaker"] == 1 ? 'Yes' : 'No' ?></p>
      </div>
      <?php if ($event["has_invited_speaker"] == 1): ?>
        <div>
          <h5>Name of Resource Speaker</h5>
          <p><?= $event["name_of_invited_resource_speaker"] ?></p>
        </div>
        <div>
          <h5>Resource Speaker Description</h5>
          <p><?= $event["invited_speaker_description"] ?></p>
        </div>
      <?php endif; ?>
      <div>
        <h5>With Collaborators?</h5>
        <p><?= $event["with_collaborators"] == 1 ? 'Yes' : 'No' ?></p>
      </div>
    </div>
  </div>

  <div class="event-container">
    <h4>Budget Breakdown</h4>
    <div id="btn-back-to-events">
      <button onclick="closeAdminEventModal()">Back to Events</button>
    </div>
    <table id="budgetTable">
      <thead>
        <tr>
          <th>Item</th>
          <th>Quantity</th>
          <th>Unit Cost</th>
          <th>Total Cost</th>
          <th>Remove</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($budget_breakdown && count($budget_breakdown) > 0): ?>
          <?php foreach ($budget_breakdown as $budget): ?>
            <tr>
              <td><?= $budget["budget_item"] ?></td>
              <td><?= $budget["quantity"] ?></td>
              <td><?= $budget["unit_cost"] ?></td>
              <td><?= $budget["total_cost"] ?></td>
              <td><i class="fa-solid fa-trash"></i></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" style="text-align: center;">No budget breakdown provided</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="event-container">
    <h4>Event History</h4>
    <div class="event-history">
      <?php if ($event_history && count($event_history) > 0): ?>
        <?php foreach ($event_history as $history): ?>
          <div class="history-item">
            <h5><?= $history["status_name"] ?> - <?= $history["access_name"] ?></h5>
            <p><?= $history["remarks"] ?? 'No remarks provided.' ?></p>
            <small><?= date("F j, Y - g:i A", strtotime($history["created_at"])) ?></small>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No history available.</p>
      <?php endif; ?>
    </div>
  </div>

  <div class="event-container">
    <h4>Event Collaborators</h4>
    <div class="event-collaborators">
      <?php if ($event_collaborators && count($event_collaborators) > 0): ?>
        <?php foreach ($event_collaborators as $collaborator): ?>
          <div class="collaborator-item">
            <h5><?= $collaborator["collab_name"] ?></h5>
            <p><?= $collaborator["collab_purpose"] ?></p>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No collaborators specified.</p>
      <?php endif; ?>
    </div>
  </div>

  <div class="event-container">
    <h4>Event Uploads</h4>
    <div class="event-uploads">
      <?php if ($event_uploads && count($event_uploads) > 0): ?>
        <?php foreach ($event_uploads as $upload): ?>
          <div class="upload-item">
            <h5><?= $upload["file_name"] ?></h5>
            <a href="<?= base_url('admin/download/' . $upload["file_name"]) ?>" download>Download</a>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No files uploaded.</p>
      <?php endif; ?>
    </div>
  </div>

</div>