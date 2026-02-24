<?php
$session = session();
$org_id = $session->get("org_id");
$user_id = $session->get("user_id");

$latestFeedback = $latest_feedback ?? null;
if (!$latestFeedback && !empty($event_history ?? [])) {
  for ($idx = count($event_history) - 1; $idx >= 0; $idx--) {
    $history = $event_history[$idx];
    $statusId = (int) ($history['status_id'] ?? 0);
    $remarks = trim((string) ($history['remarks'] ?? ''));
    if (in_array($statusId, [6, 7], true) && $remarks !== '') {
      $latestFeedback = $history;
      break;
    }
  }
}
?>

<div class="container" id="event-form-container">
  <?php if (!isset($event) || !$event): ?>
    <section class="org-page-title-card">
      <div class="org-page-title-content">
        <h1>Apply for an Event</h1>
        <p>Submit a new event proposal for the approval workflow.</p>
      </div>
    </section>
  <?php else: ?>
    <section class="org-page-title-card">
      <div class="org-page-title-content">
        <h1>Edit Event</h1>
        <p>Update your event details based on revision remarks.</p>
      </div>
    </section>
  <?php endif; ?>

  <?php if (isset($event) && $event && !empty($latestFeedback)): ?>
    <?php
    $feedbackStatusId = (int) ($latestFeedback['status_id'] ?? 0);
    $feedbackStatusLabel = $feedbackStatusId === 6 ? 'Rejected' : 'Returned for Revision';
    $feedbackBy = trim((string) ($latestFeedback['access_name'] ?? 'Approver'));
    $feedbackDateRaw = $latestFeedback['created_at'] ?? null;
    $feedbackDate = $feedbackDateRaw ? date("F j, Y g:i A", strtotime($feedbackDateRaw)) : '';
    $feedbackRemarks = trim((string) ($latestFeedback['remarks'] ?? ''));
    ?>
    <section class="revision-feedback-card" role="note" aria-label="Latest reviewer remarks">
      <h4><?= esc($feedbackStatusLabel) ?> Remarks</h4>
      <p class="feedback-meta">
        <?= esc($feedbackBy) ?>
        <?= $feedbackDate ? " | " . esc($feedbackDate) : "" ?>
      </p>
      <blockquote><?= nl2br(esc($feedbackRemarks)) ?></blockquote>
    </section>
  <?php endif; ?>

  <section class="org-page-form-card">
    <div class="form-container">
    <?php if (isset($event) && $event): ?>
      <form class="proposal-form" id="edit-event-form">
        <input type="hidden" name="org" value="<?= $event['org_id'] ?>">
        <input type="hidden" name="event" value="<?= $event['event_id'] ?>">
        <input type="hidden" name="user" value="<?= $user_id ?>">

        <!-- Activity Initiator -->
        <div class="form-input" style="grid-column: span 2;">
          <p>Activity Initiator</p>
          <div class="form-input-radio" style="grid-column: span 2">
            <div>
              <label>Student Org-Initiated Activity</label>
              <input type="radio" name="activity-initiator" value="student-org" <?= (isset($event['activity_initiator']) && $event['activity_initiator'] == 'student-org') ? 'checked' : '' ?> required>
            </div>
            <div>
              <label>Department-Initiated Activity</label>
              <input type="radio" name="activity-initiator" value="department" <?= (isset($event['activity_initiator']) && $event['activity_initiator'] == 'department') ? 'checked' : '' ?> required>
            </div>
          </div>
        </div>

        <div class="form-input">
          <label for="event-name">Event Name</label>
          <input type="text" name="event-name" value="<?= $event['event_name'] ?>">
        </div>

        <div class="form-input">
          <label for="event-date">Event Date (Start - End)</label>
          <div class="date-range-inputs">
            <input type="date" name="start-date" value="<?= $event['event_start_date'] ?>"
              onchange="generateEventSchedule()">
            <span class="range-separator">-</span>
            <input type="date" name="end-date" value="<?= $event['event_end_date'] ?>" onchange="generateEventSchedule()">
          </div>
        </div>

        <!-- Nature of Activity with Checkboxes -->
        <div class="form-input" style="grid-column: span 2;">
          <label>Nature of Activity (Venue)</label>
          <div class="checkbox-group"
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-top: 8px;">
            <div class="checkbox-item">
              <input type="checkbox" name="nature-activity[]" value="on-campus" id="nature-on-campus"
                <?= (isset($event['nature_activity']) && strpos($event['nature_activity'], 'on-campus') !== false) ? 'checked' : '' ?>>
              <label for="nature-on-campus">On-campus</label>
            </div>
            <div class="checkbox-item">
              <input type="checkbox" name="nature-activity[]" value="off-campus" id="nature-off-campus"
                <?= (isset($event['nature_activity']) && strpos($event['nature_activity'], 'off-campus') !== false) ? 'checked' : '' ?>>
              <label for="nature-off-campus">Off-campus</label>
            </div>
            <div class="checkbox-item">
              <input type="checkbox" name="nature-activity[]" value="online" id="nature-online"
                <?= (isset($event['nature_activity']) && strpos($event['nature_activity'], 'online') !== false) ? 'checked' : '' ?>>
              <label for="nature-online">Online</label>
            </div>
            <div class="checkbox-item">
              <input type="checkbox" name="nature-activity[]" value="on-campus-online" id="nature-on-campus-online"
                <?= (isset($event['nature_activity']) && strpos($event['nature_activity'], 'on-campus-online') !== false) ? 'checked' : '' ?>>
              <label for="nature-on-campus-online">On-campus Online</label>
            </div>
            <div class="checkbox-item">
              <input type="checkbox" name="nature-activity[]" value="off-campus-online" id="nature-off-campus-online"
                <?= (isset($event['nature_activity']) && strpos($event['nature_activity'], 'off-campus-online') !== false) ? 'checked' : '' ?>>
              <label for="nature-off-campus-online">Off-campus Online</label>
            </div>
          </div>
        </div>

        <!-- Type of Activity -->
        <div class="form-input" style="grid-column: span 2;">
          <label>Type of Activity</label>
          <div class="radio-group"
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-top: 8px;">
            <div class="radio-item">
              <input type="radio" name="type-activity" value="academic" id="type-academic"
                <?= (isset($event['type_activity']) && $event['type_activity'] == 'academic') ? 'checked' : '' ?> required>
              <label for="type-academic">Academic</label>
            </div>
            <div class="radio-item">
              <input type="radio" name="type-activity" value="non-academic" id="type-non-academic"
                <?= (isset($event['type_activity']) && $event['type_activity'] == 'non-academic') ? 'checked' : '' ?>
                required>
              <label for="type-non-academic">Non-academic</label>
            </div>
            <div class="radio-item">
              <input type="radio" name="type-activity" value="religious" id="type-religious"
                <?= (isset($event['type_activity']) && $event['type_activity'] == 'religious') ? 'checked' : '' ?> required>
              <label for="type-religious">Religious</label>
            </div>
          </div>
        </div>

        <div class="form-input">
          <label for="event-venue">Venue</label>
          <input type="text" name="event-venue" value="<?= $event['event_venue'] ?>">
        </div>
        <div class="form-input">
          <label for="event-time">Time Range (Start - End)</label>
          <div class="time-range-inputs">
            <input type="time" name="event-time-start" value="<?= $event['event_start_time'] ?>">
            <span class="range-separator">-</span>
            <input type="time" name="event-time-end" value="<?= $event['event_end_time'] ?>">
          </div>
        </div>
        <div class="form-input">
          <label for="event-name">Estimated No. of Participants</label>
          <input type="text" name="event-participants"
            value="<?= $event['number_of_participants'] ?>">
        </div>
        <div class="form-input">
          <label for="event-type">Event Type</label>
          <input type="text" name="event-type" value="<?= $event['type_activity'] ?>">
        </div>
        <div class="form-input" style="grid-row: span 2;">
          <label for="event-name">Name of Invited Resource Speaker/s</label>
          <input type="text" name="event-speaker" value="<?= $event['name_of_invited_resource_speaker'] ?>">
          <textarea name="event-speaker-desc"><?= $event['invited_speaker_description'] ?></textarea>
        </div>

        <label for="yes-or-no" style="grid-column: span 2">Does your event have other collaborators?</label>
        <div class="form-input-radio" style="grid-column: span 2">
          <div class="form-input-radio">
            <label for="event-collab-bool">Yes</label>
            <input type="radio" name="event-collab-bool" value="yes">
          </div>
          <div class="form-input-radio">
            <label for="event-collab-bool">No</label>
            <input type="radio" name="event-collab-bool" value="no">
          </div>
        </div>
        <div class="group-collaborator hide" style="grid-column: span 2;">
          <!-- Container for collaborators -->
          <div id="collaborators-container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">

          </div>
          <!-- Add Collaborator Button -->
          <div style="text-align: center; margin-top: 15px;">
            <button type="button" id="add-collaborator"
              style="background: none; border: 2px dashed #8b0000; border-radius: 8px; padding: 20px 40px; cursor: pointer; transition: all 0.3s;">
              <i class="fa-regular fa-plus"
                style="font-size: 24px; color: #8b0000; display: block; margin-bottom: 8px;"></i>
              <span style="color: #8b0000; font-size: 14px;">Add Collaborator</span>
            </button>
          </div>
        </div>

        <!-- Template for a collaborator block -->
        <template id="collaborator-template">
          <div class="form-input collaborator-block" style="border: 1px solid #ddd; padding: 15px; border-radius: 8px;">
            <label style="font-weight: bold; margin-bottom: 10px; display: block;">Collaborator X</label>
            <div style="margin-bottom: 12px;">
              <input type="radio" name="collab-type-X" value="internal" class="collab-type-radio" data-collab-number="X"
                checked>
              <label style="margin-right: 15px;">FU Organization</label>
              <input type="radio" name="collab-type-X" value="external" class="collab-type-radio" data-collab-number="X">
              <label>External Organization</label>
            </div>
            <input type="text" name="event-collab[]" class="org-search-name collab-input-X-internal collab-name-X"
              placeholder="Search FU organization" style="margin-bottom: 10px;" autocomplete="off">
            <input type="text" name="event-collab-external-X" class="collab-input-X-external collab-name-X"
              placeholder="Enter external organization name..." style="display: none; margin-bottom: 10px;">
            <input type="hidden" name="event-collab-type[]" class="collab-type-X" value="internal">
            <textarea name="event-collab-desc[]" placeholder="Collaborator Description..." rows="3"></textarea>
          </div>
        </template>
        <div class="form-input" style="grid-column: span 2">
          <label for="event-name">Event Description</label>
          <textarea name="event-description"><?= $event['event_desc'] ?></textarea>
        </div>
        <div class="form-input" style="grid-row: span 2">
          <label for="event-purpose">Purpose/Objective of the Activity</label>
          <textarea name="event-purpose"><?= $event['event_purpose'] ?></textarea>
        </div>
        <div class="form-input" style="grid-row: span 2">
          <label for="event-name">Alignment to the College and Year Level Objectives</label>
          <textarea name="event-alignment"><?= $event['event_uni_objectives'] ?></textarea>
        </div>
        <div class="form-input" style="grid-column: span 2;">
          <table id="budgetTable">
            <?php if (!empty($budget_breakdown)): ?>
              <?php foreach ($budget_breakdown as $b): ?>
                <tr>
                  <td>
                    <input type="text" name="desc[]" value="<?= $b['description'] ?>" required>
                  </td>
                  <td>
                    <input type="number" name="qty[]" value="<?= $b['quantity'] ?>" required>
                  </td>
                  <td>
                    <input type="text" name="unit[]" value="<?= $b['unit'] ?>" required>
                  </td>
                  <td>
                    <input type="text" name="purpose[]" value="<?= $b['purpose'] ?>" required>
                  </td>
                  <td>
                    <input type="number" name="unit-price[]" value="<?= $b['unit_price'] ?>" required>
                  </td>
                  <td>
                    <input type="number" name="amount[]" value="<?= $b['amount'] ?>" required>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <!-- Default blank row kung walay data -->
              <tr>
                <td>
                  <input type="text" name="desc[]" required>
                </td>
                <td>
                  <input type="number" name="qty[]" required>
                </td>
                <td>
                  <input type="text" name="unit[]" required>
                </td>
                <td>
                  <input type="text" name="purpose[]" required>
                </td>
                <td>
                  <input type="number" name="unit-price[]" required>
                </td>
                <td>
                  <input type="number" name="amount[]" required>
                </td>
              </tr>
            <?php endif; ?>
            <tr id="event-add-row">
              <td colspan="6"><i class="fa-regular fa-plus fa-2xl"></i></td>
            </tr>
          </table>

        </div>

        <div class="form-input">
          <label>Source of Funds</label>
          <select name="budget-source" required>
            <option value="organization" <?= (isset($event['source_of_funds']) && $event['source_of_funds'] == 'organization') ? 'selected' : '' ?>>Organization
            </option>
            <option value="department" <?= (isset($event['source_of_funds']) && $event['source_of_funds'] == 'department') ? 'selected' : '' ?>>
              Department/College</option>
            <option value="university" <?= (isset($event['source_of_funds']) && $event['source_of_funds'] == 'university') ? 'selected' : '' ?>>University
            </option>
          </select>
        </div>
        <div class="form-input">
          <label>Estimated Budget</label>
          <input type="text" name="estimated-budget" value="<?= $event['event_budget'] ?>">
        </div>

        <div class="form-input">
          <label for="program-paper">Program Paper (Google Drive Link)</label>
          <input type="url" name="program-paper" placeholder="https://drive.google.com/..." required>
        </div>
        <div class="form-input">
          <label for="communication-letter">Communication Letter (Google Drive Link - Optional)</label>
          <input type="url" name="communication-letter" placeholder="https://drive.google.com/...">
        </div>
        <div class="form-input">
          <label for="proposal-paper">Proposal (Google Drive Link - Optional)</label>
          <input type="url" name="proposal-paper" placeholder="https://drive.google.com/...">
        </div>
        <div></div>
        <div></div>
        <button colspan="2" type="submit">UPDATE</button>
      </form>
    <?php else: ?>
      <form class="proposal-form" id="host-event-form">
        <input type="hidden" name="org" value="<?= $org_id ?>">

        <!-- Activity Initiator -->
        <div class="form-input" style="grid-column: span 2;">
          <p>Activity Initiator</p>
          <div class="form-input-radio" style="grid-column: span 2">
            <div>
              <label>Student Org-Initiated Activity</label>
              <input type="radio" name="activity-initiator" value="student-org" required>
            </div>
            <div>
              <label>Department-Initiated Activity</label>
              <input type="radio" name="activity-initiator" value="department" required>
            </div>
          </div>
        </div>

        <div class="form-input">
          <label for="event-name">Event Name</label>
          <input type="text" name="event-name" required>
        </div>

        <div class="form-input">
          <label for="event-date">Event Date (Start - End)</label>
          <div class="date-range-inputs">
            <input type="date" name="start-date" required onchange="generateEventSchedule()">
            <span class="range-separator">-</span>
            <input type="date" name="end-date" required onchange="generateEventSchedule()">
          </div>
        </div>

        <!-- Event Schedule Table -->
        <div class="form-input" style="grid-column: span 2;">
          <label for="event-schedule">Event Schedule by Day</label>
          <table id="eventScheduleTable" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <thead>
              <tr style="background-color: #8b0000; color: white;">
                <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Day</th>
                <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Date</th>
                <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Activities/Notes</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan="3" style="padding: 15px; text-align: center; color: #999;">Select a date range to generate
                  schedule</td>
              </tr>
            </tbody>
          </table>
        </div>
        <label for="yes-or-no" style="grid-column: span 2">Is the event outside the campus?</label>
        <div class="form-input-radio" style="grid-column: span 2">
          <div class="form-input-radio">
            <label for="event-venue-bool">Yes</label>
            <input type="radio" name="event-venue-bool" value="yes">
          </div>
          <div class="form-input-radio">
            <label for="event-venue-bool">No</label>
            <input type="radio" name="event-venue-bool" value="no">
          </div>
        </div>
        <div class="form-input">
          <label for="event-venue">Venue</label>
          <input type="text" name="event-venue" required>
        </div>
        <!-- Nature of Activity with Checkboxes -->
        <div class="form-input" style="grid-column: span 2;">
          <label>Nature of Activity (Venue)</label>
          <div class="checkbox-group"
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-top: 8px;">
            <div class="checkbox-item">
              <input type="checkbox" name="nature-activity[]" value="on-campus" id="nature-on-campus">
              <label for="nature-on-campus">On-campus</label>
            </div>
            <div class="checkbox-item">
              <input type="checkbox" name="nature-activity[]" value="off-campus" id="nature-off-campus">
              <label for="nature-off-campus">Off-campus</label>
            </div>
            <div class="checkbox-item">
              <input type="checkbox" name="nature-activity[]" value="online" id="nature-online">
              <label for="nature-online">Online</label>
            </div>
            <div class="checkbox-item">
              <input type="checkbox" name="nature-activity[]" value="on-campus-online" id="nature-on-campus-online">
              <label for="nature-on-campus-online">On-campus Online</label>
            </div>
            <div class="checkbox-item">
              <input type="checkbox" name="nature-activity[]" value="off-campus-online" id="nature-off-campus-online">
              <label for="nature-off-campus-online">Off-campus Online</label>
            </div>
          </div>
        </div>

        <!-- Type of Activity -->
        <div class="form-input" style="grid-column: span 2;">
          <label>Type of Activity</label>
          <div class="radio-group"
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-top: 8px;">
            <div class="radio-item">
              <input type="radio" name="type-activity" value="academic" id="type-academic" required>
              <label for="type-academic">Academic</label>
            </div>
            <div class="radio-item">
              <input type="radio" name="type-activity" value="non-academic" id="type-non-academic" required>
              <label for="type-non-academic">Non-academic</label>
            </div>
            <div class="radio-item">
              <input type="radio" name="type-activity" value="religious" id="type-religious" required>
              <label for="type-religious">Religious</label>
            </div>
          </div>
        </div>

        <div class="form-input">
          <label for="event-time">Time Range (Start - End)</label>
          <div class="time-range-inputs">
            <input type="time" name="event-time-start" required>
            <span class="range-separator">-</span>
            <input type="time" name="event-time-end" required>
          </div>
        </div>
        <div class="form-input">
          <label for="event-name">Estimated No. of Participants</label>
          <input type="text" name="event-participants" required>
        </div>
        <div class="form-input">
          <label for="event-type">Event Type</label>
          <select name="event-type" required>
            <option value="">Select Event Type</option>
            <option value="seminar">Seminar</option>
            <option value="workshop">Workshop</option>
            <option value="conference">Conference</option>
            <option value="training">Training</option>
            <option value="competition">Competition</option>
            <option value="exhibition">Exhibition</option>
            <option value="concert">Concert</option>
            <option value="sports-event">Sports Event</option>
            <option value="others">Others</option>
          </select>
        </div>
        <div class="form-input" style="grid-row: span 2;">
          <label for="event-name">Name of Invited Resource Speaker/s</label>
          <input type="text" name="event-speaker" required></input>
          <textarea name="event-speaker-desc" placeholder="Description about Speaker..."></textarea>
        </div>

        <label for="yes-or-no" style="grid-column: span 2">Does your event have other collaborators?</label>
        <div class="form-input-radio" style="grid-column: span 2">
          <div class="form-input-radio">
            <label for="event-collab-bool">Yes</label>
            <input type="radio" name="event-collab-bool" value="yes">
          </div>
          <div class="form-input-radio">
            <label for="event-collab-bool">No</label>
            <input type="radio" name="event-collab-bool" value="no">
          </div>
        </div>
        <div class="group-collaborator hide" style="grid-column: span 2;">
          <!-- Container for collaborators -->
          <div id="collaborators-container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">

          </div>
          <!-- Add Collaborator Button -->
          <div style="text-align: center; margin-top: 15px;">
            <button type="button" id="add-collaborator"
              style="background: none; border: 2px dashed #8b0000; border-radius: 8px; padding: 20px 40px; cursor: pointer; transition: all 0.3s;">
              <i class="fa-regular fa-plus"
                style="font-size: 24px; color: #8b0000; display: block; margin-bottom: 8px;"></i>
              <span style="color: #8b0000; font-size: 14px;">Add Collaborator</span>
            </button>
          </div>
        </div>

        <!-- Template for a collaborator block -->
        <template id="collaborator-template">
          <div class="form-input collaborator-block">
            <label>Collaborator X</label>

            <div style="margin-bottom: 8px;">
              <input type="radio" name="collab-type-X" value="internal" class="collab-type-radio" data-collab-number="X"
                checked>
              <label>FU Organization</label>
              <input type="radio" name="collab-type-X" value="external" class="collab-type-radio" data-collab-number="X">
              <label>External Organization</label>
            </div>

            <input type="text" name="event-collab[]" class="org-search-name collab-input-X-internal collab-name-X"
              placeholder="Search FU organization">
            <input type="text" name="event-collab-external-X" class="collab-input-X-external collab-name-X"
              placeholder="Enter external organization name..." style="display: none;">

            <textarea name="event-collab-desc[]" placeholder="Collaborator Description..."></textarea>
          </div>
        </template>
        <div class="form-input" style="grid-column: span 2">
          <label for="event-name">Event Description</label>
          <textarea name="event-description" placeholder="Enter Description..." required></textarea>
        </div>
        <div class="form-input" style="grid-row: span 2">
          <label for="event-purpose">Purpose/Objective of the Activity</label>
          <textarea name="event-purpose" placeholder="Enter Description..." required></textarea>
        </div>
        <div class="form-input" style="grid-row: span 2">
          <label for="event-name">Alignment to the College and Year Level Objectives</label>
          <textarea name="event-alignment" placeholder="Enter Description..." required></textarea>
        </div>
        <div class="form-input" style="grid-column: span 2;">
          <table id="budgetTable">
            <tr>
              <th>Description</th>
              <th>Quantity</th>
              <th>Unit</th>

              <th>Purpose</th>
              <th>Unit Price</th>
              <th>Amount</th>
            </tr>
            <tr class="budget-item-row">
              <td>
                <input type="text" name="desc[]" required>
              </td>
              <td>
                <input type="number" name="qty[]" required>
              </td>
              <td>
                <input type="text" name="unit[]" required>
              </td>
              <td>
                <input type="text" name="purpose[]" required>
              </td>
              <td>
                <input type="number" name="unit-price[]" required>
              </td>
              <td>
                <input type="number" name="amount[]" readonly>
              </td>
            </tr>
            <tr id="event-add-row">
              <td colspan="6"><i class="fa-regular fa-plus fa-2xl"></i></td>
            </tr>
          </table>

        </div>

        <div class="form-input">
          <label for="budget-source">Source of Funds</label>
          <select name="budget-source" required>
            <option value="organization">Organization</option>
            <option value="department">Department/College</option>
            <option value="university">University</option>
          </select>
        </div>
        <div class="form-input">
          <label for="estimated-budget">Estimated Budget</label>
          <input type="text" name="estimated-budget" readonly>
        </div>

        <div class="form-input">
          <label for="program-paper">Program Paper (Google Drive Link)</label>
          <input type="url" name="program-paper" placeholder="https://drive.google.com/..." required>
        </div>
        <div class="form-input">
          <label for="communication-letter">Communication Letter (Google Drive Link - Optional)</label>
          <input type="url" name="communication-letter" placeholder="https://drive.google.com/...">
        </div>
        <div class="form-input">
          <label for="proposal-paper">Proposal (Google Drive Link - Optional)</label>
          <input type="url" name="proposal-paper" placeholder="https://drive.google.com/...">
        </div>
        <div></div>
        <div></div>
        <button colspan="2" type="submit">SUBMIT</button>
      </form>
    <?php endif; ?>
    </div>
  </section>
</div>
