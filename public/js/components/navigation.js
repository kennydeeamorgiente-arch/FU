$(document).ready(function () {
  // Wait a tiny bit to ensure sidebar is fully rendered
  setTimeout(function () {
    // Load initial page based on current URL
    loadInitialPage();
  }, 100);

  // Handle navigation using data-link attributes (for both anchor tags and buttons)
  $(document).on("click", "a[data-link], button[data-link]", function (e) {
    e.preventDefault();

    const link = $(this).data("link");
    const $link = $(this);

    if (!link) return;

    // Get access level from sidebar (get it fresh each time)
    const accessId = parseInt($(".sidebar").data("access-id")) || 0;

    // Remove active class from all nav links
    $(".sidebar ul li a").removeClass("active");

    // Add active class to clicked link
    $link.addClass("active");

    // Determine base URL based on the link and access level
    let baseUrl = "";
    let linkPath = "";

    if (link.startsWith("admin/")) {
      baseUrl = "/admin/";
      linkPath = link.replace("admin/", "");
    } else if (link.startsWith("organization/")) {
      baseUrl = "/organization/";
      linkPath = link.replace("organization/", "");
    } else {
      // For generic links like "dashboard" or "leaderboards", determine based on access level
      if (accessId == 0) {
        // Organization user
        baseUrl = "/organization/";
        // Map dashboard to homepage for organization users
        if (link === "dashboard") {
          linkPath = "homepage";
        } else {
          linkPath = link;
        }
      } else {
        // Admin user
        baseUrl = "/admin/";
        linkPath = link;
      }
    }

    const fullUrl = baseUrl + linkPath;

    // Close any open modals when navigating
    $(".modal").removeClass("show");
    $("#modalBody").empty();

    // Update header text based on the page
    updateHeaderText(link);

    // Load content via AJAX
    loadPageContent(fullUrl);

    // Update browser history without reload
    if (window.history && window.history.pushState) {
      window.history.pushState({ path: fullUrl }, "", fullUrl);
    }
  });

  // Handle browser back/forward buttons
  window.addEventListener("popstate", function (e) {
    // Close any open modals when going back/forward
    $(".modal").removeClass("show");
    $("#modalBody").empty();

    if (e.state && e.state.path) {
      loadPageContent(e.state.path);
      updateActiveLink(e.state.path);
    } else {
      // If no state, reload current page content
      const currentPath = window.location.pathname;
      loadPageContent(currentPath);
      updateActiveLink(currentPath);
    }
  });

  // Handle logout link clicks
  $(document).on("click", ".logout-link", function (e) {
    e.preventDefault();
    e.stopPropagation();
    const $link = $(this);
    const logoutUrl =
      $link.attr("href") ||
      $link.attr("data-logout-url") ||
      $link.data("logout-url") ||
      "/user/logout";

    if (typeof handleLogout === "function") {
      // Use the handleLogout function from admin/main.js if available
      const element = {
        getAttribute: function (attr) {
          return logoutUrl;
        },
      };
      handleLogout(element);
    } else {
      // Fallback if handleLogout is not available
      if (confirm("Are you sure you want to logout?")) {
        window.location.href = logoutUrl;
      }
    }
    return false;
  });
});

// Global function for sidebar logout (can be called from onclick)
function handleSidebarLogout(event, element) {
  if (event) {
    event.preventDefault();
    event.stopPropagation();
  }

  const logoutUrl =
    element.getAttribute("href") ||
    element.getAttribute("data-logout-url") ||
    "/user/logout";

  if (typeof handleLogout === "function") {
    // Use the handleLogout function from admin/main.js if available
    const fakeElement = {
      getAttribute: function (attr) {
        return logoutUrl;
      },
    };
    handleLogout(fakeElement);
  } else {
    // Fallback if handleLogout is not available
    if (confirm("Are you sure you want to logout?")) {
      window.location.href = logoutUrl;
    }
  }

  return false;
}

