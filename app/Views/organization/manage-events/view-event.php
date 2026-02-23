<div class="event-history" data-event-id="<?= $event['event_id'] ?>" data-status-id="<?= $event['status_id'] ?? 0 ?>">

  <?php if (isset($event['status_id']) && $event['status_id'] == 7): ?>
    <div style="margin-bottom: 20px; text-align: right;">
      <button id="edit-event-btn" class="edit-event-button" data-event-id="<?= $event['event_id'] ?>"
        style="padding: 10px 20px; background: #8b0000; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px;">
        <i class="fa-solid fa-pencil"></i> Edit Event
      </button>
    </div>
  <?php endif; ?>

  <h4>Overview - <?= $event['event_name'] ?></h4>
  <div class="event-details overview">
    <div>
      <h5>Event Name</h5>
      <p><?= $event['event_name'] ?></p>
    </div>
    <div>
      <h5>Activity Initiator</h5>
      <p><?= ucfirst($event['activity_initiator'] ?? 'N/A') ?></p>
    </div>
    <div>
      <h5>Organization Name</h5>
      <p><?= $event['organization_name'] ?? $event['org_name'] ?? 'N/A' ?></p>
    </div>
    <div>
      <h5>Semester, Academic Year</h5>
      <p><?= $event['semester_academic_year'] ?? 'N/A' ?></p>
    </div>
    <div>
      <h5>Date Filed</h5>
      <p><?= $event["created_at"] ?></p>
    </div>
    <div>
      <h5>Proposed Date(s)</h5>
      <p><?= $event['event_start_date'] ? date("F j, Y", strtotime($event['event_start_date'])) : 'N/A' ?> -
        <?= $event['event_end_date'] ? date("F j, Y", strtotime($event['event_end_date'])) : 'N/A' ?>
      </p>
    </div>
    <div>
      <h5>Nature of Activity (Venue)</h5>
      <p><?= $event['nature_activity'] ?? 'N/A' ?></p>
    </div>
    <div>
      <h5>Type of Activity</h5>
      <p><?= ucfirst($event['type_activity'] ?? 'N/A') ?></p>
    </div>
    <div>
      <h5>Venue</h5>
      <p><?= $event['event_venue'] ?? 'N/A' ?></p>
    </div>
    <div>
      <h5>Status</h5>
      <p><?= $event['status_name'] ?? 'N/A' ?></p>
    </div>
    <div>
      <h5>Current Level</h5>
      <p><?= $event['access_name'] ?? 'N/A' ?></p>
    </div>
    <div>
      <h5>Requested Budget</h5>
      <p><?= $event['event_budget'] ?? 'N/A' ?></p>
    </div>
    <div>
      <h5>Attachments</h5>
      <p>---</p>
    </div>
  </div>
  <h4>Event Details</h4>
  <div class="event-details overview">
    <div style="grid-column: span 2;">
      <h5>Event Description</h5>
      <p style="white-space: pre-wrap; word-wrap: break-word;">
        <?= esc($event['event_desc'] ?? 'No description provided.') ?></p>
    </div>
    <div>
      <h5>Event Purpose/Objective</h5>
      <p><?= $event['event_purpose'] ?? 'N/A' ?></p>
    </div>
    <div>
      <h5>Alignment to College and
        Year Level Objectives</h5>
      <p><?= $event['event_uni_objectives'] ?? 'N/A' ?></p>
    </div>
    <div>
      <h5>Expected # of Participants</h5>
      <p><?= $event['number_of_participants'] ?? 'N/A' ?></p>
    </div>
    <div>
      <h5>Name of Invited Resource
        Speakers</h5>
      <p><?= $event['name_of_invited_resource_speaker'] ?? "N/A" ?></p>
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
      <?php foreach ($budget_breakdown as $bb): ?>
        <tr>
          <td>
            <input type="text" name="qty" id="qty" value="<?= $bb["quantity"] ?>" disabled>
          </td>
          <td>
            <input type="text" name="unit" id="unit" min="1" value="<?= $bb["unit"] ?>" disabled>
          </td>
          <td>
            <input type="text" name="desc" id="desc" value="<?= $bb["description"] ?>" disabled>
          </td>
          <td>
            <input type="text" name="purpose" id="purpose" value="<?= $bb["purpose"] ?>" disabled>
          </td>
          <td>
            <input type="number" name="unit-price" id="unit-price" value="<?= $bb["unit_price"] ?>" disabled>
          </td>
          <td>
            <input type="number" name="amount" id="amount" value="<?= $bb["amount"] ?>" disabled>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
  <h4>Attached Files</h4>
  <div class="event-details overview files">
    <div>
      <h5>Program Paper</h5>
      <i class="fa-solid fa-file fa-2xl"></i>
    </div>
    <div>
      <h5>Permit</h5>
      <i class="fa-solid fa-file fa-2xl"></i>
    </div>
    <div>
      <h5>Communication Letter</h5>
      <i class="fa-solid fa-file fa-2xl"></i>
    </div>

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
      <?php foreach ($event_history as $eh): ?>
        <tr>
          <td><?= $eh["access_id"] ?></td>
          <td><?= $eh["access_name"] ?></td>
          <td><?= $eh["status_name"] ?></td>
          <td><?= $eh["remarks"] ?></td>
          <td><?= date("F j, Y", strtotime($eh['created_at'])) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
  <h4> </h4>
</div>