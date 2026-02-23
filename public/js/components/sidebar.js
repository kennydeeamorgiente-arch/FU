// Sidebar Toggle Functionality
$(document).ready(function() {
  const $sidebar = $('.sidebar');
  const $wrapper = $('.wrapper');
  const $sidebarToggle = $('#sidebarToggle');
  let $overlay = $('#sidebarOverlay, .sidebar-overlay');
  const MOBILE_BREAKPOINT = 768;

  function isMobileView() {
    return $(window).width() <= MOBILE_BREAKPOINT;
  }

  function setDesktopCollapsed(collapsed, persistState = true) {
    if (collapsed) {
      $wrapper.addClass('sidebar-collapsed');
    } else {
      $wrapper.removeClass('sidebar-collapsed');
    }

    if (persistState) {
      localStorage.setItem('sidebarCollapsed', collapsed ? '1' : '0');
    }
  }

  // Create overlay if it doesn't exist
  if ($overlay.length === 0) {
    $('body').prepend('<div class="sidebar-overlay" id="sidebarOverlay"></div>');
    $overlay = $('#sidebarOverlay');
  }

  // Restore saved desktop state on load
  if (!isMobileView()) {
    const savedState = localStorage.getItem('sidebarCollapsed');
    setDesktopCollapsed(savedState === '1', false);
  }

  // Toggle sidebar
  function toggleSidebar() {
    if (isMobileView()) {
      $sidebar.toggleClass('active show');
      $overlay.toggleClass('active show');

      // Prevent body scroll when sidebar is open
      if ($sidebar.hasClass('active')) {
        $('body').css('overflow', 'hidden');
      } else {
        $('body').css('overflow', '');
      }
    } else {
      const currentlyCollapsed = $wrapper.hasClass('sidebar-collapsed');
      setDesktopCollapsed(!currentlyCollapsed);
    }
  }

  // Toggle sidebar on button click
  $sidebarToggle.on('click', function(e) {
    e.stopPropagation();
    toggleSidebar();
  });

  // Close sidebar on close button click
  $(document).on('click', '#sidebarClose', function(e) {
    e.stopPropagation();
    if (isMobileView() && $sidebar.hasClass('active')) {
      toggleSidebar();
    }
  });

  // Close sidebar when clicking overlay
  $(document).on('click', '.sidebar-overlay', function() {
    if (isMobileView() && $sidebar.hasClass('active')) {
      toggleSidebar();
    }
  });

  // Close sidebar when clicking outside on mobile
  $(document).on('click', function(e) {
    if ($(window).width() <= 768) {
      if ($sidebar.hasClass('active')) {
        // Don't close if clicking inside sidebar or on toggle button
        if (!$(e.target).closest('.sidebar').length && 
            !$(e.target).closest('.sidebar-toggle').length &&
            !$(e.target).closest('.sidebar-close').length) {
          toggleSidebar();
        }
      }
    }
  });

  // Close sidebar when navigation link is clicked on mobile
  $(document).on('click', '.sidebar a[data-link]', function() {
    if ($(window).width() <= 768) {
      if ($sidebar.hasClass('active')) {
        // Small delay to allow navigation to process first
        setTimeout(function() {
          toggleSidebar();
        }, 100);
      }
    }
  });

  // Close sidebar on window resize if switching to desktop
  $(window).on('resize', function() {
    if (!isMobileView()) {
      $sidebar.removeClass('active');
      $overlay.removeClass('active');
      $('body').css('overflow', '');
      const savedState = localStorage.getItem('sidebarCollapsed');
      setDesktopCollapsed(savedState === '1', false);
    } else {
      $wrapper.removeClass('sidebar-collapsed');
    }
  });
});