// Load initial page based on current URL
function loadInitialPage() {
  // Close any open modals on initial page load
  $(".modal").removeClass("show");
  $("#modalBody").empty();

  const path = window.location.pathname;
  const segments = path.split("/").filter((s) => s);

  // Get access level from sidebar
  const accessId = parseInt($(".sidebar").data("access-id")) || 0;

  // If we're on the main index page (/), /index.php, or /verifyLogin, load the first nav item (Dashboard)
  if (
    segments.length === 0 ||
    (segments.length === 1 &&
      (segments[0] === "" ||
        segments[0] === "index.php" ||
        segments[0] === "verifyLogin"))
  ) {
    // Load dashboard based on access level
    if (accessId == 0) {
      // Organization user - load homepage
      loadPageContent("/organization/homepage");
      updateHeaderText("dashboard");
      $('.sidebar a[data-link="dashboard"]').addClass("active");
    } else {
      // Admin user - load dashboard
      loadPageContent("/admin/dashboard");
      updateHeaderText("dashboard");
      $('.sidebar a[data-link="dashboard"]').addClass("active");
    }
    return;
  }

  // If we're on a specific page route, load that page
  if (segments.length >= 3) {
    // Handle URLs with 3+ segments (e.g., /organization/profile/1)
    const base = segments[0];
    const page = segments[1];
    const id = segments[2];
    const fullPath = "/" + base + "/" + page + "/" + id;

    // Special handling for profile pages
    if (page === "profile") {
      loadPageContent(fullPath);
      updateHeaderText("profile");
      // Profile doesn't have a sidebar link, so don't set active link
    } else {
      loadPageContent(fullPath);
      updateActiveLink(fullPath);
      updateHeaderTextFromPath(fullPath);
    }
  } else if (segments.length >= 2) {
    const base = segments[0];
    const page = segments[1];

    // Special handling for profile page without ID
    if (page === "profile" && base === "organization") {
      // Get org_id from the profile link in header
      const $profileLink = $(".profile-link");
      let orgId = null;

      if ($profileLink.length > 0) {
        // Try data-org-id first
        orgId = $profileLink.data("org-id");

        // If not found, try to extract from data-link
        if (!orgId) {
          const profileLink = $profileLink.data("link");
          if (profileLink) {
            const linkSegments = profileLink.split("/");
            if (linkSegments.length >= 3) {
              orgId = linkSegments[2];
            }
          }
        }
      }

      if (orgId) {
        const fullPath = "/" + base + "/" + page + "/" + orgId;
        loadPageContent(fullPath);
        updateHeaderText("profile");
        return;
      } else {
        // If we can't get org_id, redirect to homepage
        if (accessId == 0) {
          loadPageContent("/organization/homepage");
          updateHeaderText("dashboard");
          $('.sidebar a[data-link="dashboard"]').addClass("active");
          return;
        }
      }
    }

    const fullPath = "/" + base + "/" + page;
    loadPageContent(fullPath);
    updateActiveLink(fullPath);
    updateHeaderTextFromPath(fullPath);
  } else if (segments.length === 1) {
    // Single segment - could be admin or organization
    const segment = segments[0];
    if (segment === "admin" || segment === "organization") {
      // Load dashboard based on access level
      if (accessId == 0) {
        loadPageContent("/organization/homepage");
        updateHeaderText("dashboard");
        $('.sidebar a[data-link="dashboard"]').addClass("active");
      } else {
        loadPageContent("/admin/dashboard");
        updateHeaderText("dashboard");
        $('.sidebar a[data-link="dashboard"]').addClass("active");
      }
    }
  }
}

