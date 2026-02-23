// BASE_URL is defined in config.js

// Variable to prevent duplicate delete submissions (check if already declared)
if (typeof deleteInProgress === "undefined") {
  var deleteInProgress = false;
}

// Generate event schedule table based on date range
function generateEventSchedule() {
  const startDateInput = $('input[name="start-date"]').val();
  const endDateInput = $('input[name="end-date"]').val();

  if (!startDateInput || !endDateInput) {
    return;
  }

  const startDate = new Date(startDateInput);
  const endDate = new Date(endDateInput);

  // Validate that end date is after start date
  if (endDate < startDate) {
    alert("End date must be after start date");
    return;
  }

  // Calculate days between
  const daysDifference =
    Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;

  let tableHTML = `
    <thead>
      <tr style="background-color: #8b0000; color: white;">
        <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Day</th>
        <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Date</th>
        <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Activities/Notes</th>
      </tr>
    </thead>
    <tbody>
  `;

  // Generate rows for each day
  for (let i = 0; i < daysDifference; i++) {
    const currentDate = new Date(startDate);
    currentDate.setDate(currentDate.getDate() + i);

    const dateString = currentDate.toLocaleDateString("en-US", {
      year: "numeric",
      month: "short",
      day: "numeric",
    });

    const dayNumber = i + 1;

    tableHTML += `
      <tr style="border: 1px solid #ddd;">
        <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold;">Day ${dayNumber}</td>
        <td style="padding: 10px; border: 1px solid #ddd;">${dateString}</td>
        <td style="padding: 10px; border: 1px solid #ddd;">
          <input type="text" name="day-${i}-activities" placeholder="Enter activities for this day" style="width: 100%; padding: 5px; border: 1px solid #ccc; border-radius: 4px;">
        </td>
      </tr>
    `;
  }

  tableHTML += `
    </tbody>
  `;

  $("#eventScheduleTable").html(tableHTML);
}

window.showOrgEvents = function () {
  getOrgEvents();
};
$(document).on("change", 'input[name="event-collab-bool"]', function () {
  let selected = $('input[name="event-collab-bool"]:checked');

  if (selected.val() == "yes") {
    if ($(".group-collaborator").hasClass("hide")) {
      $(".group-collaborator").removeClass("hide");
    }
  } else {
    $(".group-collaborator").addClass("hide");
    console.log(selected.val());
  }
});

let selected = $('input[name="event-collab-bool"]:checked');

if (selected.val() == "yes") {
  if ($(".group-collaborator").hasClass("hide")) {
    $(".group-collaborator").removeClass("hide");
  }
} else {
  $(".group-collaborator").addClass("hide");
  console.log(selected.val());
}

$(document).on("click", "#event-add-row", function (e) {
  e.stopPropagation();
  e.preventDefault();
  $("#event-add-row").before(`
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
          <input type="text" name="purpose[]"  required>
        </td>
        <td>
          <input type="number" name="unit-price[]" required>
        </td>
        <td>
          <input type="number" name="amount[]"  required>
        </td>
    </tr>
      `);
});

$(document).on(
  "input",
  "input[name='unit-price[]'], input[name='qty[]']",
  function () {
    let row = $(this).closest(".budget-item-row");
    let total = 0;
    let price = parseFloat(row.find("input[name='unit-price[]']").val()) || 0;
    let qty = parseFloat(row.find("input[name='qty[]']").val()) || 0;

    row.find("input[name='amount[]']").val(price * qty);

    $("input[name='amount[]']").each(function () {
      total += parseFloat($(this).val()) || 0;
    });

    $("input[name='estimated-budget']").val(total);
  },
);

$(document).on("submit", "#host-event-form", function (e) {
  e.preventDefault();

  const form = $("#host-event-form")[0];
  const formData = new FormData(form);
  const submitButton = $(form).find('button[type="submit"]');
  const originalText = submitButton.text();

  // Disable submit button to prevent double submission
  submitButton.prop("disabled", true).text("Adding...");

  $.ajax({
    url: `${BASE_URL}organization/host-event/add-event`,
    data: formData,
    method: "POST",
    contentType: false,
    processData: false,
    success: function (response) {
      submitButton.prop("disabled", false).text(originalText);

      if (response.status === "success") {
        alert("Event Successfully added!");
        form.reset();

        // Refresh events table
        if (typeof getOrgEvents === "function") {
          getOrgEvents();
        } else if (typeof window.getOrgEvents === "function") {
          window.getOrgEvents();
        }

        // If on track-event page, refresh the table
        if ($("#track-events-tbody").length > 0) {
          if (typeof getOrgEvents === "function") {
            getOrgEvents();
          }
        }
      } else {
        alert(response.message || "Something went wrong.");
      }
      console.log(response);
    },
    error: function (xhr, status, error) {
      submitButton.prop("disabled", false).text(originalText);
      console.error("Error adding event:", error);
      alert("An error occurred while adding the event. Please try again.");
    },
  });
});

