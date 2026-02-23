# Complete Code Restoration Guide - Event Table DataTables Implementation

## ⚠️ IMPORTANT: This document contains complete code for full restoration

If all code is lost, this document provides everything needed to recreate the implementation.

---

## File 1: `app/Views/organization/pages/homepage.php`

### Complete File Content

```php
<?php
$session = session();
$org_id = $session->get("org_id");
?>

<div class="container">
    <div class="cards">
      <?= view('organization/components/card', [
        'title' => 'Current Ranking',
        'content' => '4',
        'color' => 'red',
      ]) ?>
      <?= view('organization/components/card', [
        'title' => 'Propose An Event',
        'content' => '',
        'color' => 'blue',
        'footer' => 'Check Progress'
      ]) ?>
      <?= view('organization/components/card', [
        'title' => 'Leaderboard',
        'content' => '',
        'color' => 'yellow',
        'footer' => 'Proposal Form'
      ]) ?>
    </div>
    <div class="log-activity">
      <div class="left">
        <h3>Log Your Activity</h3>
        <h5>Organizing, Contribution &
          Attending</h5>
      </div>
      <div class="right">
        <button>Propose an Event</button>
        <button>Log an Event</button>
        <button>Contributed to an Event</button>
      </div>
    </div>

  <div class="event-org-table">
      <div class="table-header">
        <h3>Track Your Events</h3>
        <div class="table-filter">
          <h4> Filter by Date</h4>
          <input id = "demo" type="text" name="daterange" />
          </div> 
      </div>

      <table id="myEventTable">
        <thead>
          <tr>
            <th>Event Date</th>
            <th>Date Submitted</th>
            <th>Event Name</th>
            <th>Approval Level</th>
            <th>Event Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="track-events-tbody" data-id="<?= $org_id ?>"></tbody>
      </table>
    </div>
</div>
```

### Key Changes:
- Added `<tr>` wrapper around `<th>` elements in `<thead>`
- Added date range picker input: `<input id="demo" type="text" name="daterange" />`
- Added heading: `<h4> Filter by Date</h4>`

---

## File 2: `app/Controllers/Organization/OrganizationController.php`

### Modified Method: `getEvents()`

**Location**: Around line 68-95

**Complete Method Code**:

```php
public function getEvents()
{
  $org_id = $this->request->getGet("org_id");
  $start_date = $this->request->getGet("start_date");
  $end_date = $this->request->getGet("end_date");

  if (!$org_id) {
    return $this->response->setJSON([
      "status" => "error",
      "message" => "Organization ID is required."
    ]);
  }

  $data = [];
  // If date range is provided, filter by both org_id and date range
  if ($start_date && $end_date) {
    $data = $this->event->getEventsByOrgIdAndDateRange($org_id, $start_date, $end_date);
  } else {
    // Otherwise, just get events by org_id
    $data = $this->event->getEventsByOrgId($org_id);
  }

  // Always return success, even if no data is found, but with an empty array
  return $this->response->setJSON([
    "status" => "success",
    "data" => $data
  ]);
}
```

### Changes:
- Added `$start_date` and `$end_date` parameter extraction
- Added conditional logic to use date filtering when dates are provided
- Changed return to always return `success` status with data array (even if empty)

---

## File 3: `app/Models/EventsModel.php`

### New Method: `getEventsByOrgIdAndDateRange()`

**Location**: Add after `getEventsByOrgId()` method (around line 105)

**Complete Method Code**:

```php
public function getEventsByOrgIdAndDateRange($org_id, $startDate, $endDate)
{
  // Filter events where event_start_date falls within the selected date range
  // Convert dates to datetime format for comparison (add time to end date to include the full day)
  $startDateTime = $startDate . ' 00:00:00';
  $endDateTime = $endDate . ' 23:59:59';

  return $this
    ->select('events.*, organization.org_name, events_status.name as status_name, access_level.access_name as access_name, point_system.points')
    ->join('organization', 'organization.org_id = events.org_id', 'left')
    ->join('access_level', 'access_level.access_id = events.current_access_id', 'left')
    ->join('events_status', 'events_status.status_id = events.status_id', 'left')
    ->join('activities', 'activities.event_id = events.event_id', 'left')
    ->join('point_system', 'point_system.id = activities.point_system_id', 'left')
    ->where('events.org_id', $org_id)
    ->where('events.event_start_date >=', $startDateTime)
    ->where('events.event_start_date <=', $endDateTime)
    ->orderBy('events.event_start_date', 'ASC')
    ->findAll();
}
```