// Load page content via AJAX
function loadPageContent(url) {
  // Close any open modals when navigating to a new page
  $(".modal").removeClass("show");
  $("#modalBody").empty();
  const $contentWrapper = $("#content-wrapper");

  // Show loading indicator
  $contentWrapper.html(
    '<div style="text-align: center; padding: 50px;"><i class="fa-solid fa-spinner fa-spin fa-2x"></i><p>Loading...</p></div>',
  );

  $.ajax({
    url: url,
    method: "GET",
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
    success: function (response) {
      // Check if response is already HTML content or a full page
      let content = response;

      // Try to extract container content from full page
      if (typeof response === "string" && response.trim().startsWith("<!")) {
        const $response = $(response);

        // If we got the main layout, try to get the content wrapper
        const contentWrapper = $response.find("#content-wrapper").html();

        // Look for .container inside .main-content first, then just .container
        const mainContent = $response.find(".main-content .container").html();
        const containerContent = $response.find(".container").html();

        // For pages that still have wrapper/main-content structure, extract just the container
        const pageContainer = $response
          .find("body .wrapper .main-content .container")
          .html();

        // For organization pages with just container
        const orgContainer = $response.find("body .container").html();

        // For pages that return just a div.container (our new structure)
        const directContainer = $response.find(".container").first().html();

        content =
          contentWrapper ||
          pageContainer ||
          mainContent ||
          containerContent ||
          orgContainer ||
          directContainer ||
          response;
      }

      // If content is empty or undefined, use the response as-is
      if (!content || content.trim() === "") {
        content = response;
      }

      $contentWrapper.html(content);

      // Reinitialize any scripts that need to run
      if (typeof initPageScripts === "function") {
        initPageScripts();
      }

      // Load page-specific scripts if they exist
      loadPageScripts(url);

      // Trigger a custom event for page load
      $(document).trigger("pageContentLoaded", [url]);
    },
    error: function (xhr, status, error) {
      console.error("Error loading page:", url, error, xhr);
      $contentWrapper.html(`
        <div style="text-align: center; padding: 50px;">
          <h3>Error loading page</h3>
          <p>${error || "Unable to load the requested page"}</p>
          <p style="color: #666; font-size: 12px; margin-top: 10px;">URL: ${url}</p>
          <p style="color: #666; font-size: 12px;">Status: ${xhr.status} ${xhr.statusText}</p>
          <button onclick="location.reload()" style="margin-top: 20px; padding: 10px 20px; cursor: pointer;">Reload Page</button>
        </div>
      `);
    },
  });
}

