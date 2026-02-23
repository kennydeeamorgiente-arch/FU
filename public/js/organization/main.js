$(document).ready(function () {
  // Profile dropdown
  $("#user").click(function (e) {
    e.stopPropagation();
    $(".profile-container").toggleClass("hide");
    $("#notificationDropdown").addClass("hide");
  });

  $(".profile-container h4 a").click(function () {
    let orgId = $(this).data("org");
    console.log(orgId);
    $(".container").load(`${BASE_URL}organization/profile/${orgId}`);
    $(".profile-container").addClass("hide");
  });
  
  // Load notifications on page load
  loadNotifications();
  
  // Set up auto-refresh for notifications every 30 seconds
  setInterval(loadNotifications, 30000);
  
  // Close dropdowns when clicking outside
  $(document).on("click", function (e) {
    if ($(e.target).closest(".notification-icon").length || 
        $(e.target).closest(".user").length) {
      return;
    }
    
    if (!$(e.target).closest(".notification-container").length) {
      $("#notificationDropdown").addClass("hide");
      $(".notification-icon").removeClass("active");
    }
    if (!$(e.target).closest(".profile-container").length) {
      $(".profile-container").addClass("hide");
    }
  });
});

// Toggle notification dropdown
function toggleNotificationDropdown(event) {
  if (event) {
    event.stopPropagation();
  }
  
  let isHidden = $("#notificationDropdown").hasClass("hide");
  
  // Always toggle the dropdown
  $("#notificationDropdown").toggleClass("hide");
  $(".profile-container").addClass("hide");
  
  // Toggle active class for icon based on new state
  if (isHidden) {
    // Opening dropdown
    $(".notification-icon").addClass("active");
    markAllNotificationsAsSeen();
  } else {
    // Closing dropdown
    $(".notification-icon").removeClass("active");
  }
}

// Store notifications globally for unread count calculation
let currentNotifications = [];

// Load notifications from API
function loadNotifications() {
  console.log('Loading notifications from:', BASE_URL + 'organization/notifications');
  
  $.ajax({
    url: BASE_URL + 'organization/notifications',
    method: 'GET',
    dataType: 'json',
    cache: false, // Prevent caching
    success: function(response) {
      console.log('Notifications response:', response); // Debug log
      console.log('Response status:', response.status);
      console.log('Response data type:', typeof response.data);
      console.log('Response data:', response.data);
      
      if (response.status === 'success') {
        let notifications = response.data || response.notifications || [];
        console.log('Notifications count:', notifications.length); // Debug log
        currentNotifications = notifications; // Store for unread count
        renderNotifications(notifications);
        // Update badge count after notifications are loaded
        updateUnreadCount();
      } else {
        console.error('Notification response error:', response);
        currentNotifications = [];
        updateUnreadCount();
      }
    },
    error: function(xhr, status, error) {
      console.error('Error loading notifications:', error, xhr);
      currentNotifications = [];
      updateUnreadCount();
    }
  });
}

// Update unread count badge
function updateUnreadCount() {
  // Get seen notifications from localStorage
  let userId = $("#notificationDropdown").data("user-id") || "default";
  let storageKey = "notifications_seen_" + userId;
  let seenNotifications = JSON.parse(localStorage.getItem(storageKey) || "{}");
  
  // Count only unseen notifications from currentNotifications array
  let unseenCount = 0;
  if (currentNotifications && currentNotifications.length > 0) {
    currentNotifications.forEach(function(notif) {
      let notifId = notif.notification_id || notif.event_id;
      // Only count if notification hasn't been seen
      if (notifId && !seenNotifications[notifId]) {
        unseenCount++;
      }
    });
  }
  
  console.log('Unread count:', unseenCount, 'Total notifications:', currentNotifications ? currentNotifications.length : 0);
  
  let badge = $(".notification-badge");
  if (unseenCount > 0) {
    badge.text(unseenCount > 99 ? '99+' : unseenCount);
    badge.show();
    console.log('Badge shown with count:', unseenCount);
  } else {
    badge.hide();
    console.log('Badge hidden');
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
    let timeAgo = getTimeAgo(notif.created_at);
    
    // Highlight unseen notifications
    let highlightStyle = isRead ? '' : 'background: #E3F2FD; border-left: 3px solid #2196F3; font-weight: bold;';
    let dotHtml = isRead ? '' : '<span style="display: inline-block; width: 8px; height: 8px; background: #2196F3; border-radius: 50%; margin-right: 8px;"></span>';
    
    let notificationHtml = `
      <div class="notification-item ${isRead ? 'seen' : ''}" 
           data-notification-id="${notifId}" 
           data-seen="${isRead ? 'true' : 'false'}"
           data-event-id="${notif.event_id || ''}"
           style="cursor: pointer; ${highlightStyle}">
        ${dotHtml}
        <p>${escapeHtml(notif.message)}</p>
        <span class="notification-time">${timeAgo}</span>
      </div>
    `;
    
    notificationList.append(notificationHtml);
  });
  
  // Add click handler for notifications - MUST prevent admin handler from running
  // Use event.stopImmediatePropagation() to prevent other handlers
  $(document).off('click', '.notification-item').on('click', '.notification-item', function(e) {
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation(); // Prevent admin/main.js handler from running
    
    // Make sure we're in organization context
    let $dropdown = $(this).closest('#notificationDropdown');
    if ($dropdown.length === 0) {
      console.log("Not in organization notification dropdown, ignoring click");
      return false; // Not in organization notification dropdown
    }
    
    let notifId = $(this).data("notification-id");
    let eventId = $(this).data("event-id");
    
    console.log("Organization notification item clicked - Event ID:", eventId);
    
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
      console.log("Calling handleNotificationClick (organization) with eventId:", eventId);
      handleNotificationClick(eventId);
    } else {
      console.error("No eventId found in notification");
    }
    
    return false; // Prevent any default behavior
  });
  
  // Update badge count after rendering
  updateUnreadCount();
}