### Key Points:
- Filters by `event_start_date` (not `event_end_date`)
- Converts dates to datetime format for accurate comparison
- Includes all necessary joins for related data
- Orders by `event_start_date` ascending

---

## File 4: `public/js/organization/event.js`

### Complete Modified Function: `getOrgEvents()`

**Location**: Replace existing `getOrgEvents()` function (around line 100)

**Complete Function Code**:

```javascript
function getOrgEvents(startDate, endDate) {
  let tbody = $("#track-events-tbody");
  let table = $('#myEventTable');
  
  if (tbody.length === 0 || table.length === 0) {
    return;
  }
  
  let org_id = tbody.data("id");
  
  if (!org_id) {
    console.log("Organization ID not found");
    return;
  }
  
  // Build data object with org_id
  let requestData = { org_id: org_id };
  
  // Add date range if provided
  if (startDate && endDate && typeof startDate.format === 'function' && typeof endDate.format === 'function') {
    requestData.start_date = startDate.format('YYYY-MM-DD');
    requestData.end_date = endDate.format('YYYY-MM-DD');
  }
  
  $.ajax({
    url: `${BASE_URL}organization/get-org-events`,
    method: "GET",
    data: requestData,
    success: function (re) {
      // Destroy existing DataTable if it exists
      if ($.fn.DataTable.isDataTable('#myEventTable')) {
        table.DataTable().destroy();
      }
      
      // Clear tbody
      tbody.empty();
      
      if (re.status == "success") {
        let data = re.data || [];
        
        // Add rows if data exists
        if (data.length > 0) {
          data.forEach((event) => {
            tbody.append(`
              <tr class="event-row" data-id="${event.event_id}">
                <td>${event.event_start_date || ''}</td>
                <td>${event.created_at || ''}</td>
                <td>${event.event_name || ''}</td>
                <td>${event.current_access_id || ''}</td>
                <td>${event.status_name || ''}</td>
                <td>
                  <div class="action-buttons">
                    <button class="action-btn edit-btn" data-id="${event.event_id}">
                      <i class="fa-solid fa-pencil"></i>
                    </button>
                    <button class="action-btn view-btn" data-id="${event.event_id}">
                      <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    <button class="action-btn delete-btn" data-id="${event.event_id}">
                      <i class="fa-solid fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            `);
          });
        }
        
        // Initialize DataTable
        table.DataTable({
          destroy: true,
          ordering: { indicators: false },
          responsive: true,
          paging: true,
          pageLength: 10,
          lengthChange: true,
          searching: true,
          info: true,
          language: {
            emptyTable: "No results found."
          }
        });
      } else {
        // Handle error status from backend
        // Initialize DataTable with error message
        table.DataTable({
          destroy: true,
          ordering: { indicators: false },
          responsive: true,
          paging: true,
          pageLength: 10,
          lengthChange: true,
          searching: true,
          info: true,
          language: {
            emptyTable: re.message || "Error loading events. Please try again."
          }
        });
      }
    },
    error: function(xhr, status, error) {
      console.log("AJAX Error:", error);
      
      // Destroy existing DataTable if it exists
      if ($.fn.DataTable.isDataTable('#myEventTable')) {
        table.DataTable().destroy();
      }
      
      tbody.empty();
      
      // Initialize DataTable with error message
      table.DataTable({
        destroy: true,
        ordering: { indicators: false },
        responsive: true,
        paging: true,
        pageLength: 10,
        lengthChange: true,
        searching: true,
        info: true,
        language: {
          emptyTable: "Error loading events. Please try again."
        }
      });
    }
  });
}
```

### New Function: `initializeDateRangePicker()`

**Location**: Add after `getOrgEvents()` function (around line 228)

**Complete Function Code**:

```javascript
// Function to initialize date range picker and load events
function initializeDateRangePicker() {
  if ($("#demo").length > 0 && $("#track-events-tbody").length > 0) {
    $('#demo').daterangepicker({
      ranges: {
        'Today': [moment(), moment()],
        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
        'This Month': [moment().startOf('month'), moment().endOf('month')],
        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
      },
      alwaysShowCalendars: true,
      autoUpdateInput: false,
      locale: {
        format: 'MM/DD/YYYY',
        cancelLabel: 'Clear'
      },
      opens: 'left'
    }, function(start, end, label) {
      // Predefined range selected
      $('#demo').val(start.format('MM/DD/YYYY') + ' - ' + end.format('MM/DD/YYYY'));
      getOrgEvents(start, end);
    });
    
    // Handle apply button (manual date selection)
    $('#demo').on('apply.daterangepicker', function(ev, picker) {
      $('#demo').val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
      getOrgEvents(picker.startDate, picker.endDate);
    });
    
    // Handle cancel button (show all events)
    $('#demo').on('cancel.daterangepicker', function(ev, picker) {
      $('#demo').val('');
      getOrgEvents();
    });
    
    // Initial load - show all events
    getOrgEvents();
  } else if ($("#track-events-tbody").length > 0) {
    // If no date picker, load all events
    getOrgEvents();
  }
}
```

### Initialization Code

**Location**: Add at the end of `$(document).ready()` function, before closing brace

**Complete Code**:

```javascript
// Try to initialize immediately
initializeDateRangePicker();

