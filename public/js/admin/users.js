// BASE_URL is defined in config.js

$(document).ready(function () {
  let usersTableInstance = null;

  function openUserQuickView(userId) {
    if (!userId) return;
    $("#modalTitle").text("User Details");
    $("#modalBody").load(BASE_URL + "admin/modify-user/user-view/" + userId);
    $("#orgModal").addClass("show");
  }

  function bindUsersToolbarFilters() {
    if (!usersTableInstance || $("#users-search-input").length === 0) return;

    function applyFilters() {
      const keyword = ($("#users-search-input").val() || "").trim();
      const role = ($("#users-role-filter").val() || "").trim();

      usersTableInstance.search(keyword);
      if (role) {
        usersTableInstance.column(1).search("^" + role.replace(/[.*+?^${}()|[\]\\]/g, "\\$&") + "$", true, false);
      } else {
        usersTableInstance.column(1).search("");
      }
      usersTableInstance.draw();
    }

    $("#users-search-input")
      .off("input.usersToolbar")
      .on("input.usersToolbar", applyFilters);

    $("#users-role-filter")
      .off("change.usersToolbar")
      .on("change.usersToolbar", applyFilters);

    $("#users-clear-btn")
      .off("click.usersToolbar")
      .on("click.usersToolbar", function () {
        $("#users-search-input").val("");
        $("#users-role-filter").val("");
        applyFilters();
      });
  }

  function callUsers() {
    let $tbody = $("#users-tbody");
    let $table = $("#users-table");
    
    // Destroy existing DataTable instance if it exists
    if ($.fn.DataTable.isDataTable($table)) {
      $table.DataTable().destroy();
    }
    
    // Show loading
    $tbody.empty();
    let $loadingRow = $("<tr>");
    let $loadingCell = $("<td>").attr("colspan", "6").css({
      "text-align": "center",
      "padding": "40px",
      "font-size": "16px",
      "color": "#666"
    });
    $loadingCell.html('<i class="fa-solid fa-spinner fa-spin" style="margin-right: 10px;"></i>Loading users...');
    $loadingRow.append($loadingCell);
    $tbody.append($loadingRow);

  $.ajax({
    url: BASE_URL + "admin/get-users", //route padulong satong controller nga naay database
    type: "POST",
    dataType: "json", // naka js na sa ato controller so dali nani makuha and mao ni required
    success: function (users) {
      $tbody.empty(); // remove usa ang naa

      if (users.length > 0) {
        $.each(users, function (index, user) {
          let row = $("<tr>").attr("data-user-id", user.user_id || "");

          row.append(
            $("<td>")
              .addClass("user-email")
              .text(user.email || "no email")
          );
          row.append(
            $("<td>")
              .addClass("user-access")
              .text(user.access_name || "no user_type")
          );
          row.append(
            $("<td>")
              .addClass("user-org")
              .text(user.org_name || "no organization")
          );
          row.append(
            $("<td>")
              .addClass("user-position")
              .text(user.position || "no position")
          );
          row.append($("<td>").text(user.created_at || "no created_at"));

          let actionsCell = $('<td class="actions-cell">');
          let editBtn = $("<button>")
            .addClass("action-btn edit-btn")
            .attr("data-id", user.user_id || "");
          editBtn.append($("<i>").addClass("fa-solid fa-pen-to-square"));
          actionsCell.append(editBtn);
          let deleteBtn = $("<button>")
            .addClass("action-btn delete-btn")
            .attr("data-id", user.user_id || "");
          deleteBtn.append($("<i>").addClass("fa-solid fa-trash"));
          actionsCell.append(deleteBtn);

          row.append(actionsCell); // para matapad sa table row
          $tbody.append(row);
        });
      } else {
        let $noDataRow = $("<tr>");
        $noDataRow.append(
          $("<td>").attr("colspan", "6").text("No users found.")
        );
        $tbody.append($noDataRow);
        //kani error or maski dili error pero incase 0 length ang user so pasabot way unod.
      }

        // Initialize DataTable after the rows are loaded
        // Use setTimeout to ensure DOM is fully updated
        setTimeout(function() {
          usersTableInstance = $table.DataTable({
            destroy: true,
            paging: true,
            pageLength: 10,
            order: [[0, 'asc']]
          });
          bindUsersToolbarFilters();
        }, 100);
    },
    error: function (xhr, status, error) {
      console.log("AJAX Error:", status, error);
        $tbody.empty();
        $tbody.html(
        $("<tr>").append(
            $("<td>").attr("colspan", "6").css({
              "text-align": "center",
              "padding": "20px",
              "color": "#d32f2f"
            }).text("Error loading users.")
        )
      );
      //mo dungag tag table row nya butngan natog td nga ang text may error kay error mani sa ajax
    },
  });
  }

  // Edit button click event
  $(document).on("click", ".edit-btn", function () {
    let userId = $(this).attr("data-id");
    $("#modalTitle").text("Edit User");
    $("#modalBody").load(BASE_URL + "admin/modify-user/user-edit/" + userId);
    $("#orgModal").addClass("show");
  });

  // Delete button click event
  $(document).on("click", ".delete-btn", function () {
    let userId = $(this).attr("data-id");
    $("#modalTitle").text("Delete User");
    $("#modalBody").load(BASE_URL + "admin/modify-user/user-delete/" + userId);
    $("#orgModal").addClass("show");
  });

  // Add User button click event
  $(document).on("click", "#addUserBtn", function () {
    $("#modalTitle").text("Add New User");
    $("#modalBody").load(BASE_URL + "admin/modify-user/user-add/null");
    $("#orgModal").addClass("show");
  });

  // Close modal events
  $(".modal-close").click(function () {
    $("#orgModal").removeClass("show");
  });

  $(document).on("click", "#users-table tbody tr", function (e) {
    if ($(e.target).closest(".action-btn, .dataTables_empty").length > 0) {
      return;
    }
    const userId = $(this).attr("data-user-id");
    if (userId) {
      openUserQuickView(userId);
    }
  });

  // Cancel button - use event delegation since button is loaded dynamically
  $(document).on("click", "#modalCancelAddBtn", function () {
    $("#orgModal").removeClass("show");
  });

  // Close modal when clicking outside
  $("#orgModal").click(function (e) {
    if ($(e.target).is("#orgModal")) {
      $("#orgModal").removeClass("show");
    }
  });

  // Edit icon click - toggle readonly
  $(document).on("click", ".edit-icon", function () {
    const targetId = $(this).data("target");
    const inputId = $("#" + targetId);

    const isDisabled = inputId.prop("readonly");
    inputId.prop("readonly", !isDisabled);

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

  // Form submission handler for edit user
  $(document).on("submit", "#form-edit-user", function (e) {
    e.preventDefault();

    const form = $("#form-edit-user")[0];
    const formData = new FormData(form);

    $.ajax({
      url: BASE_URL + "admin/edit-user",
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (response) {
        if (response.status === "success") {
          $("#orgModal").removeClass("show");
          // Reload users table immediately
          callUsers();
          // Show alert after refresh is triggered
          setTimeout(function() {
            alert("User Successfully Updated!");
          }, 100);
        } else {
          alert("ERROR: " + (response.message || "Failed to update user"));
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", status, error);
        alert("Error updating user. Please check console for details.");
      },
    });
  });

  // Form submission handler for delete user
  $(document).on("submit", "#form-delete-user", function (e) {
    e.preventDefault();

    const form = $("#form-delete-user")[0];
    const formData = new FormData(form);

    $.ajax({
      url: BASE_URL + "admin/delete-user",
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (response) {
        if (response.status === "success") {
          $("#orgModal").removeClass("show");
          // Reload users table immediately
          callUsers();
          // Show alert after refresh is triggered
          setTimeout(function() {
            alert("User Successfully Deleted!");
          }, 100);
        } else {
          alert("ERROR: " + (response.message || "Failed to delete user"));
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", status, error);
        alert("Error deleting user. Please check console for details.");
      },
    });
  });

  // Form submission handler for add user
  // Use .off() first to prevent duplicate handlers, then .on()
  $(document).off("submit", "#form-add-user").on("submit", "#form-add-user", function (e) {
    e.preventDefault();
    e.stopImmediatePropagation(); // Prevent other handlers from firing

    const form = $("#form-add-user")[0];
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
      url: BASE_URL + "admin/create-user",
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
          $("#form-add-user")[0].reset();
          $("#orgModal").removeClass("show");
          // Reload users table immediately
          callUsers();
          // Show alert after refresh is triggered
          setTimeout(function() {
            alert("User Successfully Added!");
          }, 100);
        } else {
          alert("ERROR: " + (response.message || "Failed to add user"));
        }
      },
      error: function (xhr, status, error) {
        // Re-enable submit button on error
        if (submitBtn) {
          submitBtn.disabled = false;
          const originalText = submitBtn.getAttribute('data-original-text') || 'Confirm';
          submitBtn.textContent = originalText;
        }
        console.error("AJAX Error:", status, error);
        alert("Error adding user. Please check console for details.");
      },
    });
    
    return false;
  });

  // Initial call to load users
  callUsers();
});
