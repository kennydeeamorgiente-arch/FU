// BASE_URL is defined in config.js
window.initOrgAutocomplete = initOrgAutocomplete;

function initOrgAutocomplete() {
  if (typeof $.fn.autocomplete === "undefined") {
    console.error(
      "jQuery UI is not loaded. Autocomplete functionality will not work.",
    );
    return;
  }

  // Remove any existing autocomplete instances first
  $(".org-search-name").each(function () {
    if ($(this).data("ui-autocomplete")) {
      $(this).autocomplete("destroy");
    }
    $(this).removeData("autocomplete-initialized");
  });

  $(document).on("focus", ".org-search-name", function () {
    const $input = $(this);

    // Prevent duplicate initialization
    if ($input.data("autocomplete-initialized")) {
      return;
    }

    $input
      .autocomplete({
        source: function (request, response) {
          $.ajax({
            url: BASE_URL + "organization/search-organizations",
            dataType: "json",
            data: {
              term: request.term,
            },
            success: function (data) {
              if (data.status === "success") {
                response(
                  $.map(data.data, function (item) {
                    return {
                      label: item.org_name,
                      value: item.org_name,
                      org_id: item.org_id,
                      description: item.description || "",
                    };
                  }),
                );
              } else {
                response([]);
              }
            },
            error: function () {
              response([]);
            },
          });
        },
        minLength: 2,
        select: function (event, ui) {
          const $collaboratorBlock = $(this).closest(".collaborator-block");

          // Store the org_id in a hidden field or data attribute
          // But display the org_name in the input
          $(this).val(ui.item.label); // Show the organization name
          $(this).data("org-id", ui.item.org_id); // Store the org_id in data attribute

          // Update the hidden input or create one to store org_id
          let $hiddenOrgId = $collaboratorBlock.find(
            'input[name="event-collab[]"]',
          );

          // We need to change the approach: store org_id separately
          // Check if there's already a hidden field for org_id
          let $orgIdField = $collaboratorBlock.find(".collab-org-id");
          if ($orgIdField.length === 0) {
            // Create hidden field for org_id
            $orgIdField = $(
              '<input type="hidden" class="collab-org-id" name="event-collab[]">',
            );
            $collaboratorBlock.append($orgIdField);
          }

          // Set org_id in the hidden field
          $orgIdField.val(ui.item.org_id);

          // Remove name attribute from the visible input so it doesn't get submitted
          $(this).removeAttr("name");

          // Set the description in the textarea and make it readonly
          const $descTextarea = $collaboratorBlock.find(
            'textarea[name="event-collab-desc[]"]',
          );
          $descTextarea.val(ui.item.description);
          $descTextarea.prop("readonly", true);
          $descTextarea.css("background-color", "#f5f5f5");

          return false;
        },
        focus: function (event, ui) {
          // Show the org name while focusing, not the ID
          $(this).val(ui.item.label);
          return false;
        },
        change: function (event, ui) {
          // If user cleared the field or typed invalid text
          if (!ui.item) {
            const $collaboratorBlock = $(this).closest(".collaborator-block");
            const $descTextarea = $collaboratorBlock.find(
              'textarea[name="event-collab-desc[]"]',
            );

            // Clear description and make it editable again
            $descTextarea.val("");
            $descTextarea.prop("readonly", false);
            $descTextarea.css("background-color", "");

            $(this).val("");
            $(this).removeData("org-name");
            $(this).attr("placeholder", "Search FU organization");
          }
        },
      })
      .data("autocomplete-initialized", true);
  });
}