// Also try after a delay for AJAX-loaded content
setTimeout(function() {
  initializeDateRangePicker();
}, 500);

// Listen for page content loaded event (remove existing listener first to prevent duplicates)
$(document).off('pageContentLoaded.eventjs').on('pageContentLoaded.eventjs', function(e, url) {
  if (url && (url.includes('/organization/homepage') || url.includes('homepage'))) {
    setTimeout(function() {
      initializeDateRangePicker();
    }, 300);
  }
});
```

### Important Notes:
- Replace the old `getOrgEvents()` call (if it exists) with the initialization code above
- The function now accepts `startDate` and `endDate` parameters
- URL should be: `${BASE_URL}organization/get-org-events` (no leading slash)

---

## File 5: `public/css/organization/homepage.css`

### Complete CSS Additions

Add all of the following CSS to the end of the file:

```css
/* Date Range Picker Input */
#demo {
  padding: 10px 15px;
  font-size: 16px;
  font-weight: bold;
  border: 2px solid #A52A2A;
  border-radius: 8px;
  background-color: white;
  color: #333;
  cursor: pointer;
  width: 100%;
  box-sizing: border-box;
}

#demo:focus {
  outline: none;
  border-color: #8B0000;
  box-shadow: 0 0 5px rgba(165, 42, 42, 0.3);
}

/* Date Range Picker Container */
.daterangepicker {
  border: 2px solid #A52A2A !important;
  border-radius: 8px !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
  font-family: inherit !important;
}

/* Calendar Header */
.daterangepicker .calendar-table th {
  color: #A52A2A;
  font-weight: bold;
}

/* Calendar Navigation Buttons */
.daterangepicker .prev,
.daterangepicker .next {
  color: #A52A2A !important;
}

.daterangepicker .prev:hover,
.daterangepicker .next:hover {
  background-color: #A52A2A !important;
  color: white !important;
  border-radius: 4px;
}

/* Month/Year Selectors */
.daterangepicker .monthselect,
.daterangepicker .yearselect {
  color: #A52A2A;
  font-weight: bold;
}

.daterangepicker .monthselect:hover,
.daterangepicker .yearselect:hover {
  background-color: #A52A2A !important;
  color: white !important;
  border-radius: 4px;
}

/* Calendar Days */
.daterangepicker td.available:hover {
  background-color: #f0f0f0 !important;
  color: #A52A2A !important;
  border-radius: 4px;
}

/* Active/Selected Date */
.daterangepicker td.active,
.daterangepicker td.active:hover {
  background-color: #A52A2A !important;
  color: white !important;
  border-radius: 4px !important;
}

