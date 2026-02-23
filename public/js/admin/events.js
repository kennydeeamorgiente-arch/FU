$(document).ready(function () {
  function hasManageEventsTable() {
    return $("#manage-events-table").length > 0;
  }

  function hasCalendarContainer() {
    return document.getElementById("calendar") !== null;
  }

  function shouldLoadEventsData() {
    return hasManageEventsTable() || hasCalendarContainer();
  }

  function openAdminEventModal(eventId) {
    if (!eventId) return;

    var modal = $("#orgModal");
    if (modal.length > 0) {
      $("#modalTitle").text("Event Details");
      $("#modalBody").html(
        '<div style="text-align: center; padding: 50px;"><i class="fa-solid fa-spinner fa-spin fa-2x"></i><p>Loading event...</p></div>',
      );
      modal.addClass("show");

      $("#modalBody").load(
        `${BASE_URL}admin/manage-events/view/${eventId}`,
        function (response, status, xhr) {
          if (status == "success") {
            $("#modalBody #btn-back-to-events").hide();
            approvalInProgress = false;
            lastProcessedEventId = null;
          } else {
            $("#modalBody").html(
              '<div style="text-align: center; padding: 50px;"><p style="color: red;">Error loading event. Please try again.</p><button onclick="$(\'#orgModal\').removeClass(\'show\');" style="padding: 10px 20px; background: #8b0000; color: white; border: none; border-radius: 5px; cursor: pointer; margin-top: 20px;">Close</button></div>',
            );
            console.error("Error loading event:", xhr.status, xhr.statusText);
          }
        },
      );
    }
  }

  // STATUS FILTERING - Fixed to work with DataTables
  $(".status-box").click(function () {
    if (!hasManageEventsTable() || !$.fn.DataTable.isDataTable("#manage-events-table")) {
      return;
    }

    const filterStatus = $(this).data("status");
    const table = $("#manage-events-table").DataTable();

    // Toggle active class
    if ($(this).hasClass("active")) {
      $(this).removeClass("active");
      // Clear filter - show all rows
      table.column(1).search("").draw();
    } else {
      $(".status-box").removeClass("active");
      $(this).addClass("active");

      // Filter by exact status match using regex
      table
        .column(1)
        .search("^" + filterStatus + "$", true, false)
        .draw();
    }
  });

  $(document).on("click", "#manage-events-table .event-row", function () {
    let eventId = $(this).data("id");
    openAdminEventModal(eventId);
  });

  // SessionStorage loading - for navigation from notifications
  let eventIdToLoad = sessionStorage.getItem("loadEventId");
  if (eventIdToLoad) {
    sessionStorage.removeItem("loadEventId");

    openAdminEventModal(eventIdToLoad);
  }

  // Close modal function
  window.closeAdminEventModal = function () {
    $("#orgModal").removeClass("show");
    $("#modalBody").empty();
    // Reset approval state when closing modal
    approvalInProgress = false;
    lastProcessedEventId = null;
  };

  //CALENDAR - Not affected by semester filtering
  let calendar;
  var modal = $("#orgModal");
  let pending = [];
  let inProgress = [];
  let awaitingDocumentation = [];
  let forVerification = [];
  let completedRejected = [];
  let allCalendarEvents = []; // Store all events for calendar (normalized)
  let currentSemesterFilter = "annual"; // default
  let allEvents = []; // store original data

  function toCalendarEvent(event) {
    if (!event || !event.event_start_date) return null;

    const normalized = {
      id: String(event.event_id),
      title: event.event_name || "Untitled Event",
      start: event.event_start_date,
      allDay: true,
      extendedProps: {
        org_name: event.org_name || "",
        status_name: event.status_name || "",
        access_name: event.access_name || "",
      },
    };

    if (event.event_end_date) {
      // FullCalendar end date is exclusive for allDay events.
      const endDate = new Date(event.event_end_date);
      if (!isNaN(endDate.getTime())) {
        endDate.setDate(endDate.getDate() + 1);
        normalized.end = endDate.toISOString().slice(0, 10);
      }
    }

    return normalized;
  }

  function normalizeCalendarEvents(events) {
    if (!Array.isArray(events)) return [];
    return events.map(toCalendarEvent).filter((event) => event !== null);
  }

  function formatDateRange(startDate, endDate) {
    const safeStart = startDate ? new Date(startDate) : null;
    const safeEnd = endDate ? new Date(endDate) : null;

    if (!safeStart || isNaN(safeStart.getTime())) {
      return "Date not available";
    }

    const startText = safeStart.toLocaleDateString("en-US", {
      month: "short",
      day: "numeric",
      year: "numeric",
    });

    if (!safeEnd || isNaN(safeEnd.getTime()) || startText === safeEnd.toLocaleDateString("en-US", {
      month: "short",
      day: "numeric",
      year: "numeric",
    })) {
      return startText;
    }

    const endText = safeEnd.toLocaleDateString("en-US", {
      month: "short",
      day: "numeric",
      year: "numeric",
    });

    return `${startText} - ${endText}`;
  }

  function updateDashboardBento(events) {
    const hasBento = $("#dashboard-upcoming-list").length > 0;
    if (!hasBento || !Array.isArray(events)) return;

    const now = new Date();
    const sorted = [...events].sort((a, b) => {
      const aDate = new Date(a.event_start_date || 0).getTime();
      const bDate = new Date(b.event_start_date || 0).getTime();
      return aDate - bDate;
    });

    const focusEvent =
      sorted.find((event) => new Date(event.event_start_date || 0).getTime() >= now.getTime()) ||
      sorted[0] ||
      null;

    const statMap = {
      pending: 0,
      progress: 0,
      awaiting: 0,
      verification: 0,
      completed: 0,
    };

    sorted.forEach((event) => {
      switch (event.status_name) {
        case "Pending":
          statMap.pending += 1;
          break;
        case "In-Progress":
          statMap.progress += 1;
          break;
        case "Awaiting Documentation":
          statMap.awaiting += 1;
          break;
        case "For Verification":
          statMap.verification += 1;
          break;
        case "Completed":
          statMap.completed += 1;
          break;
      }
    });

    $("#dashboard-stat-pending").text(statMap.pending);
    $("#dashboard-stat-progress").text(statMap.progress);
    $("#dashboard-stat-awaiting").text(statMap.awaiting);
    $("#dashboard-stat-verification").text(statMap.verification);
    $("#dashboard-stat-completed").text(statMap.completed);

    if (focusEvent) {
      $("#dashboard-focus-name").text(focusEvent.event_name || "Untitled Event");
      $("#dashboard-focus-meta").text(
        `${focusEvent.org_name || "Unknown Org"} | ${formatDateRange(
          focusEvent.event_start_date,
          focusEvent.event_end_date,
        )}`,
      );
      $("#dashboard-focus-open").attr("data-id", focusEvent.event_id || "");
    } else {
      $("#dashboard-focus-name").text("No upcoming events");
      $("#dashboard-focus-meta").text("Waiting for event submissions");
      $("#dashboard-focus-open").attr("data-id", "");
    }

    const $upcomingList = $("#dashboard-upcoming-list");
    $upcomingList.empty();

    if (sorted.length === 0) {
      $upcomingList.append('<li class="empty">No scheduled activities.</li>');
      return;
    }

    sorted.slice(0, 6).forEach((event) => {
      const safeName = $("<div>").text(event.event_name || "Untitled Event").html();
      const safeMeta = $("<div>")
        .text(`${event.org_name || "Unknown Org"} | ${formatDateRange(event.event_start_date, event.event_end_date)}`)
        .html();

      $upcomingList.append(`
        <li>
          <button type="button" class="bento-upcoming-item" data-id="${event.event_id}">
            <strong>${safeName}</strong>
            <span>${safeMeta}</span>
          </button>
        </li>
      `);
    });
  }

  function initCalendar() {
    var calendarEl = document.getElementById("calendar");
    if (!calendarEl) return;

    if (calendar) {
      calendar.destroy();
      calendar = null;
    }

    calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: "dayGridMonth",
      selectable: true,
      height: "auto",
      events: allCalendarEvents || [],
      eventColor: "#8B0000",
      eventClick: function (info) {
        info.jsEvent.preventDefault();
        openAdminEventModal(info.event.id);
      },
    });

    calendar.render();
  }

  function getEvents() {
    if (!shouldLoadEventsData()) return;

    $.ajax({
      url: BASE_URL + "admin/get-events",
      method: "GET",
      dataType: "json",
      cache: false,
      success: function (response) {
        if (response.status === "success") {
          allEvents = Array.isArray(response.data) ? response.data : [];

          // Build status arrays
          pending = [];
          inProgress = [];
          awaitingDocumentation = [];
          forVerification = [];
          completedRejected = [];

          allEvents.forEach((event) => {
            switch (event.status_name) {
              case "Pending":
                pending.push(event);
                break;
              case "In-Progress":
                inProgress.push(event);
                break;
              case "Awaiting Documentation":
                awaitingDocumentation.push(event);
                break;
              case "For Verification":
                forVerification.push(event);
                break;
              case "Completed":
              case "Rejected":
                completedRejected.push(event);
                break;
            }
          });

          allCalendarEvents = normalizeCalendarEvents(allEvents);

          if (hasManageEventsTable()) {
            renderEvents(allEvents);
          }

          if (hasCalendarContainer()) {
            initCalendar();
            updateDashboardBento(allEvents);
          }
        }
      },
    });
  }

  if (shouldLoadEventsData()) {
    getEvents();
  }

  $(document)
    .off("pageContentLoaded.adminEventsFetch")
    .on("pageContentLoaded.adminEventsFetch", function () {
      if (shouldLoadEventsData()) {
        getEvents();
      }
    });

  $(document).on("click", "#dashboard-focus-open, .bento-upcoming-item", function () {
    const eventId = $(this).data("id");
    if (eventId) {
      openAdminEventModal(eventId);
    }
  });

  function renderEvents(data) {
    if (!hasManageEventsTable()) return;

    let tableBody = $("#manage-events-table tbody");

    if ($.fn.DataTable.isDataTable("#manage-events-table")) {
      $("#manage-events-table").DataTable().destroy();
    }

    tableBody.empty();

    // Apply semester filter to table only (not calendar)
    let filteredData = filterBySemester(data);

    filteredData.forEach((event) => {
      let eventDate = new Date(event.event_start_date).toLocaleDateString(
        "en-US",
        {
          month: "long",
          day: "numeric",
          year: "numeric",
        },
      );

      let approvalLevel = event.access_name || "N/A";

      if (event.status_name === "Pending" && event.current_access_id) {
        approvalLevel += ` (Level ${event.current_access_id})`;
      }

      tableBody.append(`
      <tr class="event-row"
          data-id="${event.event_id}"
          data-value="${event.status_name}">
        <td>${eventDate}</td>
        <td>${event.status_name}</td>
        <td>${event.access_name || "N/A"}</td>
        <td>${event.event_name}</td>
        <td>${event.org_name}</td>
        <td>${approvalLevel}</td>
      </tr>
    `);
    });

    if (hasManageEventsTable()) {
      const table = $("#manage-events-table").DataTable({
        responsive: true,
        paging: true,
        searching: true,
        info: true,
      });
      bindManageEventsToolbar(table);
    }
  }

  function bindManageEventsToolbar(table) {
    if (!table || $("#manage-events-search-input").length === 0) return;

    function applyFilters() {
      const keyword = ($("#manage-events-search-input").val() || "").trim();
      const status = ($("#manage-events-status-select").val() || "").trim();

      table.search(keyword);
      if (status) {
        table.column(1).search("^" + status.replace(/[.*+?^${}()|[\]\\]/g, "\\$&") + "$", true, false);
      } else {
        table.column(1).search("");
      }
      table.draw();
    }

    $("#manage-events-search-input")
      .off("input.manageEventsToolbar")
      .on("input.manageEventsToolbar", applyFilters);

    $("#manage-events-status-select")
      .off("change.manageEventsToolbar")
      .on("change.manageEventsToolbar", applyFilters);

    $("#manage-events-clear-btn")
      .off("click.manageEventsToolbar")
      .on("click.manageEventsToolbar", function () {
        $("#manage-events-search-input").val("");
        $("#manage-events-status-select").val("");
        applyFilters();
      });
  }

  function filterBySemester(data) {
    let today = new Date();
    let currentYear = today.getFullYear();
    let currentMonth = today.getMonth() + 1;

    let academicStartYear = currentMonth >= 8 ? currentYear : currentYear - 1;
    let academicEndYear = academicStartYear + 1;
    console.log("Acad year: " + academicStartYear + " - " + academicEndYear);

    return data.filter((event) => {
      if (!event.event_start_date) return false;

      let date = new Date(event.event_start_date);

      // Validate date
      if (isNaN(date.getTime())) return false;

      let month = date.getMonth() + 1;
      let year = date.getFullYear();

      switch (currentSemesterFilter) {
        case "first":
          return year === academicStartYear && month >= 8 && month <= 12;

        case "second":
          return year === academicEndYear && month >= 1 && month <= 5;

        case "summer":
          return year === academicEndYear && month >= 6 && month <= 7;

        case "annual":
        default:
          return (
            (year === academicStartYear && month >= 8) ||
            (year === academicEndYear && month <= 7)
          );
      }
    });
  }

  // SEMESTER FILTER BUTTON HANDLERS
  $("#filter-annual, #filter-first, #filter-second, #filter-summer").on("click", function () {
    $("#filter-annual, #filter-first, #filter-second, #filter-summer").removeClass("active");
    $(this).addClass("active");
  });

  $("#filter-annual").on("click", function () {
    currentSemesterFilter = "annual";
    renderEvents(allEvents);
  });

  $("#filter-first").on("click", function () {
    currentSemesterFilter = "first";
    renderEvents(allEvents);
  });

  $("#filter-second").on("click", function () {
    currentSemesterFilter = "second";
    renderEvents(allEvents);
  });

  // ADDED: Missing summer semester filter handler
  $("#filter-summer").on("click", function () {
    currentSemesterFilter = "summer";
    renderEvents(allEvents);
  });

  // function renderTable(data, tableName) {
  //   // console.log(`Data For: ${tableName}: ${data[0].event_id}`);
  //   let tableBody = $(`${tableName} tbody`);
  //   tableBody.empty();
  //   $.each(data, function (index, event) {
  //     tableBody.append(`
  //       <tr class="event-row" data-id="${event.event_id}">
  //         <td>${new Date(event.event_start_date).toLocaleDateString("en-US", {
  //           month: "long",
  //           day: "numeric",
  //           year: "numeric",
  //         })}</td>
  //         <td>${event.status_name}</td>
  //         <td>${event.access_name}</td>
  //         <td>${event.event_name}</td>
  //         <td>
  //           ${event.org_name}
  //         </td>
  //         <td>${event.current_access_id}</td>
  //       </tr>
  //     `);
  //   });
  // }

  let responseType = "";
  let approvalInProgress = false;
  let lastProcessedEventId = null; // Track last processed event to prevent duplicates

  // Remove ALL existing handlers to prevent duplicates
  $(document).off("click", ".response-buttons button");
  $(document).off("click", "#btn-accept");
  $(document).off("click", "#btn-reject");
  $(document).off("click", "#btn-revision");
  $(document).off("click", "#btn-submit");
  $(document).off("click", "#btn-cancel");

  // Remove ALL modal-specific handlers
  $(document).off("click", "#orgModal .response-buttons button");
  $(document).off("click", "#orgModal #btn-accept");
  $(document).off("click", "#orgModal #btn-reject");
  $(document).off("click", "#orgModal #btn-revision");
  $(document).off("click", "#orgModal #btn-submit");
  $(document).off("click", "#orgModal #btn-cancel");

  // Test if submit button exists when modal loads
  setTimeout(function () {
    let submitButton = $("#btn-submit");
    let approveButton = $("#btn-accept");
    let rejectButton = $("#btn-reject");
    let revisionButton = $("#btn-revision");

    console.log("=== FINAL BUTTON CHECK ===");
    console.log("Submit button found:", submitButton.length > 0 ? "YES" : "NO");
    console.log(
      "Approve button found:",
      approveButton.length > 0 ? "YES" : "NO",
    );
    console.log("Reject button found:", rejectButton.length > 0 ? "YES" : "NO");
    console.log(
      "Revision button found:",
      revisionButton.length > 0 ? "YES" : "NO",
    );

    if (submitButton.length > 0) {
      // Add IMMEDIATE direct click test - no delays
      submitButton.on("click", function (e) {
        console.log("🔥🔥🔥 FINAL CLICK - Submit button clicked!");
        console.log("Event data:", {
          id: $(this).data("id"),
          name: $(this).data("name"),
          user: $(this).data("user"),
        });

        // DIRECT AJAX CALL - bypass all other logic
        let eventId = $(this).data("id");
        let eventName = $(this).data("name");
        let userId = $(this).data("user");

        console.log("🚀 FINAL AJAX CALL...");
        $.ajax({
          url: BASE_URL + "admin/update-events",
          method: "POST",
          data: {
            event_id: eventId,
            remarks: "Approved via final call",
            status_id: 8, // Approve
            user_id: userId,
            event_name: eventName,
          },
          dataType: "json",
          success: function (response) {
            console.log("✅ FINAL AJAX SUCCESS:", response);
            if (response.status == "success") {
              alert("Event approved successfully!");
              $("#orgModal").removeClass("show");
              location.reload();
            }
          },
          error: function (xhr, status, error) {
            console.log("❌ FINAL AJAX ERROR:", error);
            alert("Error approving event. Please try again.");
          },
        });
      });
    }
  }, 2000); // Check after 2 seconds

  // FINAL CLEAN HANDLERS - Bind only ONCE
  $(document).on("click", ".response-buttons button", function (e) {
    let buttonId = $(this).attr("id");
    console.log("FINAL - Button clicked:", buttonId);

    // Set response type
    responseType = buttonId;
    console.log("FINAL - Response type set to:", responseType);

    // For revision/reject, show remarks and make required
    if (buttonId === "btn-revision" || buttonId === "btn-reject") {
      $(".response-remarks").show();
      $("#remarks").attr("required", true);
      $("#remarks").attr(
        "placeholder",
        "Remarks are required for Revision/Rejection...",
      );
      console.log("FINAL - Showing remarks for revision/reject");
    } else if (buttonId === "btn-accept") {
      $(".response-remarks").hide();
      $("#remarks").attr("required", false);
      console.log("FINAL - Hiding remarks for approve");

      // AUTO-TRIGGER SUBMIT after approve - no need to click submit button
      setTimeout(function () {
        console.log("🚀 AUTO-TRIGGERING SUBMIT after approve...");
        $("#btn-submit").trigger("click");
      }, 500);
    }

    // Highlight active button
    $(".response-buttons button").removeClass("active");
    $(this).addClass("active");
  });

  // Cancel button handler - FINAL
  $(document).on("click", "#btn-cancel", function () {
    $(".response-remarks").hide();
    $(".response-buttons button").removeClass("active");
    responseType = "";
    $("#remarks").val("");
  });

  // Submit button handler - FINAL
  $(document).on("click", "#btn-submit", function () {
    console.log("FINAL - Submit button clicked");
    console.log("FINAL - Current response type:", responseType);

    // Check if response type is selected
    if (!responseType) {
      alert(
        "Please select an action first (Approve, Reject, or Return for Revision).",
      );
      return false;
    }

    let remarks = $("#remarks").val();
    let status_id = 0;
    let event_id = $(this).data("id");
    let event_name = $(this).data("name");
    let user_id = $(this).data("user");

    console.log("FINAL - Submit data:", {
      event_id,
      remarks,
      status_id,
      user_id,
      event_name,
      responseType,
    });

    // Validate required data
    if (!event_id) {
      console.error("FINAL - Event ID missing!");
      alert("Error: Event ID not found.");
      return false;
    }

    if (!user_id) {
      console.error("FINAL - User ID missing!");
      alert("Error: User ID not found.");
      return false;
    }

    // Determine status_id based on response type
    switch (responseType) {
      case "btn-revision":
        status_id = 7;
        // Remarks required for revision
        if (!remarks || remarks.trim() === "") {
          alert("Please add remarks for revision.");
          return false;
        }
        break;
      case "btn-reject":
        status_id = 6;
        // Remarks required for rejection
        if (!remarks || remarks.trim() === "") {
          alert("Please add remarks for rejection.");
          return false;
        }
        break;
      case "btn-accept":
        status_id = 8;
        // Remarks optional for approval, but show confirmation
        let eventName = event_name || "this event";
        if (!confirm(`Are you sure you want to approve "${eventName}"?`)) {
          return false;
        }
        // Set flag to prevent duplicate approvals
        approvalInProgress = true;
        $(this).prop("disabled", true);
        break;
      default:
        console.log("FINAL - No response type selected, returning");
        alert(
          "Please select an action (Approve, Reject, or Return for Revision).",
        );
        return false;
    }

    console.log("FINAL - Final data for update:", {
      event_id,
      remarks,
      status_id,
      user_id,
      event_name,
    });
    console.log("🚀 FINAL - About to call updateEvent function...");

    // Call update function directly
    try {
      updateEvent(event_id, remarks || "", status_id, user_id, event_name);
      console.log("✅ FINAL - updateEvent function called successfully");
    } catch (error) {
      console.error("❌ FINAL - Error calling updateEvent:", error);
      alert("Error updating event. Please check console for details.");
    }

    $(".response-remarks").removeClass("active");
    $(".response-buttons button").removeClass("active");
    responseType = "";
    $("#remarks").val(""); // Clear remarks field
  });

  function updateEvent(event_id, remarks, status_id, user_id, event_name) {
    // Prevent duplicate calls for the same event
    let eventKey = event_id + "_" + status_id;
    if (lastProcessedEventId === eventKey && approvalInProgress) {
      console.log("Duplicate updateEvent call prevented for event:", event_id);
      return;
    }

    // Mark as processing
    lastProcessedEventId = eventKey;

    console.log("🚀 Making AJAX call to update event...");
    console.log("📤 URL:", BASE_URL + "admin/update-events");
    console.log("📤 Data:", {
      event_id,
      remarks,
      status_id,
      user_id,
      event_name,
    });

    // Test direct AJAX call without validation
    $.ajax({
      url: BASE_URL + "admin/update-events",
      method: "POST",
      data: {
        event_id: event_id,
        remarks: remarks || "Test remarks",
        status_id: 8, // Force approve for testing
        user_id: user_id || 1, // Default user for testing
        event_name: event_name || "Test Event",
      },
      dataType: "json",
      beforeSend: function () {
        console.log("📡 Sending AJAX request...");
      },
      success: function (response) {
        console.log("✅ AJAX Response received:", response);
        if (response.status == "success") {
          console.log("✅ Update successful - showing alert");
          // Show success message only once
          alert("Event updated successfully!");

          // Close modal if open
          if ($("#orgModal").hasClass("show")) {
            closeAdminEventModal();
          }

          // Re-enable button and reset flags
          approvalInProgress = false;
          lastProcessedEventId = null; // Reset to allow future updates
          $(".response-buttons button").prop("disabled", false);

          // Refresh the events table using AJAX only (no page reload)
          if ($(".admin-events").length > 0) {
            $("#manage-events-table tbody").empty();
            if ($.fn.DataTable.isDataTable(".admin-events")) {
              $(".admin-events").DataTable().destroy();
            }
            setTimeout(function () {
              getEvents();
            }, 100);
          } else {
            window.location.href = BASE_URL + "admin/manage-events";
          }
        } else {
          console.log("❌ Update failed:", response);
          // Reset approval flag on error and re-enable button
          approvalInProgress = false;
          lastProcessedEventId = null;
          $(".response-buttons button").prop("disabled", false);
          alert("Error: " + response.message);
        }
      },
      error: function (xhr, status, error) {
        console.log("❌ AJAX Error:", { xhr, status, error });
        // Reset approval flag on error and re-enable button
        approvalInProgress = false;
        lastProcessedEventId = null;
        $(".response-buttons button").prop("disabled", false);
        console.error("Update error:", error);
        alert("Error updating event. Please try again.");
      },
    });
  }

  // MODAL

  $(document).on("click", "#btn-approve", function () {
    let eventId = $(this).data("id");

    // Store the event ID inside the modal for later use
    $("#points-modal").attr("data-event-id", eventId);

    // Show modal
    $("#points-modal").addClass("show");
  });

  // Close button click for points modal
  $(document).on("click", ".modal-close, .btn-modal-cancel", function () {
    $("#points-modal").removeClass("show");
  });

  // Close orgModal when clicking close button or outside
  $(document).on("click", "#orgModal .modal-close", function () {
    $("#orgModal").removeClass("show");
  });

  // Close orgModal when clicking outside the modal content
  $(document).on("click", "#orgModal", function (e) {
    if ($(e.target).is("#orgModal")) {
      $("#orgModal").removeClass("show");
    }
  });

  // Close orgModal when clicking close button
  $(document).on("click", "#orgModal .modal-close", function () {
    closeAdminEventModal();
  });

  // Close orgModal when clicking outside the modal content
  $(document).on("click", "#orgModal", function (e) {
    if ($(e.target).is("#orgModal")) {
      closeAdminEventModal();
    }
  });

  $(document).on("click", "#btn-view-event", function () {
    let orgId = $(this).data("id");
    window.location.href = `${BASE_URL}/admin/manage-events`;
  });

  $(document).off("click", "#btn-points-confirm");

  $(document).on("click", "#btn-points-confirm", function () {
    let eventId = $("#points-modal").attr("data-event-id");
    let selectedPoints = $("input[name='points']:checked").val();

    console.log(selectedPoints);
    if (!selectedPoints) {
      alert("Please select a point value");
      return;
    }

    // AJAX request to update the points
    $.ajax({
      url: `${BASE_URL}/admin/approve-event`,
      method: "POST",
      data: {
        event_id: eventId,
        points: selectedPoints,
      },
      success: function (response) {
        if (response.status === "success") {
          // Close modal
          $("#points-modal").fadeOut(200);
          $("#points-modal").removeClass("show");

          // Hide the approve button
          $("#btn-approve").hide();

          // Update UI points text on the page
          $(".response-final-approval h5").text(
            "Points allocated successfully!",
          );

          // Show success message ONCE
          alert("Points allocated successfully!");

          // Optional: Reload the page to reflect changes
          // location.reload();
        } else {
          alert(response.message || "Something went wrong.");
        }
      },
      error: function (xhr, status, error) {
        console.error("Error:", error);
        alert("Something went wrong.");
      },
    });
  });
});