// Handle notification click - opens event details in modal
// EXACT COPY of view-event action from org-events.js (lines 204-241)
function handleNotificationClick(eventId) {
  // Close notification dropdown first
  $("#notificationDropdown").addClass("hide");
  $(".notification-icon").removeClass("active");
  
  // EXACT COPY - Same as clicking .view-btn in org-events.js
  var modal = $("#orgModal");
  
  if (!eventId) {
    console.error("Event ID not found");
    alert("Error: Event ID not found");
    return;
  }
  
  // Show modal immediately with loading state
  $("#modalTitle").text("Event Details");
  $("#modalBody").html('<div style="text-align: center; padding: 50px;"><i class="fa-solid fa-spinner fa-spin fa-2x"></i><p>Loading event details...</p></div>');
  modal.addClass("show");
  
  // Build the URL - EXACT SAME as view-event action
  var loadUrl = `${BASE_URL}organization/manage-event/view-event/${eventId}`;
  console.log("Loading event from URL:", loadUrl);
  
  // Load event content - SAME URL as view-event action
  $("#modalBody").load(
    loadUrl,
    function(response, status, xhr) {
      console.log("Load response - Status:", status, "XHR Status:", xhr.status);
      if (status == "error") {
        console.error("Error loading event:", xhr.status, xhr.statusText);
        console.error("Response URL:", xhr.responseURL);
        $("#modalBody").html(
          '<div style="text-align: center; padding: 50px;">' +
          '<p style="color: red;">Error loading event details. Please try again.</p>' +
          '<button onclick="$(\'#orgModal\').removeClass(\'show\');" style="padding: 10px 20px; background: #8b0000; color: white; border: none; border-radius: 5px; cursor: pointer; margin-top: 20px;">Close</button>' +
          '</div>'
        );
      } else if (status == "success") {
        // Content loaded successfully
        console.log("Event details loaded successfully");
      }
    }
  );
  
  // Mark the specific notification as seen in localStorage
  let userId = $("#notificationDropdown").data("user-id") || "default";
  let storageKey = "notifications_seen_" + userId;
  let seenNotifications = JSON.parse(localStorage.getItem(storageKey) || "{}");
  
  // Find the specific notification item and mark it as seen
  let $notificationItem = $(`.notification-item[data-event-id="${eventId}"]`);
  if ($notificationItem.length > 0) {
    let notifId = $notificationItem.data("notification-id");
    if (notifId) {
      seenNotifications[notifId] = true;
      localStorage.setItem(storageKey, JSON.stringify(seenNotifications));
      
      // Update its visual state
      $notificationItem.attr("data-seen", "true");
      $notificationItem.addClass("seen").removeClass("unseen").css("background", "");
      $notificationItem.css("border-left", "").css("font-weight", "");
      $notificationItem.find("span[style*='background: #2196F3']").remove();
    }
  }
  
  updateNotificationBadge();
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

// Get time ago string
function getTimeAgo(dateString) {
  let date = new Date(dateString);
  let now = new Date();
  let diff = now - date;
  let seconds = Math.floor(diff / 1000);
  let minutes = Math.floor(seconds / 60);
  let hours = Math.floor(minutes / 60);
  let days = Math.floor(hours / 24);
  
  if (days > 0) return days + (days === 1 ? ' day' : ' days') + ' ago';
  if (hours > 0) return hours + (hours === 1 ? ' hour' : ' hours') + ' ago';
  if (minutes > 0) return minutes + (minutes === 1 ? ' minute' : ' minutes') + ' ago';
  return 'Just now';
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
    alert('End date must be after start date');
    return;
  }
  
  // Calculate days between
  const daysDifference = Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
  
  let tableHTML = `
    <thead>
      <tr style="background-color: #8b0000; color: white;">
        <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Day</th>
        <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Date</th>
        <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Start Time</th>
        <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">End Time</th>
        <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Activities/Notes</th>
      </tr>
    </thead>
    <tbody>
  `;
  
  // Generate rows for each day
  for (let i = 0; i < daysDifference; i++) {
    const currentDate = new Date(startDate);
    currentDate.setDate(currentDate.getDate() + i);
    
    const dateString = currentDate.toLocaleDateString('en-US', { 
      year: 'numeric', 
      month: 'short', 
      day: 'numeric' 
    });
    
    const dayNumber = i + 1;
    
    tableHTML += `
      <tr style="border: 1px solid #ddd;">
        <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold;">Day ${dayNumber}</td>
        <td style="padding: 10px; border: 1px solid #ddd;">${dateString}</td>
        <td style="padding: 10px; border: 1px solid #ddd;">
          <input type="time" name="day-${i}-start-time" style="width: 100%; padding: 5px; border: 1px solid #ccc; border-radius: 4px;">
        </td>
        <td style="padding: 10px; border: 1px solid #ddd;">
          <input type="time" name="day-${i}-end-time" style="width: 100%; padding: 5px; border: 1px solid #ccc; border-radius: 4px;">
        </td>
        <td style="padding: 10px; border: 1px solid #ddd;">
          <input type="text" name="day-${i}-activities" placeholder="Enter activities for this day" style="width: 100%; padding: 5px; border: 1px solid #ccc; border-radius: 4px;">
        </td>
      </tr>
    `;
  }
  
  tableHTML += `
    </tbody>
  `;
  
  $('#eventScheduleTable').html(tableHTML);
}