/* Date Range (in-range dates) */
.daterangepicker td.in-range {
  background-color: rgba(165, 42, 42, 0.1) !important;
  color: #A52A2A !important;
}

.daterangepicker td.in-range:hover {
  background-color: rgba(165, 42, 42, 0.2) !important;
}

/* Start and End Dates */
.daterangepicker td.start-date,
.daterangepicker td.end-date {
  background-color: #A52A2A !important;
  color: white !important;
  border-radius: 4px !important;
}

/* Predefined Ranges */
.daterangepicker .ranges {
  padding: 10px;
}

.daterangepicker .ranges li {
  color: #333;
  padding: 8px 12px;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.daterangepicker .ranges li:hover {
  background-color: #A52A2A !important;
  color: white !important;
}

.daterangepicker .ranges li.active {
  background-color: #A52A2A !important;
  color: white !important;
  font-weight: bold;
}

/* Apply and Cancel Buttons */
.daterangepicker .drp-buttons .btn {
  padding: 8px 20px;
  border-radius: 6px;
  font-weight: bold;
  transition: all 0.2s ease;
  border: none;
}

.daterangepicker .drp-buttons .btn.applyBtn {
  background-color: #A52A2A !important;
  color: white !important;
  margin-left: 5px;
  padding: 10px 24px !important;
}

.daterangepicker .drp-buttons .btn.applyBtn:hover {
  background-color: #8B0000 !important;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(165, 42, 42, 0.3);
}

.daterangepicker .drp-buttons .btn.cancelBtn {
  background-color: #f0f0f0 !important;
  color: #333 !important;
  padding: 10px 24px !important;
}

.daterangepicker .drp-buttons .btn.cancelBtn:hover {
  background-color: #e0e0e0 !important;
}

/* Calendar Table Styling */
.daterangepicker .calendar-table {
  border-collapse: separate;
  border-spacing: 2px;
}

.daterangepicker .calendar-table td,
.daterangepicker .calendar-table th {
  padding: 8px;
  text-align: center;
}

/* Today's Date */
.daterangepicker td.today {
  border: 1px solid #A52A2A !important;
  border-radius: 4px;
}

.daterangepicker td.today:not(.active) {
  background-color: transparent !important;
  color: #A52A2A !important;
  font-weight: bold;
}

/* Pagination - Active/Current State */
.dataTables_wrapper .dataTables_paginate .paginate_button.current:not(.disabled),
.dataTables_wrapper .dataTables_paginate .paginate_button.current:not(.disabled) * {
    background-color: #A52A2A !important;
    border: 1px solid #A52A2A !important;
    border-radius: 4px !important;
    color: white !important;
}

/* Pagination - Hover State */
.dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.disabled):not(.current) {
    background-color: #A52A2A !important;
    border: 1px solid #A52A2A !important;
    border-radius: 4px !important;
    color: white !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.disabled):not(.current) * {
    color: white !important;
}

/* Pagination - Current Button Hover */
.dataTables_wrapper .dataTables_paginate .paginate_button.current:hover:not(.disabled),
.dataTables_wrapper .dataTables_paginate .paginate_button.current:hover:not(.disabled) * {
    background-color: #A52A2A !important;
    border: 1px solid #A52A2A !important;
    border-radius: 4px !important;
    color: white !important;
}

.dataTables_length{
  margin-bottom: 25px;
}

/* DataTables Length and Filter Styling */
.dataTables_wrapper .dataTables_length select {
  padding: 5px 10px;
  border: 1px solid #A52A2A;
  border-radius: 4px;
  margin: 0 5px;
}

.dataTables_wrapper .dataTables_length select:focus {
  outline: none;
  border-color: #8B0000;
  box-shadow: 0 0 5px rgba(165, 42, 42, 0.3);
}

.dataTables_wrapper .dataTables_filter input {
  padding: 5px 10px;
  border: 1px solid #A52A2A;
  border-radius: 4px;
  margin-left: 10px;
}

.dataTables_wrapper .dataTables_filter input:focus {
  outline: none;
  border-color: #8B0000;
  box-shadow: 0 0 5px rgba(165, 42, 42, 0.3);
}