$(document).on("submit", "#edit-event-form", function (e) {
  e.preventDefault();

  const form = $("#edit-event-form")[0];
  const formData = new FormData(form);
  const submitButton = $(form).find('button[type="submit"]');
  const originalText = submitButton.text();

  // Disable submit button to prevent double submission
  submitButton.prop("disabled", true).text("Updating...");

  $.ajax({
    url: `${BASE_URL}organization/host-event/edit-event`,
    data: formData,
    method: "POST",
    contentType: false,
    processData: false,
    success: function (response) {
      submitButton.prop("disabled", false).text(originalText);

      if (response.status === "success") {
        alert("Event successfully updated!");
        // Close modal
        $("#orgModal").removeClass("show");
        // Refresh events list
        if (typeof getOrgEvents === "function") {
          getOrgEvents();
        }
        // Also refresh if using the date-filtered version
        if (typeof window.getOrgEvents === "function") {
          window.getOrgEvents();
        }
      } else {
        alert(response.message || "Something went wrong.");
      }
      console.log(response);
    },
    error: function (xhr, status, error) {
      submitButton.prop("disabled", false).text(originalText);
      console.error("Error updating event:", error);
      alert("An error occurred while updating the event. Please try again.");
    },
  });
});

function getOrgEvents() {
  let org_id = $("#track-events-tbody").data("id");
  let tbody = $("#track-events-tbody");
  $.ajax({
    url: `${BASE_URL}organization/get-org-events`,
    method: "GET",
    data: { org_id: org_id },
    success: function (re) {
      if (re.status == "success") {
        tbody.empty();
        let data = re.data;

        data.forEach((event) => {
          tbody.append(`
              <tr class="event-row" data-id="${event.event_id}">
                <td>${event.event_start_date}</td>
                <td>${event.created_at}</td>
                <td>${event.event_name}</td>
                <td>${event.current_access_id}</td>
                <td>${event.status_name}</td>
                <td>
                  <div class="action-buttons">
                    <button class="action-btn view-btn" data-id="${
                      event.event_id
                    }" title="View Event">
                      <i class="fa-solid fa-magnifying-glass"></i>
                    </button>

                    ${
                      event.status_id == 6 || event.status_id == 7
                        ? `
                          <button class="action-btn delete-btn" data-id="${event.event_id}" title="Delete Event">
                            <i class="fa-solid fa-trash"></i>
                          </button>
                        `
                        : ``
                    }

                  </div>
                </td>
              </tr>
            `);
        });
      } else {
        console.log("ERROR");
      }
    },
  });
}

if ($("#track-events-tbody").length > 0) {
  getOrgEvents();
}

$(document).on(
  "click",
  "#track-events-tbody .event-row .action-buttons .view-btn",
  function (e) {
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();

    // Close any open modals first
    $("#orgModal").removeClass("show");

    let eventId = $(this).data("id");

    if (!eventId) {
      console.error("Event ID not found");
      alert("Error: Event ID not found");
      return false;
    }

    console.log("Loading event view for ID:", eventId);

    // Load event view directly into container instead of modal
    $(".container").load(
      `${BASE_URL}organization/manage-event/view-event/${eventId}`,
      function (response, status, xhr) {
        if (status == "error") {
          console.error("Error loading event:", xhr.status, xhr.statusText);
          alert("Error loading event details. Please try again.");
        } else {
          // Ensure modals are closed after loading
          $("#orgModal").removeClass("show");
          console.log("Event details loaded successfully");
        }
      },
    );

    return false;
  },
);

