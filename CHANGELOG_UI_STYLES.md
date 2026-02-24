# UI/Style Modifications - Summary

Last updated: 2026-02-24

## Scope
This document summarizes the current UI/style-related updates made for admin and organization pages, focused on modal layout, form alignment, and table/filter consistency.

## High-Level Changes
1. Fixed modal width/position overlap behavior so admin-specific modal sizing does not affect organization pages.
2. Removed hardcoded inline input widths that caused uneven form alignment in organization event forms.
3. Unified organization table/filter presentation with the admin table-page card style (table pages only).
4. Added a clear action for organization date filtering to match toolbar behavior and improve UX.

## File-by-File Changes

### `public/css/admin/events-view.css`
- Scoped `#orgModal .modal-content` width overrides to admin context only:
  - From global selector to:
    - `.sidebar[data-access-id]:not([data-access-id="0"]) ~ .main-content #orgModal .modal-content`
- Applied the same scope in responsive media query blocks.
- Result: prevents modal layout conflicts on organization pages.

### `app/Views/organization/pages/hostEvent.php`
- Removed inline `style="width: ..."` from:
  - `event-participants` inputs
  - `start-date` and `end-date` inputs
- Result: form width now follows shared CSS rules for consistent alignment and responsiveness.

### `public/css/organization/events.css`
- Removed generic rule:
  - `.form-input div input { width: 40%; display: inline; }`
- Result: avoids forced narrow inputs that caused overlap/misalignment in date and form rows.

### `app/Views/organization/pages/trackEvent.php`
- Refactored table/filter page structure to use admin-style wrappers:
  - Added `admin-page-toolbar-card org-page-toolbar-card`
  - Added `admin-toolbar-grid org-track-toolbar-grid`
  - Moved date range control into toolbar section
  - Added `Clear` button (`#track-events-clear-btn`)
  - Updated table wrapper to `admin-page-table-card org-page-table-card`
  - Added `org-table` class to `#myEventTable`
  - Normalized `<thead>` to include a `<tr>`
- Result: organization table/filter area now visually matches admin table pages.

### `public/css/organization/homepage.css`
- Added scoped styles for new organization toolbar layer:
  - `.admin-page-toolbar-card.org-page-toolbar-card`
  - `.org-page-toolbar-card .org-track-toolbar-grid`
  - `.org-track-date-group`, `.org-track-date-label`
  - `.org-track-date-input-wrap` and icon/caret styling
  - `.org-track-clear-btn`
  - responsive behavior at `max-width: 900px`
- Result: organization filter controls now follow the same card/toolbar visual language as admin table pages.

### `public/js/organization/event.js`
- Added clear button logic for new toolbar button:
  - Binds `#track-events-clear-btn`
  - Clears `#demo` date range input
  - Resets picker state and reloads unfiltered event list via `window.getOrgEvents()`
- Result: filter UX stays consistent with the new toolbar and remains functional.

## Copy/Paste File List for Other Repo
1. `public/css/admin/events-view.css`
2. `app/Views/organization/pages/hostEvent.php`
3. `public/css/organization/events.css`
4. `app/Views/organization/pages/trackEvent.php`
5. `public/css/organization/homepage.css`
6. `public/js/organization/event.js`

## Notes
- No backend controller/model/database schema changes are included in this set.
- Logic updates in JS are limited to date-filter clear interaction for UI consistency.

---

## Consistency Update (Admin vs Org/Student Table Pages)

### Goal
Make organization/student table pages use the same table/filter card design language as admin table pages.

### Added/Updated

#### `app/Views/organization/pages/homepage.php`
- Converted event table area to admin-style structure:
  - Added toolbar card for date range + clear
  - Kept table card with title/hint
  - Set table class to `org-table` for shared table styling

#### `app/Views/organization/pages/leaderboards.php`
- Converted leaderboard controls to admin-style toolbar:
  - Added search input (`#org-leaderboard-search-input`)
  - Kept period filter (`#leaderboard-filter`) with admin select styling
  - Added clear button (`#org-leaderboard-clear-btn`)
- Updated table card/header to same admin pattern.
- Set leaderboard table class to `org-table`.

#### `public/js/org.js`
- Added shared handlers for admin + org leaderboard toolbar controls:
  - Search now supports `#admin-leaderboard-search-input` and `#org-leaderboard-search-input`
  - Clear now supports `#admin-leaderboard-clear-btn` and `#org-leaderboard-clear-btn`
- Behavior is matched across both sides (search reset + period reset + DataTable search reset).

#### `public/css/leaderboard.css`
- Hid DataTables default search for org leaderboard table wrapper when using toolbar search:
  - `.admin-page-table-card #org-leaderboard-table_wrapper .dataTables_filter { display: none; }`

#### `public/css/organization/homepage.css`
- Updated my-events row hover colors to the same neutral admin-like hover tone.
- Reused/extended toolbar styling block used by org table pages.

### Result
- Admin `Manage Events` / `Leaderboards` and org/student `Home` / `Leaderboards` / `My Events` now follow the same table-page layout pattern:
  - title card
  - toolbar card
  - table card
  - consistent DataTables surface styling