.event-row:hover{
  background-color: #A52A2A;
  color: white;
}

.event-row:hover .action-btn,
.event-row:hover .action-btn i {
  color: white ;
}

.event-row .action-btn:hover {
  background-color: white;
  color: #A52A2A;
}

.event-row .action-btn:hover i {
  color: #A52A2A;
}
```

---

## Dependencies Required

### JavaScript Libraries (must be loaded before `event.js`):
1. **jQuery** - Already included
2. **DataTables.js** - Already included
3. **moment.js** - Required for date handling
4. **daterangepicker.js** - Required for date range picker

### CSS Files (must be loaded):
1. **DataTables CSS** - Already included
2. **daterangepicker CSS** - Already included

---

## Installation Steps (If Starting Fresh)

1. **Update View File**: Replace `homepage.php` with File 1 content
2. **Update Controller**: Modify `getEvents()` method with File 2 content
3. **Update Model**: Add `getEventsByOrgIdAndDateRange()` method from File 3
4. **Update JavaScript**: Replace `getOrgEvents()` and add initialization code from File 4
5. **Update CSS**: Add all CSS from File 5 to `homepage.css`
6. **Verify Dependencies**: Ensure all required libraries are loaded
7. **Test**: 
   - Page loads with all events
   - Date picker opens and functions
   - Date filtering works
   - "No results found" displays when appropriate
   - Cancel button clears filter

---

## Critical Implementation Details

### URL Format
- **Correct**: `${BASE_URL}organization/get-org-events` (no leading slash)
- **Wrong**: `${BASE_URL}/organization/get-org-events` (with leading slash)

### Date Format
- Frontend sends: `YYYY-MM-DD` (e.g., "2024-01-15")
- Backend converts to: `YYYY-MM-DD 00:00:00` (start) and `YYYY-MM-DD 23:59:59` (end)

### Event Listener Namespace
- Uses `.eventjs` namespace to prevent duplicate listeners
- Always removes existing listener before attaching: `.off('pageContentLoaded.eventjs').on('pageContentLoaded.eventjs', ...)`

### DataTable Initialization
- Always destroys existing instance before creating new one
- Initializes even on errors to show error message
- Uses `destroy: true` option to allow reinitialization

---

## Troubleshooting

### Events Not Loading
- Check browser console for errors
- Verify `org_id` is present in `data-id` attribute
- Check AJAX URL is correct (no leading slash)
- Verify backend route exists: `/organization/get-org-events`

### Date Filtering Not Working
- Verify `getEventsByOrgIdAndDateRange()` method exists in model
- Check date format being sent (should be `YYYY-MM-DD`)
- Verify database field is `event_start_date` (not `event_end_date`)

### Date Picker Not Appearing
- Verify `#demo` element exists in HTML
- Check daterangepicker.js is loaded
- Check moment.js is loaded
- Verify initialization code runs after DOM ready

### Duplicate Event Listeners
- Ensure using namespaced events: `pageContentLoaded.eventjs`
- Always use `.off()` before `.on()`

---

## Verification Checklist

After restoration, verify:
- [ ] Table structure has `<tr>` wrapper in `<thead>`
- [ ] Date picker input field exists with `id="demo"`
- [ ] `getEvents()` method accepts `start_date` and `end_date`
- [ ] `getEventsByOrgIdAndDateRange()` method exists in model
- [ ] `getOrgEvents()` function accepts `startDate` and `endDate` parameters
- [ ] `initializeDateRangePicker()` function exists
- [ ] Initialization code runs on page load
- [ ] All CSS styles are added
- [ ] All required libraries are loaded
- [ ] AJAX URL has no leading slash
- [ ] Event listener uses namespace `.eventjs`

---

## Summary

This document contains **complete, copy-paste ready code** for full restoration. If all code is lost, follow the installation steps above to recreate the entire implementation.

**Files Modified**: 5 files
**New Methods**: 1 (getEventsByOrgIdAndDateRange)
**New Functions**: 1 (initializeDateRangePicker)
**CSS Additions**: ~300 lines
**JavaScript Changes**: ~200 lines

All code is production-ready and tested.


