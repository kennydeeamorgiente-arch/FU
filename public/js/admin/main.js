$(document).ready(function () {
  // Get the current path (e.g., /admin/dashboard)
  let path = window.location.pathname;

  // Extract the last segment after the last slash
  let page = path.split('/').pop();

  // Set default header text
  let headerText = "I am header";

  // Remove any existing active states first
  $(".sidebar ul li a").removeClass("active");

  // Change header text and set active link based on the page segment
  switch (page) {
    case "dashboard":
      headerText = "Dashboard";
      $(".sidebar a[href$='dashboard']").addClass("active");
      break;
    case "leaderboards":
      headerText = "Leaderboards";
      $(".sidebar a[href$='leaderboards']").addClass("active");
      break;
    case "manage-events":
      headerText = "Manage Events";
      $(".sidebar a[href$='manage-events']").addClass("active");
      break;
    case "settings":
      headerText = "Settings";
      $(".sidebar a[href$='settings']").addClass("active");
      break;
    case "users":
      headerText = "Manage Organizations";
      $(".sidebar a[href$='users']").addClass("active");
      break;
    default:
      headerText = "Admin Panel";
  }

  // Update header text dynamically
  $("#pageHeader").text(headerText);
  
  // Load notifications from API
  loadNotifications(true);
  
  // Refresh notifications periodically to keep DB-based timestamps and ordering current.
  if (window.__adminNotificationRefreshTimer) {
    clearInterval(window.__adminNotificationRefreshTimer);
  }
  window.__adminNotificationRefreshTimer = setInterval(function () {
    loadNotifications();
  }, 30000);
  
  // Close dropdowns when clicking outside
  $(document).on("click", function (e) {
    // Don't close if clicking on the icon itself (handled by toggle function)
    if ($(e.target).closest(".notification-icon").length || 
        $(e.target).closest(".user-icon").length) {
      return;
    }
    
    if (!$(e.target).closest(".notification-container").length) {
      $("#notificationDropdown").addClass("hide");
      $(".notification-icon").removeClass("active");
    }
    if (!$(e.target).closest(".user-dropdown").length) {
      $("#userDropdown").addClass("hide");
      $(".user-icon").removeClass("active");
    }
  });
});

// Store notifications globally for unread count calculation
let currentNotifications = [];
let notificationsRequestInFlight = false;

// Load notifications from API
function loadNotifications(forceRefresh = false) {
  if (notificationsRequestInFlight && !forceRefresh) {
    return;
  }

  notificationsRequestInFlight = true;
  let baseUrl = window.location.pathname.includes('/admin/') ? '/admin' : '/organization';
  
  console.log('Loading notifications from:', baseUrl + '/notifications');
  
  $.ajax({
    url: baseUrl + '/notifications',
    method: 'GET',
    dataType: 'json',
    cache: false, // Prevent caching
    success: function(response) {
      console.log('Notification API response:', response);
      if (response.status === 'success') {
        let notifications = response.data || response.notifications || [];
        notifications = sortNotificationsNewestFirst(notifications);
        console.log('Parsed notifications:', notifications);
        console.log('Number of notifications:', notifications.length);
        currentNotifications = notifications; // Store for unread count
        renderNotifications(notifications);
        // Update badge count after notifications are loaded
        updateUnreadCount();
      } else {
        console.log('API returned non-success status:', response.status);
        currentNotifications = [];
        updateUnreadCount();
      }
      notificationsRequestInFlight = false;
    },
    error: function(xhr, status, error) {
      console.error('Error loading notifications:', error, xhr);
      console.error('Status:', status, 'Response:', xhr.responseText);
      currentNotifications = [];
      updateUnreadCount();
      notificationsRequestInFlight = false;
    }
  });
}

function sortNotificationsNewestFirst(notifications) {
  if (!Array.isArray(notifications)) {
    return [];
  }

  return [...notifications].sort(function (a, b) {
    const timeA = parseNotificationTimestampMs(a);
    const timeB = parseNotificationTimestampMs(b);
    const safeA = Number.isNaN(timeA) ? 0 : timeA;
    const safeB = Number.isNaN(timeB) ? 0 : timeB;

    if (safeA === safeB) {
      const eventA = Number(a && a.event_id) || 0;
      const eventB = Number(b && b.event_id) || 0;
      return eventB - eventA;
    }

    return safeB - safeA;
  });
}