// Load page-specific scripts based on URL
function loadPageScripts(url) {
  // Remove existing dynamic scripts
  $("script[data-dynamic-script]").remove();

  // Map URLs to their required scripts
  const scriptMap = {
    "/admin/dashboard": ["js/admin/events.js", "js/organization/event.js"],
    "/admin/manage-events": [
      "js/admin/events.js",
      "js/organization/event.js",
      "js/data-table.js",
    ],
    "/admin/organizations": ["js/admin/organizations.js"],
    "/admin/users": ["js/admin/users.js"],
    "/admin/leaderboards": ["js/org.js"],
    "/organization/homepage": ["js/organization/event.js"],
    "/organization/track-event": ["js/organization/event.js"],
    "/organization/host-event": ["js/organization/event.js"],
    "/organization/log-event": ["js/organization/event.js"],
    "/organization/leaderboards": ["js/org.js"],
  };

  // Find matching scripts
  let scriptsToLoad = [];
  for (const [path, scripts] of Object.entries(scriptMap)) {
    if (url.includes(path)) {
      scriptsToLoad = scripts;
      break;
    }
  }

  // Load scripts dynamically, checking if they're already loaded
  scriptsToLoad.forEach(function (scriptPath) {
    // Check if script is already loaded
    const scriptId =
      "script-" + scriptPath.replace(/\//g, "-").replace(/\.js$/, "");
    if (document.getElementById(scriptId)) {
      return; // Skip if already loaded
    }

    const script = document.createElement("script");
    script.id = scriptId;
    script.src = "/" + scriptPath;
    script.setAttribute("data-dynamic-script", "true");
    document.body.appendChild(script);
  });
}

// Update header text based on page
function updateHeaderText(link) {
  // Get access level to determine appropriate header text
  const accessId = parseInt($(".sidebar").data("access-id")) || 0;

  const headerTexts = {
    dashboard: accessId == 0 ? "Home" : "Dashboard",
    homepage: "Home",
    leaderboards: "Leaderboards",
    "manage-events": "Manage Events",
    "admin/manage-events": "Manage Events",
    "track-event": "My Events",
    "organization/track-event": "My Events",
    "host-event": "Host Event",
    "organization/host-event": "Host Event",
    "log-event": "Log Activity",
    "organization/log-event": "Log an Activity",
    organizations: "Organizations",
    "admin/organizations": "Organizations",
    users: "Users",
    "admin/users": "Users",
    profile: "Profile",
    "organization/profile": "Profile",
  };

  const headerText =
    headerTexts[link] ||
    link
      .split("/")
      .pop()
      .replace(/-/g, " ")
      .replace(/\b\w/g, (l) => l.toUpperCase());
  $("#pageHeader").text(headerText);
}

// Update header text from path
function updateHeaderTextFromPath(path) {
  const segments = path.split("/").filter((s) => s);
  if (segments.length >= 2) {
    const page = segments[1];
    const fullPath = segments[0] + "/" + page;
    updateHeaderText(fullPath);
  } else if (segments.length === 1) {
    updateHeaderText(segments[0]);
  }
}

// Update active link based on path
function updateActiveLink(path) {
  $(".sidebar ul li a").removeClass("active");
  const segments = path.split("/").filter((s) => s);

  if (segments.length >= 2) {
    const base = segments[0];
    const page = segments[1];
    const fullPath = base + "/" + page;

    // Special case: homepage should match dashboard link for organization users
    if (base === "organization" && page === "homepage") {
      const $link = $('.sidebar a[data-link="dashboard"]');
      if ($link.length > 0) {
        $link.addClass("active");
        return;
      }
    }

    // Try to find exact match first
    let $link = $(`.sidebar a[data-link="${fullPath}"]`);

    // If not found, try just the page name
    if ($link.length === 0) {
      $link = $(`.sidebar a[data-link="${page}"]`);
    }

    // If still not found, try with base prefix
    if ($link.length === 0 && base === "admin") {
      $link = $(`.sidebar a[data-link="admin/${page}"]`);
    }

    if ($link.length > 0) {
      $link.addClass("active");
    }
  } else if (segments.length === 1) {
    const page = segments[0];
    const $link = $(`.sidebar a[data-link="${page}"]`);
    if ($link.length > 0) {
      $link.addClass("active");
    }
  }
}

// Navigate to page (can be called from other scripts)
function navigateToPage(element) {
  const link = $(element).data("link");
  if (link) {
    $('a[data-link="' + link + '"]').click();
  }
}

// Navigate to profile page (handles organization profile links with IDs)
function navigateToProfile(element) {
  const $element = $(element);
  let profileId = null;

  // Try to get org_id from data-org-id attribute first
  profileId = $element.data("org-id");

  // If not found, try to extract from data-link
  if (!profileId) {
    const link = $element.data("link");
    if (link) {
      const segments = link.split("/");
      if (
        segments.length >= 3 &&
        segments[0] === "organization" &&
        segments[1] === "profile"
      ) {
        profileId = segments[2];
      }
    }
  }

  if (!profileId) {
    console.error("Organization ID not found for profile");
    return;
  }

  // Close the user dropdown
  $("#userDropdown").addClass("hide");
  $(".user-icon").removeClass("active");

  const fullUrl = "/organization/profile/" + profileId;

  // Update header text
  updateHeaderText("profile");

  // Load content via AJAX
  loadPageContent(fullUrl);

  // Update browser history
  if (window.history && window.history.pushState) {
    window.history.pushState({ path: fullUrl }, "", fullUrl);
  }
}

// Toggle Sidebar Function
function toggleSidebar() {
  const sidebar = document.querySelector(".sidebar");
  const wrapper = document.querySelector(".wrapper");
  const toggleIcon = document.querySelector("#sidebarToggleIcon");

  if (!sidebar || !wrapper) return;

  // Toggle hidden class
  sidebar.classList.toggle("hidden");
  wrapper.classList.toggle("sidebar-hidden");

  // Update icon - bars when hidden, X when visible
  if (toggleIcon) {
    if (sidebar.classList.contains("hidden")) {
      toggleIcon.className = "fa-solid fa-bars";
    } else {
      toggleIcon.className = "fa-solid fa-xmark";
    }
  }

  // Save state to localStorage
  const isHidden = sidebar.classList.contains("hidden");
  localStorage.setItem("sidebarHidden", isHidden);
}

// Initialize sidebar state on page load
$(document).ready(function () {
  // Check if sidebar should be hidden from localStorage
  const sidebarHidden = localStorage.getItem("sidebarHidden") === "true";
  const sidebar = document.querySelector(".sidebar");
  const wrapper = document.querySelector(".wrapper");
  const toggleIcon = document.querySelector("#sidebarToggleIcon");

  if (sidebarHidden && sidebar && wrapper) {
    sidebar.classList.add("hidden");
    wrapper.classList.add("sidebar-hidden");
    // Set icon to bars when sidebar is hidden
    if (toggleIcon) {
      toggleIcon.className = "fa-solid fa-bars";
    }
  } else {
    // Set icon to X when sidebar is visible
    if (toggleIcon) {
      toggleIcon.className = "fa-solid fa-xmark";
    }
  }
});
