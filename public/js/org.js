$(document).ready(function () {
  // BASE_URL is defined in config.js
  function buildLeaderboardDateRange(filterType = "annually") {
    const now = new Date();
    const currentYear = now.getFullYear();
    const currentMonth = now.getMonth() + 1; // JavaScript months are 0-indexed

    let startDate = null;
    let endDate = null;

    if (filterType === "annually") {
      if (currentMonth >= 8) {
        startDate = `${currentYear}-08-01`;
        endDate = `${currentYear + 1}-05-31`;
      } else {
        startDate = `${currentYear - 1}-08-01`;
        endDate = `${currentYear}-05-31`;
      }
    } else if (filterType === "1st_semester") {
      if (currentMonth >= 8) {
        startDate = `${currentYear}-08-01`;
        endDate = `${currentYear}-12-31`;
      } else {
        startDate = `${currentYear - 1}-08-01`;
        endDate = `${currentYear - 1}-12-31`;
      }
    } else if (filterType === "2nd_semester") {
      if (currentMonth >= 8) {
        startDate = `${currentYear + 1}-01-01`;
        endDate = `${currentYear + 1}-05-31`;
      } else {
        startDate = `${currentYear}-01-01`;
        endDate = `${currentYear}-05-31`;
      }
    } else {
      return null;
    }

    return {
      type: filterType,
      start_date: startDate,
      end_date: endDate
    };
  }

  function callOrganizations(dateRange = null) {
    // Check if leaderboard table exists
    if (
      $("#admin-leaderboard-body").length === 0 &&
      $("#org-leaderboard-body").length === 0
    ) {
      return; // Exit if table doesn't exist
    }

    // Determine which endpoint to use based on which table exists
    const isOrgLeaderboard = $("#org-leaderboard-body").length > 0;
    const endpoint = isOrgLeaderboard 
      ? "organization/get-organizations" 
      : "admin/get-organizations";

    // Prepare data object
    const requestData = {};
    if (dateRange && dateRange.start_date && dateRange.end_date) {
      requestData.start_date = dateRange.start_date;
      requestData.end_date = dateRange.end_date;
    }

    // Add console logging for debugging
    console.log("Calling organizations API with date range:", requestData);
    
    $.ajax({
      url: BASE_URL + endpoint,
      method: "GET",
      data: requestData,
      dataType: "json",
      success: function (response) {
        console.log("Organizations API response:", response);
        if (response.status === "success") {
          let tbody =
            $("#admin-leaderboard-body").length > 0
              ? $("#admin-leaderboard-body")
              : $("#org-leaderboard-body");

          tbody.empty();
          let data = response.data;

          if (data.length === 0) {
            tbody.append("<tr><td colspan='4'>No Data Found.</td></tr>");
          } else {
            $.each(data, function (index, org) {
              tbody.append(`
              <tr data-id="${org.org_id}">
                <td>${index + 1}</td>
                <td>${org.org_name}</td>
                <td>${org.org_type_name}</td>
                <td>${org.total_points || 0}</td>
              </tr>
            `);
            });
          }

          // 🔥 Initialize DataTable *after* the rows are loaded
          let tableSelector =
            $("#admin-leaderboard-body").length > 0
              ? "#admin-leaderboard-table"
              : "#org-leaderboard-table";

          // Destroy old instance (if any), then reinitialize
          if ($(tableSelector).length > 0) {
            // Check if DataTable is already initialized and destroy it
            if ($.fn.DataTable.isDataTable(tableSelector)) {
              $(tableSelector).DataTable().destroy();
            }
            
            // Reinitialize DataTable
            $(tableSelector).DataTable({
              destroy: true,
              paging: true,
              searching: true,
              ordering: true,
              info: true,
              responsive: true
            });
          }
        } else {
          alert("There was an error occurred");
        }
      },
      error: function(xhr, status, error) {
        console.error("Error loading organizations:", error);
        alert("There was an error loading the leaderboard data");
      }
    });
  }

  function applyLeaderboardFilter(filterType = "annually") {
    const dateRange = buildLeaderboardDateRange(filterType);

    if (dateRange) {
      callOrganizations(dateRange);
      if ($("#leaderboard-current-ranking").length > 0) {
        loadLeaderboardCurrentRanking(dateRange);
      }
    } else {
      callOrganizations();
      if ($("#leaderboard-current-ranking").length > 0) {
        loadLeaderboardCurrentRanking();
      }
    }
  }

  // Function to initialize leaderboard
  function initLeaderboard() {
    // Call API after checking table existence
    if (
      $("#admin-leaderboard-body").length > 0 ||
      $("#org-leaderboard-body").length > 0
    ) {
      const filterSelect = $("#leaderboard-filter, #admin-leaderboard-filter, .leaderboard-filter-select").first();
      if (filterSelect.length > 0) {
        const selectedFilter = filterSelect.val() || "annually";
        filterSelect.val(selectedFilter);
        applyLeaderboardFilter(selectedFilter);
        return;
      }

      callOrganizations();
    }
  }

  // Function to load current ranking for leaderboard page
  function loadLeaderboardCurrentRanking(dateRange = null) {
    const requestData = {};
    if (dateRange && dateRange.start_date && dateRange.end_date) {
      requestData.start_date = dateRange.start_date;
      requestData.end_date = dateRange.end_date;
    }

    $.ajax({
      url: BASE_URL + 'organization/get-current-ranking',
      method: 'GET',
      data: requestData,
      dataType: 'json',
      success: function(response) {
        if (response.status === 'success' && response.rank) {
          $('#leaderboard-current-ranking').text(response.rank);
        } else {
          $('#leaderboard-current-ranking').text('-');
        }
      },
      error: function(xhr, status, error) {
        console.error('Error loading current ranking:', error);
        $('#leaderboard-current-ranking').text('-');
      }
    });
  }

  // Initialize on page load (for direct page access)
  initLeaderboard();

  // Also initialize when content is loaded via AJAX
  $(document).on("pageContentLoaded", function(event, url) {
    // Small delay to ensure DOM is ready
    setTimeout(function() {
      initLeaderboard();
    }, 100);
  });

  // Click handler for leaderboard rows
  $(document).on("click", "#admin-leaderboard-table tbody tr, #org-leaderboard-table tbody tr", function (e) {
    // Ignore clicks on interactive controls inside row (future-safe)
    if ($(e.target).closest("a, button, input, select, textarea, label").length > 0) {
      return;
    }

    let row = $(this);
    // DataTables responsive child rows do not carry the data-id; use parent row
    if (row.hasClass("child")) {
      row = row.prev("tr");
    }

    const orgId = row.data("id");
    if (!orgId) {
      return;
    }

    const isOrgLeaderboard = row.closest("#org-leaderboard-table").length > 0;
    const url = isOrgLeaderboard
      ? `${BASE_URL}organization/profile/${orgId}`
      : `${BASE_URL}admin/modify-organization/org-view/${orgId}`;

    $("#content-wrapper").load(url);
  });

  let results = $(".search-result");
  results.hide();
  // ORG SEARCH NAME
  $(document).on("input", ".org-search-name", function () {
    let input = $(this);
    let text = input.val();
    let results = input.next(".search-result");
    if (!results.length) {
      input.after('<div class="search-result"></div>');
      results = input.next(".search-result"); // now we can use it
    }
    $.ajax({
      url: `${BASE_URL}/search-org`,
      method: "GET",
      data: { search: text },
      success: function (re) {
        if (re.status == "success") {
          let data = re.message;
          results.empty();
          results.show();
          data.forEach((result) => {
            results.append(`
            <p data-name="${result.org_name}" data-desc="${result.description}">${result.org_name}</p>
            `);
          });
        } else {
          results.html(`
            <p>${re.message}</p>
            `);
        }
      },
    });
  });

  $(document).on("click", ".search-result p", function () {
    let description = $(this).parent().next();
    let orgName = $(this).parent().prev();
    description.val($(this).data("desc"));
    orgName.val($(this).data("name"));
    $(this).parent().hide();
  });

  $(document).on("click", function (e) {
    // If click is NOT on a search-result or inside an input
    if (!$(e.target).closest(".search-result, .org-search-name").length) {
      $(".search-result").hide(); // hide all search results
    }
  });

  // Handle leaderboard period dropdown change
  $(document).on("change", ".leaderboard-filter-select", function() {
    if ($("#org-leaderboard-body").length === 0 && $("#admin-leaderboard-body").length === 0) {
      return;
    }

    const selectedFilter = $(this).val() || "annually";
    applyLeaderboardFilter(selectedFilter);
  });

  // Admin leaderboard search (DataTable search)
  $(document).on("input", "#admin-leaderboard-search-input", function () {
    const table = $("#admin-leaderboard-table");
    if (!table.length || !$.fn.DataTable.isDataTable("#admin-leaderboard-table")) {
      return;
    }

    table.DataTable().search($(this).val()).draw();
  });

  // Admin leaderboard clear controls
  $(document).on("click", "#admin-leaderboard-clear-btn", function () {
    $("#admin-leaderboard-search-input").val("");
    $("#admin-leaderboard-filter").val("annually").trigger("change");

    const table = $("#admin-leaderboard-table");
    if (table.length && $.fn.DataTable.isDataTable("#admin-leaderboard-table")) {
      table.DataTable().search("").draw();
    }
  });
  
});
