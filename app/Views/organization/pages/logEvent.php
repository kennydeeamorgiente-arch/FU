<div class="container log-event-page">
  <section class="org-page-title-card">
    <div class="org-page-title-content">
      <h1>Log My Event</h1>
      <p class="log-event-subtitle">Submit documentation and financial report links for completed events.</p>
    </div>
    <div class="org-page-meta">
      <span class="meta-pill">Status 3</span>
      <span class="meta-pill">Moves to Status 4</span>
    </div>
  </section>
  <section class="org-page-form-card">
    <div class="form-container">
    <form method="POST" class="proposal-form log-event-form" id="log-event-form">
      <div class="form-input is-full">
        <label for="log-event-select">Event (Awaiting Documentation)</label>
        <select id="log-event-select" name="event_id" required>
          <option value="">Loading events...</option>
        </select>
      </div>

      <div class="form-input is-full">
        <label>Event Details</label>
        <div id="log-event-details" class="log-event-details-box">
          Select an event to view details.
        </div>
      </div>

      <div class="form-input">
        <label for="financial-report-link">Financial Report (Google Drive Link)</label>
        <input
          id="financial-report-link"
          type="url"
          name="financial_report_link"
          placeholder="https://drive.google.com/..."
          required>
      </div>

      <div class="form-input">
        <label for="documentation-link">Documentation (Google Drive Link)</label>
        <input
          id="documentation-link"
          type="url"
          name="documentation_link"
          placeholder="https://drive.google.com/..."
          required>
      </div>

      <div class="form-input is-full">
        <label for="additional-notes">Additional Notes (Optional)</label>
        <textarea id="additional-notes" name="additional_notes" placeholder="Add optional notes about your submission..."></textarea>
      </div>

      <button type="submit" class="log-event-submit">Submit</button>
    </form>
  </div>
  </section>
</div>