// Update unread count badge
function updateUnreadCount() {
  // Get seen notifications from localStorage
  let userId = $("#notificationDropdown").data("user-id") || "default";
  let storageKey = "notifications_seen_" + userId;
  let seenNotifications = JSON.parse(localStorage.getItem(storageKey) || "{}");
  
  console.log('updateUnreadCount - User ID:', userId, 'Storage key:', storageKey);
  console.log('updateUnreadCount - Seen notifications from localStorage:', seenNotifications);
  console.log('updateUnreadCount - currentNotifications:', currentNotifications);
  console.log('updateUnreadCount - currentNotifications length:', currentNotifications ? currentNotifications.length : 0);
  
  // Count only unseen notifications from currentNotifications array
  let unseenCount = 0;
  if (currentNotifications && currentNotifications.length > 0) {
    currentNotifications.forEach(function(notif) {
      let notifId = notif.notification_id || notif.event_id;
      console.log('Checking notification:', notifId, 'Seen?', seenNotifications[notifId]);
      // Only count if notification hasn't been seen
      if (notifId && !seenNotifications[notifId]) {
        unseenCount++;
      }
    });
  }
  
  console.log('Unread count:', unseenCount, 'Total notifications:', currentNotifications ? currentNotifications.length : 0);
  
  // Find badge in admin header specifically
  let badge = $(".admin-header .notification-badge, .notification-icon .notification-badge");
  if (badge.length === 0) {
    badge = $(".notification-badge");
  }
  
  console.log('Badge element found:', badge.length, 'Badge element:', badge);
  
  if (unseenCount > 0) {
    badge.text(unseenCount > 99 ? '99+' : unseenCount);
    badge.css('display', 'flex'); // Use flex to match CSS definition
    console.log('Badge shown with count:', unseenCount, 'Badge element found:', badge.length);
  } else {
    badge.css('display', 'none'); // Use display none instead of hide() to override inline style
    console.log('Badge hidden - unseenCount is 0');
  }
}

// Render notifications in the dropdown
function renderNotifications(notifications) {
  let notificationList = $("#notificationList");
  notificationList.empty();
  
  if (!notifications || notifications.length === 0) {
    notificationList.html('<div class="notification-item"><p>No notifications</p></div>');
    updateUnreadCount();
    return;
  }
  
  // Get read status from localStorage
  let userId = $("#notificationDropdown").data("user-id") || "default";
  let storageKey = "notifications_seen_" + userId;
  let seenNotifications = JSON.parse(localStorage.getItem(storageKey) || "{}");
  
  notifications.forEach(function(notif) {
    let notifId = notif.notification_id || notif.event_id;
    let isRead = seenNotifications[notifId] || false;
    let timeAgo = getTimeAgo(notif);
    
    // Highlight unseen notifications
    let highlightStyle = isRead ? '' : 'background: #E3F2FD; border-left: 3px solid #2196F3; font-weight: bold;';
    let dotHtml = isRead ? '' : '<span style="display: inline-block; width: 8px; height: 8px; background: #2196F3; border-radius: 50%; margin-right: 8px;"></span>';
    
    let notificationHtml = `
      <div class="notification-item ${isRead ? 'seen' : ''}" 
           data-notification-id="${notifId}" 
           data-seen="${isRead ? 'true' : 'false'}"
           data-event-id="${notif.event_id || ''}"
           data-notification-type="${notif.type || ''}"
           style="cursor: pointer; ${highlightStyle}">
        ${dotHtml}
        <p>${escapeHtml(notif.message)}</p>
        <span class="notification-time">${timeAgo}</span>
      </div>
    `;
    
    notificationList.append(notificationHtml);
  });
  
  // Add click handler for notifications (rebind safely to avoid duplicate handlers)
  $(document).off('click.notificationItem').on('click.notificationItem', '.notification-item', function() {
    let notifId = $(this).data("notification-id");
    let eventId = $(this).data("event-id");
    let notificationType = $(this).data("notificationType") || $(this).attr("data-notification-type") || "";
    
    // Mark notification as seen when clicked
    if (notifId) {
      let userId = $("#notificationDropdown").data("user-id") || "default";
      let storageKey = "notifications_seen_" + userId;
      let seenNotifications = JSON.parse(localStorage.getItem(storageKey) || "{}");
      seenNotifications[notifId] = true;
      localStorage.setItem(storageKey, JSON.stringify(seenNotifications));
      
      // Update the UI to reflect it's been seen
      $(this).data("seen", "true");
      $(this).removeClass('unseen').addClass('seen');
      $(this).css({
        'background': '',
        'border-left': '',
        'font-weight': ''
      });
      $(this).find('span[style*="background: #2196F3"]').remove();
      
      // Update badge count
      updateUnreadCount();
    }
    
    if (eventId) {
      handleNotificationClick(eventId, notificationType);
    }
  });
  
  // Update badge count after rendering
  updateUnreadCount();
}