// GLOBAL FUNCTION - Move this BEFORE $(document).ready()
window.getOrgEvents = function (startDate, endDate) {
  let tbody = $("#track-events-tbody");
  let table = $("#myEventTable");

  if (tbody.length === 0 || table.length === 0) {
    console.log(
      "Track events table not found - tbody:",
      tbody.length,
      "table:",
      table.length,
    );
    return;
  }

  let org_id = tbody.data("id");

  if (!org_id) {
    console.log("Organization ID not found in tbody data-id attribute");
    return;
  }

  console.log("Loading events for org_id:", org_id);

  // Build data object with org_id
  let requestData = { org_id: org_id };

  // Add date range if provided
  if (
    startDate &&
    endDate &&
    typeof startDate.format === "function" &&
    typeof endDate.format === "function"
  ) {
    requestData.start_date = startDate.format("YYYY-MM-DD");
    requestData.end_date = endDate.format("YYYY-MM-DD");
    console.log(
      "Date range:",
      requestData.start_date,
      "to",
      requestData.end_date,
    );
  }

  $.ajax({
    url: `${BASE_URL}organization/get-org-events`,
    method: "GET",
    data: requestData,
    success: function (re) {
      console.log("Events loaded:", re);

      // Destroy existing DataTable if it exists
      if ($.fn.DataTable && $.fn.DataTable.isDataTable("#myEventTable")) {
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
                <td>${event.event_start_date || ""}</td>
                <td>${event.created_at || ""}</td>
                <td>${event.event_name || ""}</td>
                <td>${event.current_access_id || ""}</td>
                <td>${event.status_name || ""}</td>
                <td>
                  <div class="action-buttons">
                    <button class="action-btn view-btn" data-id="${event.event_id}" title="View Event">
                      <i class="fa-solid fa-eye"></i>
                    </button>
                    ${
                      event.status_id == 6 || event.status_id == 7
                        ? `
                          <button class="action-btn delete-btn" data-id="${event.event_id}" title="Delete Event">
                            <i class="fa-solid fa-trash"></i>
                          </button>
                          <button class="action-btn edit-btn" data-id="${event.event_id}" title="Edit Event">
                            <i class="fa-solid fa-pencil"></i>
                          </button>
                        `
                        : ``
                    }
                  </div>
                </td>
              </tr>
            `);
          });
        }

        // Initialize DataTable
        if ($.fn.DataTable) {
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
              emptyTable: "No results found.",
            },
          });
        }
      } else {
        // Handle error status from backend
        if ($.fn.DataTable) {
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
              emptyTable:
                re.message || "Error loading events. Please try again.",
            },
          });
        }
      }
    },
    error: function (xhr, status, error) {
      console.log("AJAX Error:", error);
      console.log("XHR:", xhr);

      // Destroy existing DataTable if it exists
      if ($.fn.DataTable && $.fn.DataTable.isDataTable("#myEventTable")) {
        table.DataTable().destroy();
      }

      tbody.empty();

      // Initialize DataTable with error message
      if ($.fn.DataTable) {
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
            emptyTable: "Error loading events. Please try again.",
          },
        });
      }
    },
  });
};

function updateEventScheduleTableForForm($form) {
  const $table = $form.find("#eventScheduleTable");
  if ($table.length === 0) {
    return;
  }

  const $startInput = $form.find('input[name="start-date"]');
  const $endInput = $form.find('input[name="end-date"]');
  const $tbody = $table.find("tbody");

  if ($startInput.length === 0 || $endInput.length === 0 || $tbody.length === 0) {
    return;
  }

  const startValue = $startInput.val();
  const endValue = $endInput.val();

  const renderMessageRow = (message, isError = false) => {
    const textColor = isError ? "#8b0000" : "#999";
    $tbody.html(`
      <tr>
        <td colspan="3" style="padding: 15px; text-align: center; color: ${textColor};">${message}</td>
      </tr>
    `);
  };

  if (!startValue || !endValue) {
    if ($endInput[0]) {
      $endInput[0].setCustomValidity("");
    }
    renderMessageRow("Select a date range to generate schedule");
    return;
  }

  const startDate = new Date(`${startValue}T00:00:00`);
  const endDate = new Date(`${endValue}T00:00:00`);

  if (Number.isNaN(startDate.getTime()) || Number.isNaN(endDate.getTime())) {
    renderMessageRow("Invalid date selection.", true);
    return;
  }

  if (endDate < startDate) {
    if ($endInput[0]) {
      $endInput[0].setCustomValidity("End date must be on or after start date.");
    }
    renderMessageRow("End date must be on or after start date.", true);
    return;
  }

  if ($endInput[0]) {
    $endInput[0].setCustomValidity("");
  }

  const millisecondsPerDay = 24 * 60 * 60 * 1000;
  const dayCount = Math.floor((endDate - startDate) / millisecondsPerDay) + 1;

  if (dayCount > 366) {
    renderMessageRow("Date range is too large. Please limit to 366 days.", true);
    return;
  }

  let rowsHtml = "";
  for (let i = 0; i < dayCount; i++) {
    const currentDate = new Date(startDate);
    currentDate.setDate(startDate.getDate() + i);

    const dateLabel = currentDate.toLocaleDateString("en-US", {
      weekday: "short",
      year: "numeric",
      month: "short",
      day: "numeric",
    });

    rowsHtml += `
      <tr style="border: 1px solid #ddd;">
        <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold;">Day ${i + 1}</td>
        <td style="padding: 10px; border: 1px solid #ddd;">${dateLabel}</td>
        <td style="padding: 10px; border: 1px solid #ddd;">
          <input
            type="text"
            name="event-schedule-notes[]"
            placeholder="Enter activities for this day"
            style="width: 100%; padding: 5px; border: 1px solid #ccc; border-radius: 4px;"
          >
        </td>
      </tr>
    `;
  }

  $tbody.html(rowsHtml);
}

function initializeHostEventScheduleTable() {
  const dateInputSelector =
    '#host-event-form input[name="start-date"], ' +
    '#host-event-form input[name="end-date"], ' +
    '#edit-event-form input[name="start-date"], ' +
    '#edit-event-form input[name="end-date"]';

  $(document)
    .off("change.hostEventSchedule input.hostEventSchedule", dateInputSelector)
    .on("change.hostEventSchedule input.hostEventSchedule", dateInputSelector, function () {
      const $form = $(this).closest("form");
      updateEventScheduleTableForForm($form);
    });

  $("#host-event-form, #edit-event-form").each(function () {
    updateEventScheduleTableForForm($(this));
  });
}

window.generateEventSchedule = function () {
  const $focusedForm = $(
    'input[name="start-date"]:focus, input[name="end-date"]:focus',
  ).closest("form");
  if ($focusedForm.length > 0) {
    updateEventScheduleTableForForm($focusedForm);
    return;
  }

  const $hostForm = $("#host-event-form");
  if ($hostForm.length > 0) {
    updateEventScheduleTableForForm($hostForm);
    return;
  }

  const $editForm = $("#edit-event-form");
  if ($editForm.length > 0) {
    updateEventScheduleTableForForm($editForm);
  }
};

$(document).ready(function () {
  console.log("event.js loaded and ready");

  $('input[name="event-collab-bool"]').change(function () {
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

  // Use event delegation for all collaborator type radios
  $(document).on("change", ".collab-type-radio", function () {
    let collabNumber = $(this).data("collab-number");
    let value = $(this).val();

    const internalInput = document.querySelector(
      `.collab-input-${collabNumber}-internal`,
    );
    const externalInput = document.querySelector(
      `.collab-input-${collabNumber}-external`,
    );
    const typeField = document.querySelector(`.collab-type-${collabNumber}`);

    if (value === "internal") {
      internalInput.style.display = "block";
      internalInput.name = "event-collab[]";
      internalInput.required = false;
      externalInput.style.display = "none";
      externalInput.name = "";
      externalInput.required = false;
      externalInput.value = "";
      typeField.value = "internal";
    } else {
      internalInput.style.display = "none";
      internalInput.name = "";
      internalInput.required = false;
      internalInput.value = "";
      externalInput.style.display = "block";
      externalInput.name = "event-collab[]";
      externalInput.required = false;
      typeField.value = "external";
    }
  });

  setTimeout(function () {
    if (typeof $.fn.autocomplete !== "undefined") {
      initOrgAutocomplete();
    } else {
      console.warn("jQuery UI not available yet, will retry...");
      // Retry after another delay
      setTimeout(function () {
        if (typeof $.fn.autocomplete !== "undefined") {
          initOrgAutocomplete();
        } else {
          console.error("jQuery UI failed to load");
        }
      }, 500);
    }
  }, 200);

  const container = document.getElementById("collaborators-container");
  const template = document.getElementById("collaborator-template");
  let collaboratorCount = 0;

  function addCollaborator() {
    if (!template || !container) {
      console.log("Collaborator template or container not found on this page");
      return;
    }
    collaboratorCount++;
    const clone = template.content.cloneNode(true);
    const html = clone.firstElementChild.outerHTML.replaceAll(
      /X/g,
      collaboratorCount,
    );
    container.insertAdjacentHTML("beforeend", html);
  }

  // Only initialize if elements exist
  if (template && container) {
    // Add initial collaborator
    addCollaborator();

    // Add on button click
    const addButton = document.getElementById("add-collaborator");
    if (addButton) {
      addButton.addEventListener("click", addCollaborator);
    }
  }

  let selected = $('input[name="event-collab-bool"]:checked');
  if (selected.val() == "yes") {
    if ($(".group-collaborator").hasClass("hide")) {
      $(".group-collaborator").removeClass("hide");
    }
  } else {
    $(".group-collaborator").addClass("hide");
    console.log(selected.val());
  }

  $("#event-add-row").click(function (e) {
    e.stopPropagation();
    e.preventDefault();
    $(this).before(`
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
      `);
  });

  // Budget calculation - works for both new and edit forms
  $(document).on(
    "input",
    "input[name='unit-price[]'], input[name='qty[]']",
    function () {
      let row = $(this).closest(".budget-item-row");
      let total = 0;
      let price = parseFloat(row.find("input[name='unit-price[]']").val()) || 0;
      let qty = parseFloat(row.find("input[name='qty[]']").val()) || 0;

      // Calculate amount for this row
      let amount = price * qty;
      row.find("input[name='amount[]']").val(amount.toFixed(2));

      // Calculate total estimated budget
      $(this)
        .closest("form")
        .find("input[name='amount[]']")
        .each(function () {
          total += parseFloat($(this).val()) || 0;
        });

      $(this)
        .closest("form")
        .find("input[name='estimated-budget']")
        .val(total.toFixed(2));
    },
  );

  // Initialize budget calculation on page load for existing rows
  $(document).ready(function () {
    $("input[name='unit-price[]'], input[name='qty[]']").trigger("input");
  });

  $("#host-event-form").submit(function (e) {
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
          if (typeof window.getOrgEvents === "function") {
            window.getOrgEvents();
          }

          // If using navigation, trigger a refresh of the current page
          // Check if we're on the track-event page and refresh it
          if ($("#track-events-tbody").length > 0) {
            if (typeof window.getOrgEvents === "function") {
              window.getOrgEvents();
            }
          }

          // Navigate to events page if not already there
          const currentPath = window.location.pathname;
          if (
            !currentPath.includes("track-event") &&
            !currentPath.includes("manage-event")
          ) {
            // Navigate to events page
            const eventsLink = $('a[data-link="organization/track-event"]');
            if (eventsLink.length > 0) {
              eventsLink.trigger("click");
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
          // Close modal if it's open
          $("#orgModal").removeClass("show");
          $("#modalBody").empty();

          // Refresh events list
          if ($("#track-events-tbody").length > 0) {
            // Get current date range from date picker if it exists
            let startDate, endDate;
            if ($("#demo").length > 0 && $("#demo").data("daterangepicker")) {
              const picker = $("#demo").data("daterangepicker");
              startDate = picker.startDate;
              endDate = picker.endDate;
            }
            // Call getOrgEvents to refresh the table
            if (typeof window.getOrgEvents === "function") {
              window.getOrgEvents(startDate, endDate);
            }
          }
        } else {
          alert(
            response.message || "Failed to update event. Please try again.",
          );
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

  // Handle edit button click from table action buttons
  $(document).on("click", ".edit-btn", function (e) {
    e.stopPropagation();

    let eventId = $(this).data("id");
    console.log("Edit button clicked for event:", eventId);

    if (!eventId) {
      console.error("Event ID not found");
      alert("Error: Event ID not found");
      return;
    }

    var modal = $("#orgModal");

    // Update modal title
    $("#modalTitle").text("Edit Event");

    // Clear previous content and show loading
    $("#modalBody").html(
      '<div style="text-align: center; padding: 50px;"><i class="fa-solid fa-spinner fa-spin fa-2x"></i><p>Loading edit form...</p></div>',
    );
    modal.addClass("show");

    // Load the edit form using the new route
    $("#modalBody").load(
      `${BASE_URL}organization/host-event/edit/${eventId}`,
      function (response, status, xhr) {
        if (status == "error") {
          console.error("Error loading edit form:", xhr.status, xhr.statusText);
          $("#modalBody").html(
            '<div style="text-align: center; padding: 50px;">' +
              '<p style="color: red;">Error loading edit form. Please try again.</p>' +
              '<p style="color: #666; font-size: 12px;">Status: ' +
              xhr.status +
              " " +
              xhr.statusText +
              "</p>" +
              '<p style="color: #666; font-size: 12px;">URL: ' +
              `${BASE_URL}organization/host-event/edit/${eventId}` +
              "</p>" +
              "<button onclick=\"$('#orgModal').removeClass('show');\" style=\"padding: 10px 20px; background: #8b0000; color: white; border: none; border-radius: 5px; cursor: pointer; margin-top: 20px;\">Close</button>" +
              "</div>",
          );
        } else if (status == "success") {
          console.log("Edit form loaded successfully");
          // Re-initialize budget calculation for dynamically loaded form
          $("input[name='unit-price[]'], input[name='qty[]']").trigger("input");
        }
      },
    );
  });

  // Function to initialize date range picker and load events
  function initializeDateRangePicker() {
    if ($("#demo").length > 0 && $("#track-events-tbody").length > 0) {
      console.log("Initializing date range picker...");
      const dateFilterInput = $("#demo");
      const clearFilterButton = $("#track-events-clear-btn");
      dateFilterInput.prop("readonly", true).attr("inputmode", "none").attr("aria-readonly", "true");
      dateFilterInput.on("keydown paste drop", function (e) {
        e.preventDefault();
      });

      clearFilterButton.off("click.trackEventsDateClear").on("click.trackEventsDateClear", function () {
        const picker = dateFilterInput.data("daterangepicker");
        if (picker) {
          picker.setStartDate(moment());
          picker.setEndDate(moment());
          picker.hide();
        }

        dateFilterInput.val("");
        if (typeof window.getOrgEvents === "function") {
          window.getOrgEvents();
        }
      });

      // Ensure the filter field is never visually blank before interaction.
      if (!dateFilterInput.attr("placeholder")) {
        dateFilterInput.attr("placeholder", "Select date range");
      }

      // Check if daterangepicker is available
      if (typeof $.fn.daterangepicker === "undefined") {
        console.log("DateRangePicker library not loaded yet");
        // Just load events without date filter
        if (typeof window.getOrgEvents === "function") {
          window.getOrgEvents();
        }
        return;
      }

      dateFilterInput.daterangepicker(
        {
          ranges: {
            Today: [moment(), moment()],
            Yesterday: [
              moment().subtract(1, "days"),
              moment().subtract(1, "days"),
            ],
            "Last 7 Days": [moment().subtract(6, "days"), moment()],
            "Last 30 Days": [moment().subtract(29, "days"), moment()],
            "This Month": [moment().startOf("month"), moment().endOf("month")],
            "Last Month": [
              moment().subtract(1, "month").startOf("month"),
              moment().subtract(1, "month").endOf("month"),
            ],
          },
          alwaysShowCalendars: true,
          autoUpdateInput: false,
          locale: {
            format: "MM/DD/YYYY",
            cancelLabel: "Clear",
          },
          opens: "left",
        },
        function (start, end, label) {
          // Predefined range selected
          dateFilterInput.val(
            start.format("MM/DD/YYYY") + " - " + end.format("MM/DD/YYYY"),
          );
          if (typeof window.getOrgEvents === "function") {
            window.getOrgEvents(start, end);
          }
        },
      );

      // Toggle behavior: click input to open, click again to close.
      // Use mousedown to prevent daterangepicker from re-opening on the same click cycle.
      let suppressNextClick = false;
      dateFilterInput.off(".dateRangeToggle");

      dateFilterInput.on("mousedown.dateRangeToggle", function (e) {
        const picker = dateFilterInput.data("daterangepicker");
        if (picker && picker.isShowing) {
          suppressNextClick = true;
          picker.hide();
          e.preventDefault();
          e.stopImmediatePropagation();
        }
      });

      dateFilterInput.on("click.dateRangeToggle", function (e) {
        if (suppressNextClick) {
          suppressNextClick = false;
          e.preventDefault();
          e.stopImmediatePropagation();
          return false;
        }
      });

      // Handle apply button (manual date selection)
      dateFilterInput.on("apply.daterangepicker", function (ev, picker) {
        dateFilterInput.val(
          picker.startDate.format("MM/DD/YYYY") +
            " - " +
            picker.endDate.format("MM/DD/YYYY"),
        );
        if (typeof window.getOrgEvents === "function") {
          window.getOrgEvents(picker.startDate, picker.endDate);
        }
      });

      // Handle cancel button (show all events)
      dateFilterInput.on("cancel.daterangepicker", function (ev, picker) {
        dateFilterInput.val("");
        if (typeof window.getOrgEvents === "function") {
          window.getOrgEvents();
        }
      });

      // Initial load - show all events
      console.log("Loading initial events...");
      if (typeof window.getOrgEvents === "function") {
        window.getOrgEvents();
      }
    } else if ($("#track-events-tbody").length > 0) {
      // If no date picker, load all events
      console.log("No date picker, loading events directly...");
      if (typeof window.getOrgEvents === "function") {
        window.getOrgEvents();
      }
    }
  }

  function initializeLogEventForm() {
    const form = $("#log-event-form");
    const eventSelect = $("#log-event-select");
    const detailsContainer = $("#log-event-details");

    if (form.length === 0 || eventSelect.length === 0 || detailsContainer.length === 0) {
      return;
    }

    if (form.data("initialized")) {
      return;
    }
    form.data("initialized", true);

    const submitButton = form.find('button[type="submit"]');
    const originalButtonText = submitButton.text();
    let pendingEvents = [];

    function formatDate(dateString) {
      if (!dateString) {
        return "N/A";
      }

      const parts = dateString.split("-");
      if (parts.length === 3) {
        return `${parts[1]}/${parts[2]}/${parts[0]}`;
      }

      return dateString;
    }

    function setDetails(message) {
      detailsContainer.text(message);
    }

    function renderEventDetails(event) {
      if (!event) {
        setDetails("Select an event to view details.");
        return;
      }

      detailsContainer.html(`
        <div><strong>Event:</strong> ${event.event_name || "N/A"}</div>
        <div><strong>Start Date:</strong> ${formatDate(event.event_start_date)}</div>
        <div><strong>End Date:</strong> ${formatDate(event.event_end_date)}</div>
        <div><strong>Venue:</strong> ${event.event_venue || "N/A"}</div>
      `);
    }

    function loadPendingEvents() {
      eventSelect.prop("disabled", true).html('<option value="">Loading events...</option>');
      submitButton.prop("disabled", true).text(originalButtonText);
      setDetails("Loading event details...");

      $.ajax({
        url: `${BASE_URL}organization/log-event/get-pending-events`,
        method: "GET",
        dataType: "json",
        success: function (response) {
          if (response.status !== "success") {
            eventSelect.html('<option value="">Unable to load events</option>');
            setDetails(response.message || "Failed to load events.");
            return;
          }

          pendingEvents = response.data || [];
          eventSelect.empty();
          eventSelect.append('<option value="">Select an event</option>');

          if (pendingEvents.length === 0) {
            eventSelect.html('<option value="">No events awaiting documentation</option>');
            setDetails("No events in Awaiting Documentation status.");
            return;
          }

          pendingEvents.forEach((event) => {
            eventSelect.append(
              `<option value="${event.event_id}">${event.event_name}</option>`,
            );
          });

          eventSelect.prop("disabled", false);
          submitButton.prop("disabled", false);
          setDetails("Select an event to view details.");
        },
        error: function (xhr) {
          eventSelect.html('<option value="">Unable to load events</option>');
          setDetails("Error loading events. Please refresh and try again.");

          if (xhr.status === 401) {
            submitButton.prop("disabled", true);
          }
        },
      });
    }

    eventSelect.on("change", function () {
      const selectedEventId = parseInt($(this).val(), 10);
      const selectedEvent = pendingEvents.find(
        (event) => event.event_id == selectedEventId,
      );

      renderEventDetails(selectedEvent);
    });

    form.on("submit", function (e) {
      e.preventDefault();

      if (!eventSelect.val()) {
        alert("Please select an event to log.");
        return;
      }

      submitButton.prop("disabled", true).text("Submitting...");

      $.ajax({
        url: `${BASE_URL}organization/log-event/submit`,
        method: "POST",
        data: form.serialize(),
        dataType: "json",
        success: function (response) {
          if (response.status === "success") {
            alert(response.message || "Event log submitted successfully.");
            form[0].reset();
            renderEventDetails(null);
            loadPendingEvents();
          } else {
            alert(response.message || "Failed to submit event log.");
            submitButton.prop("disabled", false).text(originalButtonText);
          }
        },
        error: function (xhr) {
          const errorMessage =
            (xhr.responseJSON && xhr.responseJSON.message) ||
            "Failed to submit event log.";
          alert(errorMessage);
          submitButton.prop("disabled", false).text(originalButtonText);
        },
      });
    });

    loadPendingEvents();
  }

  // Try to initialize immediately
  initializeDateRangePicker();
  initializeHostEventScheduleTable();
  initializeLogEventForm();

  // Also try after delays for AJAX-loaded content
  setTimeout(function () {
    initializeDateRangePicker();
    initializeHostEventScheduleTable();
    initializeLogEventForm();
  }, 500);

  setTimeout(function () {
    initializeDateRangePicker();
    initializeHostEventScheduleTable();
    initializeLogEventForm();
  }, 1000);

  // Function to load current ranking
  function loadCurrentRanking(dateRange = null) {
    const requestData = {};
    if (dateRange && dateRange.start_date && dateRange.end_date) {
      requestData.start_date = dateRange.start_date;
      requestData.end_date = dateRange.end_date;
    }

    $.ajax({
      url: BASE_URL + "organization/get-current-ranking",
      method: "GET",
      data: requestData,
      dataType: "json",
      success: function (response) {
        if (response.status === "success" && response.rank) {
          $("#current-ranking").text(response.rank);
        } else {
          $("#current-ranking").text("-");
        }
      },
      error: function (xhr, status, error) {
        console.error("Error loading current ranking:", error);
        $("#current-ranking").text("-");
      },
    });
  }

  // Initialize ranking on page load
  function initCurrentRanking() {
    if ($("#current-ranking").length > 0) {
      // Calculate default date range for Annually (same as leaderboard)
      const now = new Date();
      const currentYear = now.getFullYear();
      const currentMonth = now.getMonth() + 1;

      let startDate, endDate;
      if (currentMonth >= 8) {
        startDate = `${currentYear}-08-01`;
        endDate = `${currentYear + 1}-05-31`;
      } else {
        startDate = `${currentYear - 1}-08-01`;
        endDate = `${currentYear}-05-31`;
      }

      loadCurrentRanking({
        start_date: startDate,
        end_date: endDate,
      });
    }
  }

  // Initialize ranking on page load
  initCurrentRanking();

  // Listen for page content loaded event (remove existing listener first to prevent duplicates)
  $(document)
    .off("pageContentLoaded.eventjs")
    .on("pageContentLoaded.eventjs", function (e, url) {
      console.log("Page content loaded:", url);
      if (
        url &&
        (url.includes("/organization/homepage") || url.includes("homepage"))
      ) {
        setTimeout(function () {
          initializeDateRangePicker();
          initCurrentRanking();
        }, 300);
      }

      // Check if track-events page loaded
      if (url && url.includes("track-event")) {
        console.log("Track events page loaded, initializing...");
        setTimeout(function () {
          initializeDateRangePicker();
        }, 300);
      }

      if (url && url.includes("host-event")) {
        setTimeout(function () {
          initializeHostEventScheduleTable();
        }, 300);
      }

      if (url && url.includes("log-event")) {
        setTimeout(function () {
          initializeLogEventForm();
        }, 300);
      }
    });

  var modal = $("#orgModal");

  $(document).on("click", ".view-btn", function (e) {
    e.stopPropagation();
    let eventId = $(this).data("id");
    console.log(eventId);

    if (!eventId || eventId === "undefined" || eventId === "null") {
        console.error("Invalid Event ID for view:", eventId);
        alert("Error: Invalid Event ID.");
        return;
    }

    // Update modal title
    $("#modalTitle").text("Event Details");

    // Clear previous content and show loading
    $("#modalBody").html(
      '<div style="text-align: center; padding: 20px;"><i class="fa-solid fa-spinner fa-spin fa-2x"></i><p>Loading event details...</p></div>',
    );
    modal.addClass("show");

    // Load the view-event content
    $("#modalBody").load(
      `${BASE_URL}organization/manage-event/view-event/${eventId}`,
      function (response, status, xhr) {
        if (status == "error") {
          $("#modalBody").html(
            '<div style="padding: 20px; text-align: center;"><p style="color: red;">Error loading event details. Please try again.</p></div>',
          );
        }
      },
    );
  });

  modal.click(function (e) {
    if ($(e.target).is(modal)) {
      modal.removeClass("show");
    }
  });

  // Close button click
  $(".modal-close").click(function () {
    modal.removeClass("show");
  });

  // Cancel button click
  $("#modalCancelBtn").click(function () {
    modal.removeClass("show");
  });
});

// Variable to prevent duplicate delete submissions (check if already declared)
if (typeof deleteInProgress === "undefined") {
  var deleteInProgress = false;
}

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
            if (typeof window.getOrgEvents === "function") {
              // Get current date range from date picker if it exists
              let startDate, endDate;
              if ($("#demo").length > 0 && $("#demo").data("daterangepicker")) {
                const picker = $("#demo").data("daterangepicker");
                startDate = picker.startDate;
                endDate = picker.endDate;
              }
              window.getOrgEvents(startDate, endDate);
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

// Handle edit button click in view-event modal (for revision status)
$(document).on("click", "#edit-event-btn", function (e) {
  e.stopPropagation();
  e.preventDefault();

  console.log("Edit button clicked!"); // Debug log

  let eventId = $(this).data("event-id");
  console.log("Event ID:", eventId); // Debug log

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
            '<p style="color: #666; font-size: 12px;">Status: ' +
            xhr.status +
            " " +
            xhr.statusText +
            "</p>" +
            '<p style="color: #666; font-size: 12px;">URL: ' +
            `${BASE_URL}organization/manage-event/edit-event/${eventId}` +
            "</p>" +
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
