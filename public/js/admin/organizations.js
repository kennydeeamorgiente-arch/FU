// BASE_URL is defined in config.js

$(document).ready(function () {
  let orgTableInstance = null;

  // Modal variables
  var modal = $("#orgModal");
  var modalTitle = $("#modalTitle");
  var modalBody = $("#modalBody");
  var modalConfirmBtn = $("#modalConfirmBtn");

  function openOrganizationDetails(orgId) {
    if (!orgId || orgId === "undefined" || orgId === "null") {
      alert("Error: Organization ID not found");
      return;
    }

    $("#orgModal").removeClass("show");
    $("#adminEventModal").removeClass("show");

    $(".container").load(`${BASE_URL}admin/modify-organization/org-view/${orgId}`, function (response, status, xhr) {
      if (status === "error") {
        console.error("Error loading organization:", xhr.status, xhr.statusText);
        alert("Error loading organization details. Please try again.");
      }
    });
  }

  function bindOrganizationToolbarFilters() {
    if (!orgTableInstance || $("#org-search-input").length === 0) return;

    function applyFilters() {
      const keyword = ($("#org-search-input").val() || "").trim();
      const type = ($("#org-type-filter").val() || "").trim();

      orgTableInstance.search(keyword);
      if (type) {
        orgTableInstance.column(3).search("^" + type.replace(/[.*+?^${}()|[\]\\]/g, "\\$&") + "$", true, false);
      } else {
        orgTableInstance.column(3).search("");
      }
      orgTableInstance.draw();
    }

    $("#org-search-input")
      .off("input.orgToolbar")
      .on("input.orgToolbar", applyFilters);

    $("#org-type-filter")
      .off("change.orgToolbar")
      .on("change.orgToolbar", applyFilters);

    $("#org-clear-btn")
      .off("click.orgToolbar")
      .on("click.orgToolbar", function () {
        $("#org-search-input").val("");
        $("#org-type-filter").val("");
        applyFilters();
      });
  }

  // function openModal($type, $orgId = null) {
  //   $.get(`${baseUrl}/${type}/id=${orgId}`);
  // }
  // Add New Org button click
  $(document).on("click", "#addOrgBtn", function () {
    modalTitle.text("Add New Organization");
    modalBody.load("modify-organization/org-add/null");
    modal.addClass("show");
  });

  // Edit button click
  $(document).on("click", ".edit-btn", function () {
    let orgId = $(this).attr("data-org");
    modalTitle.text("Edit Organization");
    modalBody.load(`modify-organization/org-edit/${orgId}`);
    modal.addClass("show");
  });

  // View button click - scoped to organizations table only
  $(document).on("click", "#organizations-table .view-btn", function (e) {
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();
    
    console.log("Organization view button clicked");
    let orgId = $(this).attr("data-org");
    
    if (!orgId || orgId === 'undefined' || orgId === 'null') {
      console.error("Organization ID not found");
      alert("Error: Organization ID not found");
      return false;
    }
    
    console.log("Loading organization view for ID:", orgId);
    openOrganizationDetails(orgId);
    return false;
  });

  $(document).on("click", "#organizations-table tbody tr", function (e) {
    if ($(e.target).closest(".action-btn, .dataTables_empty").length > 0) {
      return;
    }
    const orgId = $(this).attr("data-org-id");
    if (orgId) {
      openOrganizationDetails(orgId);
    }
  });

  // Delete button click
  $(document).on("click", ".delete-btn", function () {
    let orgId = $(this).attr("data-org");
    modalTitle.text("Delete Organization");
    modalBody.load(`modify-organization/org-delete/${orgId}`);
    modalConfirmBtn.text("Delete");
    modal.addClass("show");
  });

  // Close button click
  $(".modal-close").click(function () {
    modal.removeClass("show");
    modalConfirmBtn.css("background-color", "#8B0000");
  });

  // Cancel button click
  $("#modalCancelBtn").click(function () {
    modal.removeClass("show");
    modalConfirmBtn.css("background-color", "#8B0000");
  });

  // Close modal when clicking outside
  modal.click(function (e) {
    if ($(e.target).is(modal)) {
      modal.removeClass("show");
      modalConfirmBtn.css("background-color", "#8B0000");
    }
  });

  $(document).on("click", ".edit-icon", function () {
    const targetId = $(this).data("target");
    const inputId = $("#" + targetId);

    const isDisabled = inputId.prop("readonly");
    inputId.prop("readonly", !isDisabled);
    console.log(isDisabled);

    if (isDisabled) {
      inputId.val("");
      inputId.focus();
      $(this).css("color", "#8B0000");
      $(this).removeClass("fa-pencil").addClass("fa-lock");
    } else {
      $(this).css("color", "");
      $(this).removeClass("fa-lock").addClass("fa-pencil");
    }
  });

  function callOrganizations() {
    let tbody = $("#org-table-body");
    let table = $("#organizations-table");
    
    // Destroy existing DataTable instance if it exists
    if ($.fn.DataTable.isDataTable(table)) {
      table.DataTable().destroy();
    }
    
    // Show loading
    tbody.empty();
    let $loadingRow = $("<tr>");
    let $loadingCell = $("<td>").attr("colspan", "8").css({
      "text-align": "center",
      "padding": "40px",
      "font-size": "16px",
      "color": "#666"
    });
    $loadingCell.html('<i class="fa-solid fa-spinner fa-spin" style="margin-right: 10px;"></i>Loading organizations...');
    $loadingRow.append($loadingCell);
    tbody.append($loadingRow);

    $.ajax({
      url: BASE_URL + "admin/get-organizations",
      method: "GET",
      dataType: "json",
      success: function (response) {
        if (response.status === "success") {
          tbody.empty();
          let data = response.data;

          if (data.length === 0) {
            tbody.append(
              "<tr><td colspan = '8'>No Organizations Found.</td></tr>"
            );
          } else {
            $.each(data, function (index, org) {
              tbody.append(
                `
              <tr data-org-id="${org.org_id}">
                  <td>
                    <div class="org-info">
                      <div class="org-icon org-icon-blue">
                        ${
                          org.logo
                            ? `<img src="${BASE_URL}uploads/org-logos/${org.logo}" alt="${org.org_name} Logo" class="logo-image">`
                            : `${org.org_name ? org.org_name.substring(0, 1).toUpperCase() : 'O'}`
                        }
                      </div>
                      <div class="org-details">
                        <div class="org-name">${org.org_name || 'Unknown'}</div>
                      </div>
                    </div>
                  </td>
                  <td>${org.facebook_link || '-'}</td>
                  <td>${org.adviser || '-'}</td>
                  <td>${org.org_type_name || '-'}</td>
                  <td>${org.org_num_members || 0}</td>
                  <td>${org.num_events || 0}</td>
                  <td>${org.status || 'active'}</td>
                  <td>
                    <div class="action-buttons">
                      <button class="action-btn edit-btn" data-org="${
                        org.org_id
                      }">
                        <i class="fa-solid fa-pencil"></i>
                      </button>
                      <button class="action-btn view-btn" data-org="${
                        org.org_id
                      }">
                        <i class="fa-solid fa-magnifying-glass"></i>
                      </button>
                      <button class="action-btn delete-btn" data-org="${
                        org.org_id
                      }">
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              `
              );
            });
          }

          // Initialize DataTable after the rows are loaded
          // Use setTimeout to ensure DOM is fully updated
          setTimeout(function() {
            orgTableInstance = table.DataTable({
              destroy: true,
              paging: true,
              pageLength: 10,
              order: [[0, 'asc']]
            });
            bindOrganizationToolbarFilters();
          }, 100);
        } else {
          alert("There was an error occurred: " + (response.message || "Unknown error"));
        }
      },
      error: function(xhr, status, error) {
        console.error("Error loading organizations:", status, error);
        tbody.empty();
        tbody.append("<tr><td colspan='8' style='text-align: center; color: red;'>Error loading organizations. Please refresh the page.</td></tr>");
      }
    });
  }

  $(document).on("submit", "#form-edit-organization", function (e) {
    e.preventDefault();

    const form = $("#form-edit-organization")[0];
    const formData = new FormData(form);

    $.ajax({
      url: `${BASE_URL}/admin/edit-organization`,
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        if (response.status == "success") {
          alert("Organization Successfully Updated!");
          modal.removeClass("show");
          callOrganizations();
        } else {
          alert("ERROR: " + response.message);
        }
      },
    });
  });

  // Use .off() first to prevent duplicate handlers, then .on()
  $(document).off("submit", "#form-add-organization").on("submit", "#form-add-organization", function (e) {
    e.preventDefault();
    e.stopImmediatePropagation(); // Prevent other handlers from firing

    const form = $("#form-add-organization")[0];
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Prevent double submission
    if (submitBtn && submitBtn.disabled) {
      return false;
    }
    
    // Disable submit button to prevent double submission
    if (submitBtn) {
      submitBtn.disabled = true;
      const originalText = submitBtn.textContent || submitBtn.innerText;
      submitBtn.textContent = 'Creating...';
      submitBtn.setAttribute('data-original-text', originalText);
    }
    
    const formData = new FormData(form);

    $.ajax({
      url: BASE_URL + "admin/create-organization",
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (response) {
        // Re-enable submit button
        if (submitBtn) {
          submitBtn.disabled = false;
          const originalText = submitBtn.getAttribute('data-original-text') || 'Confirm';
          submitBtn.textContent = originalText;
        }
        
        if (response.status === "success") {
          alert("Organization Successfully added!");
          $("#form-add-organization")[0].reset();
          modal.removeClass("show");
          $("#modalBody").empty();
          callOrganizations();
        } else {
          alert(response.message || "Something went wrong.");
        }
        console.log(response);
      },
      error: function(xhr, status, error) {
        // Re-enable submit button on error
        if (submitBtn) {
          submitBtn.disabled = false;
          const originalText = submitBtn.getAttribute('data-original-text') || 'Confirm';
          submitBtn.textContent = originalText;
        }
        console.error("Error:", status, error);
        alert("An error occurred. Please try again.");
      }
    });
    
    return false;
  });

  $(document).on("submit", "#form-delete-organization", function (e) {
    e.preventDefault();

    const form = $("#form-delete-organization")[0];
    const formData = new FormData(form);

    $.ajax({
      url: BASE_URL + "admin/delete-organization",
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        if (response.status === "success") {
          alert("Organization Successfully Deleted!");
          $("#form-delete-organization")[0].reset();
          modal.removeClass("show");
          callOrganizations();
        } else {
          alert(response.message || "Something went wrong.");
        }
        console.log(response);
      },
    });
  });

  callOrganizations();
});