// Handle edit button click in view-event modal (for revision status)
$(document).on("click", "#edit-event-btn", function (e) {
  e.stopPropagation();
  e.preventDefault();

  let eventId = $(this).data("event-id");
  if (!eventId) {
    console.error("Event ID not found");
    alert("Error: Event ID not found");
    return;
  }

  var modal = $("#orgModal");

  // Update modal title
  $("#modalTitle").text("Edit Event");

  // Show loading state
  $("#modalBody").html(
    '<div style="text-align: center; padding: 50px;"><i class="fa-solid fa-spinner fa-spin fa-2x"></i><p>Loading edit form...</p></div>',
  );

  // Load edit form
  $("#modalBody").load(
    `${BASE_URL}organization/manage-event/edit-event/${eventId}`,
    function (response, status, xhr) {
      if (status == "error") {
        console.error("Error loading edit form:", xhr.status, xhr.statusText);
        $("#modalBody").html(
          '<div style="text-align: center; padding: 50px;">' +
            '<p style="color: red;">Error loading edit form. Please try again.</p>' +
            "<button onclick=\"$('#orgModal').removeClass('show');\" style=\"padding: 10px 20px; background: #8b0000; color: white; border: none; border-radius: 5px; cursor: pointer; margin-top: 20px;\">Close</button>" +
            "</div>",
        );
      } else if (status == "success") {
        console.log("Edit form loaded successfully");
        // Re-initialize any event-specific scripts if needed
      }
    },
  );
});
$(document).on(
  "click",
  "#track-events-tbody .event-row .action-buttons .edit-btn",
  function (e) {
    e.stopPropagation();
    let eventId = $(this).data("id");

    if (!eventId) {
      console.error("Event ID not found");
      alert("Error: Event ID not found");
      return;
    }

    // Load edit form in content wrapper using AJAX
    $("#content-wrapper").load(
      `${BASE_URL}organization/manage-event/edit-event/${eventId}`,
      function (response, status, xhr) {
        if (status == "error") {
          console.error("Error loading edit form:", xhr.status, xhr.statusText);
          alert("Error loading edit form. Please try again.");
        } else {
          // Re-initialize any form scripts if needed
          console.log("Edit form loaded successfully");
        }
      },
    );
  },
);

$(document).on("click", "#orgModal", function (e) {
  var modal = $("#orgModal");
  if ($(e.target).is(modal)) {
    modal.removeClass("show");
  }
});

// CLOSE MODAL (buttons)
$(document).on("click", ".modal-close", function () {
  $(this).closest(".modal").removeClass("show");
});

$(document).on("click", "#calendarModal", function (e) {
  var modal = $("#orgModal");
  if ($(e.target).is($(this))) {
    $(this).removeClass("show");
  }
});

// Delete event handler
$(document).on(
  "click",
  "#track-events-tbody .event-row .action-buttons .delete-btn",
  function (e) {
    e.stopPropagation();

    // Prevent duplicate submissions
    if (deleteInProgress) {
      return false;
    }

    let eventId = $(this).data("id");
    let eventRow = $(this).closest(".event-row");
    let eventName = eventRow.find("td:nth-child(3)").text(); // Get event name from table

    if (
      confirm(
        `Are you sure you want to delete the event "${eventName}"? This action cannot be undone.`,
      )
    ) {
      // Set flag to prevent duplicate submissions
      deleteInProgress = true;
      $(this).prop("disabled", true);

      $.ajax({
        url: `${BASE_URL}organization/delete-event`,
        method: "POST",
        data: { event_id: eventId },
        success: function (response) {
          deleteInProgress = false; // Reset flag

          if (response.status === "success") {
            alert("Event successfully deleted!");
            // Reload the events table
            if (typeof getOrgEvents === "function") {
              getOrgEvents();
            } else {
              // If function doesn't exist, reload the page
              location.reload();
            }
          } else {
            alert(
              response.message || "Failed to delete event. Please try again.",
            );
            // Re-enable button on error
            $(".delete-btn[data-id='" + eventId + "']").prop("disabled", false);
          }
        },
        error: function (xhr, status, error) {
          deleteInProgress = false; // Reset flag
          console.error("Error deleting event:", error);
          alert(
            "An error occurred while deleting the event. Please try again.",
          );
          // Re-enable button on error
          $(".delete-btn[data-id='" + eventId + "']").prop("disabled", false);
        },
      });
    }
  },
);