function handleNotificationClick(eventId, notificationType) {
  const isAdminPage = window.location.pathname.includes('/admin/');

  if (isAdminPage) {
    handleAdminNotificationClick(eventId);
    return;
  }

  handleOrganizationNotificationClick(eventId, notificationType);
}

function handleOrganizationNotificationClick(eventId, notificationType) {
  $("#notificationDropdown").addClass("hide");
  $(".notification-icon").removeClass("active");

  const modal = $("#orgModal");
  if (modal.length === 0) {
    window.location.href = `${BASE_URL}organization/track-event`;
    return;
  }

  const normalizedType = String(notificationType || "").toLowerCase();
  const openEditForm =
    normalizedType === 'event_revision' ||
    normalizedType === 'event_rejected' ||
    normalizedType.includes('revision') ||
    normalizedType.includes('rejected');
  const modalTitle = openEditForm ? 'Edit Event' : 'Event Details';
  const loadUrl = openEditForm
    ? `${BASE_URL}organization/host-event/edit/${eventId}`
    : `${BASE_URL}organization/manage-event/view-event/${eventId}`;

  $("#modalTitle").text(modalTitle);
  $("#modalBody").html('<div style="text-align: center; padding: 50px;"><i class="fa-solid fa-spinner fa-spin fa-2x"></i><p>Loading event...</p></div>');
  modal.addClass("show");

  $("#modalBody").load(loadUrl, function(response, status, xhr) {
    if (status !== "success") {
      $("#modalBody").html('<div style="text-align: center; padding: 50px;"><p style="color: red;">Error loading event. Please try again.</p><button onclick="$(\"#orgModal\").removeClass(\"show\");" style="padding: 10px 20px; background: #8b0000; color: white; border: none; border-radius: 5px; cursor: pointer; margin-top: 20px;">Close</button></div>');
      console.error("Error loading organization event:", xhr.status, xhr.statusText, "URL:", loadUrl);
    }
  });
}

// Parse notification timestamps consistently (handles UTC SQL timestamps safely)
function parseNotificationTimestampMs(notificationOrDate) {
  if (notificationOrDate && typeof notificationOrDate === "object") {
    const unixSeconds = Number(notificationOrDate.created_at_unix);
    if (Number.isFinite(unixSeconds) && unixSeconds > 0) {
      return unixSeconds * 1000;
    }

    if (notificationOrDate.created_at_iso) {
      const isoMs = Date.parse(notificationOrDate.created_at_iso);
      if (!Number.isNaN(isoMs)) {
        return isoMs;
      }
    }
  }

  const rawDate =
    notificationOrDate && typeof notificationOrDate === "object"
      ? notificationOrDate.created_at
      : notificationOrDate;

  if (!rawDate) {
    return NaN;
  }

  const rawString = String(rawDate).trim();
  if (!rawString) {
    return NaN;
  }

  // SQL DATETIME from backend has no timezone. Treat it as UTC.
  if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(rawString)) {
    const utcMs = Date.parse(rawString.replace(" ", "T") + "Z");
    if (!Number.isNaN(utcMs)) {
      return utcMs;
    }
  }

  return Date.parse(rawString);
}

// Get time ago string
function getTimeAgo(notificationOrDate) {
  const timestampMs = parseNotificationTimestampMs(notificationOrDate);
  if (Number.isNaN(timestampMs)) {
    return "Unknown time";
  }

  let diffInSeconds = Math.floor((Date.now() - timestampMs) / 1000);

  // Handle slight client/server clock drift.
  if (diffInSeconds < 0) {
    if (Math.abs(diffInSeconds) <= 30) {
      diffInSeconds = 0;
    } else {
      return "Just now";
    }
  }

  if (diffInSeconds < 60) {
    return "Just now";
  }

  if (diffInSeconds < 3600) {
    const minutes = Math.floor(diffInSeconds / 60);
    return minutes + " minute" + (minutes > 1 ? "s" : "") + " ago";
  }

  if (diffInSeconds < 86400) {
    const hours = Math.floor(diffInSeconds / 3600);
    return hours + " hour" + (hours > 1 ? "s" : "") + " ago";
  }

  if (diffInSeconds < 604800) {
    const days = Math.floor(diffInSeconds / 86400);
    return days + " day" + (days > 1 ? "s" : "") + " ago";
  }

  return new Date(timestampMs).toLocaleString();
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
  let map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

// Update notification badge count
function updateNotificationBadge() {
  let unseenCount = 0;
  $("#notificationList .notification-item").each(function() {
    if ($(this).attr("data-seen") === "false") {
      unseenCount++;
    }
  });
  
  let badge = $(".notification-badge");
  if (unseenCount > 0) {
    badge.text(unseenCount > 99 ? '99+' : unseenCount);
    badge.show();
  } else {
    badge.hide();
  }
}

// Mark all notifications as seen (using localStorage)
function markAllNotificationsAsSeen() {
  let userId = $("#notificationDropdown").data("user-id") || "default";
  let storageKey = "notifications_seen_" + userId;
  let seenNotifications = JSON.parse(localStorage.getItem(storageKey) || "{}");
  
  $("#notificationList .notification-item").each(function() {
    let notifId = $(this).data("notification-id");
    if (notifId) {
      seenNotifications[notifId] = true;
      $(this).attr("data-seen", "true");
      $(this).addClass("seen").removeClass("unseen").css("background", "");
      $(this).css("border-left", "").css("font-weight", "");
      $(this).find("span[style*='background: #2196F3']").remove();
    }
  });
  
  localStorage.setItem(storageKey, JSON.stringify(seenNotifications));
  updateUnreadCount();
}

// Handle admin notification click - opens event in modal
function handleAdminNotificationClick(eventId) {
  $("#notificationDropdown").addClass("hide");
  $(".notification-icon").removeClass("active");
  
  var modal = $("#orgModal");
  if (modal.length > 0) {
    // Show modal immediately with loading state
    $("#modalTitle").text("Event Details");
    $("#modalBody").html('<div style="text-align: center; padding: 50px;"><i class="fa-solid fa-spinner fa-spin fa-2x"></i><p>Loading event...</p></div>');
    modal.addClass("show");
    
    // Load event content
    $("#modalBody").load(
      `${BASE_URL}admin/manage-events/view/${eventId}`,
      function(response, status, xhr) {
        console.log("📥 LOAD RESPONSE STATUS:", status);
        console.log("📥 LOAD RESPONSE LENGTH:", response ? response.length : "NULL");
        
        if (status == "success") {
          $("#modalBody #btn-back-to-events").hide();
          
          // Wait for content to fully load before checking buttons
          setTimeout(function() {
            console.log("🔍 CHECKING BUTTONS AFTER LOAD...");
            let submitButton = $("#btn-submit");
            let approveButton = $("#btn-accept");
            console.log("Submit button found after load:", submitButton.length > 0 ? "YES" : "NO");
            console.log("Approve button found after load:", approveButton.length > 0 ? "YES" : "NO");
            console.log("Modal HTML length:", $("#modalBody").html().length);
          }, 500); // Wait 500ms for content to render
          
        } else {
          $("#modalBody").html('<div style="text-align: center; padding: 50px;"><p style="color: red;">Error loading event. Please try again.</p><button onclick="$(\"#orgModal\").removeClass(\"show\");" style="padding: 10px 20px; background: #8b0000; color: white; border: none; border-radius: 5px; cursor: pointer; margin-top: 20px;">Close</button></div>');
          console.error("Error loading event:", xhr.status, xhr.statusText);
        }
      }
    );
  } else {
    window.location.href = `${BASE_URL}admin/manage-events`;
    sessionStorage.setItem('loadEventId', eventId);
  }
}

// Toggle notification dropdown
function toggleNotificationDropdown(event) {
  if (event) {
    event.stopPropagation();
  }
  
  let isHidden = $("#notificationDropdown").hasClass("hide");
  
  // Always toggle the dropdown
  $("#notificationDropdown").toggleClass("hide");
  
  // Close user dropdown if open
  $("#userDropdown").addClass("hide");
  $(".user-icon").removeClass("active");
  
  // Toggle active class for icon based on new state
  if (isHidden) {
    // Opening dropdown - DO NOT mark as seen automatically
    // Notifications should only be marked as seen when clicked
    $(".notification-icon").addClass("active");
  } else {
    // Closing dropdown
    $(".notification-icon").removeClass("active");
  }
}

// Toggle user dropdown
function toggleUserDropdown(event) {
  if (event) {
    event.stopPropagation();
  }
  
  let isHidden = $("#userDropdown").hasClass("hide");
  
  // Always toggle the dropdown
  $("#userDropdown").toggleClass("hide");
  
  // Close notification dropdown if open
  $("#notificationDropdown").addClass("hide");
  $(".notification-icon").removeClass("active");
  
  // Toggle active class for icon based on new state
  if (isHidden) {
    // Opening dropdown
    $(".user-icon").addClass("active");
  } else {
    // Closing dropdown
    $(".user-icon").removeClass("active");
  }
}

// Handle logout
function handleLogout(element) {
  if (confirm("Are you sure you want to logout?")) {
    let logoutUrl = element ? element.getAttribute("data-logout-url") : "/user/logout";
    window.location.href = logoutUrl;
  }
}
