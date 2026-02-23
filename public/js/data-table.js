/**
 * Custom Column Header Filtering for DataTables
 * Provides dropdown filtering with checkboxes, search, and sorting for table columns
 * Works with any DataTable that has filter-dropdown-container elements
 */
$(document).ready(function() {
  
  /**
   * Initialize filter dropdown for a specific column
   * @param {string} tableSelector - jQuery selector for the DataTable
   * @param {number} columnIndex - Zero-based index of the column to filter
   */
  function initializeColumnFilter(tableSelector, columnIndex) {
    // Wait for DataTable to be initialized
    setTimeout(function() {
      const table = $(tableSelector).DataTable();
      if (!table) {
        console.log('DataTable not found for ' + tableSelector);
        return;
      }

      const filterContainer = $('.filter-dropdown-container[data-column="' + columnIndex + '"]');
      if (filterContainer.length === 0) {
        return; // No filter container for this column
      }

      const filterButton = filterContainer.find('.filter-button');
      const filterMenu = filterContainer.find('.filter-menu');
      const checkboxList = filterContainer.find('.filter-checkbox-list');
      const applyBtn = filterContainer.find('.apply-filter-btn');
      const clearBtn = filterContainer.find('.clear-filter-btn');
      const columnSearchInput = filterContainer.find('.column-search-input');
      const sortAscBtn = filterContainer.find('.sort-asc-btn');
      const sortDescBtn = filterContainer.find('.sort-desc-btn');
      const selectAllBtn = filterContainer.find('.select-all-btn');
      const uncheckAllBtn = filterContainer.find('.uncheck-all-btn');

      /**
       * Populate checkbox list with unique values from the column
       */
      function populateCheckboxes() {
        checkboxList.empty();
        const columnData = table.column(columnIndex).data().unique().sort();

        columnData.each(function(d) {
          const escapedValue = $('<div>').text(d).html();
          const checkboxHtml = `
              <label>
                  <input type="checkbox" value="${escapedValue}" checked>
                  <span>${escapedValue}</span>
              </label>
          `;
          const $label = $(checkboxHtml);
          checkboxList.append($label);
          
          // Add checked class for styling
          $label.find('input').on('change', function() {
            if ($(this).is(':checked')) {
              $label.addClass('checked');
            } else {
              $label.removeClass('checked');
            }
          });
          
          // Initialize checked state
          $label.addClass('checked');
        });
      }

      /**
       * Custom filter function for DataTables
       * Filters rows based on selected checkbox values
       */
      $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
          // Check if this is the correct table instance
          const currentTable = $(tableSelector).DataTable();
          if (!currentTable || settings.nTable !== currentTable.table().node) {
            return true;
          }

          const selectedValues = filterContainer.data('selected-values');
          const columnValue = data[columnIndex];

          if (!selectedValues || selectedValues.length === 0) {
            return true;
          }

          if (selectedValues.includes(columnValue)) {
            return true;
          }

          return false;
        }
      );

      /**
       * Position dropdown menu relative to button, ensuring it stays within viewport
       * @param {jQuery} button - The filter button element
       * @param {jQuery} menu - The dropdown menu element
       */
      function positionDropdown(button, menu) {
        const buttonOffset = $(button).offset();
        const buttonHeight = $(button).outerHeight();
        const menuWidth = menu.outerWidth();
        const windowWidth = $(window).width();
        
        // Calculate position
        let left = buttonOffset.left + $(button).outerWidth() - menuWidth;
        let top = buttonOffset.top + buttonHeight + 8;
        
        // Adjust if dropdown goes off screen to the right
        if (left < 10) {
          left = buttonOffset.left;
        }
        
        // Adjust if dropdown goes off screen to the left
        if (left + menuWidth > windowWidth - 10) {
          left = windowWidth - menuWidth - 10;
        }
        
        // Adjust if dropdown goes off screen at bottom
        const windowHeight = $(window).height();
        const menuHeight = menu.outerHeight();
        if (top + menuHeight > windowHeight - 10) {
          top = buttonOffset.top - menuHeight - 8;
        }
        
        menu.css({
          'left': left + 'px',
          'top': top + 'px'
        });
      }

      // Toggle dropdown menu on button click
      filterButton.off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const isExpanded = $(this).attr('aria-expanded') === 'true';
        
        // Close all other dropdowns
        $('.filter-button').not(this).attr('aria-expanded', 'false');
        $('.filter-menu').not(filterMenu).css({ 'left': '', 'top': '' });
        
        if (!isExpanded) {
          $(this).attr('aria-expanded', 'true');
          setTimeout(function() {
            positionDropdown(filterButton, filterMenu);
          }, 10);
        } else {
          filterMenu.css({ 'left': '', 'top': '' });
        }
      });
      
      // Reposition dropdown on window resize
      $(window).on('resize', function() {
        if (filterButton.attr('aria-expanded') === 'true') {
          positionDropdown(filterButton, filterMenu);
        }
      });

      // Select All
      selectAllBtn.on('click', function(e) {
        e.stopPropagation();
        checkboxList.find('label:visible input[type="checkbox"]').prop('checked', true);
        checkboxList.find('label:visible').addClass('checked');
      });

      // Uncheck All
      uncheckAllBtn.on('click', function(e) {
        e.stopPropagation();
        checkboxList.find('label:visible input[type="checkbox"]').prop('checked', false);
        checkboxList.find('label:visible').removeClass('checked');
      });

      // Apply Filter
      applyBtn.on('click', function(e) {
        e.stopPropagation();
        const selected = [];
        checkboxList.find('input:checked').each(function() {
          selected.push($(this).val());
        });

        filterContainer.data('selected-values', selected);
        table.draw();
        filterButton.attr('aria-expanded', 'false');
        filterMenu.css({ 'left': '', 'top': '' });
      });

      // Clear Filter
      clearBtn.on('click', function(e) {
        e.stopPropagation();
        checkboxList.find('input').prop('checked', true);
        checkboxList.find('label').addClass('checked');
        filterContainer.data('selected-values', null);
        table.draw();
        filterButton.attr('aria-expanded', 'false');
        filterMenu.css({ 'left': '', 'top': '' });
      });

      // Search within dropdown
      columnSearchInput.on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        checkboxList.find('label').each(function() {
          const text = $(this).text().toLowerCase();
          if (text.includes(searchTerm)) {
            $(this).show();
          } else {
            $(this).hide();
          }
        });
      });

      // Sorting
      sortAscBtn.on('click', function(e) {
        e.stopPropagation();
        table.order([columnIndex, 'asc']).draw();
        filterButton.attr('aria-expanded', 'false');
        filterMenu.css({ 'left': '', 'top': '' });
      });

      sortDescBtn.on('click', function(e) {
        e.stopPropagation();
        table.order([columnIndex, 'desc']).draw();
        filterButton.attr('aria-expanded', 'false');
        filterMenu.css({ 'left': '', 'top': '' });
      });

      // Prevent menu closure when clicking inside
      filterMenu.on('click', function(e) {
        e.stopPropagation();
      });

      // Initialize checkboxes (even if empty, show the dropdown)
      try {
        populateCheckboxes();
        
        // If no data, show a message
        if (checkboxList.find('label').length === 0) {
          checkboxList.html('<div style="padding: 20px; color: #999; text-align: center; font-size: 13px; font-style: italic;">No data available to filter</div>');
        }
      } catch (error) {
        console.log('Error populating checkboxes:', error);
        checkboxList.html('<div style="padding: 20px; color: #999; text-align: center; font-size: 13px; font-style: italic;">No data available to filter</div>');
      }
      
      console.log('Filter initialized for column', columnIndex);
    }, 500); // Wait a bit for DataTable to initialize
  }
  
  /**
   * Event delegation for filter buttons (handles dynamically added elements)
   * Uses the same positioning logic as the direct handlers
   */
  $(document).off('click', '.filter-button').on('click', '.filter-button', function(e) {
    e.preventDefault();
    e.stopPropagation();
    const $button = $(this);
    const isExpanded = $button.attr('aria-expanded') === 'true';
    const $menu = $button.siblings('.filter-menu');
    
    // Close all other dropdowns
    $('.filter-button').not($button).attr('aria-expanded', 'false');
    $('.filter-menu').not($menu).css({ 'left': '', 'top': '' });
    
    if (!isExpanded) {
      $button.attr('aria-expanded', 'true');
      // Position dropdown using same logic
      setTimeout(function() {
        const buttonOffset = $button.offset();
        const buttonHeight = $button.outerHeight();
        const menuWidth = $menu.outerWidth();
        const windowWidth = $(window).width();
        
        let left = buttonOffset.left + $button.outerWidth() - menuWidth;
        let top = buttonOffset.top + buttonHeight + 8;
        
        // Adjust if off screen
        if (left < 10) left = buttonOffset.left;
        if (left + menuWidth > windowWidth - 10) left = windowWidth - menuWidth - 10;
        
        const windowHeight = $(window).height();
        const menuHeight = $menu.outerHeight();
        if (top + menuHeight > windowHeight - 10) {
          top = buttonOffset.top - menuHeight - 8;
        }
        
        $menu.css({ 'left': left + 'px', 'top': top + 'px' });
      }, 10);
    } else {
      $menu.css({ 'left': '', 'top': '' });
    }
  });

  // Close menu when clicking outside (but don't interfere with table row clicks)
  $(document).on('click', function(event) {
    const $target = $(event.target);
    // Only close if clicking outside filter elements AND not on table rows
    if (!$target.closest('.filter-dropdown-container').length && 
        !$target.closest('.filter-menu').length &&
        !$target.closest('.event-row').length &&
        !$target.closest('tr').hasClass('event-row')) {
      $('.filter-button').attr('aria-expanded', 'false');
      $('.filter-menu').css({ 'left': '', 'top': '' });
    }
  });

  /**
   * Public API: Initialize filters for multiple columns
   * @param {string} tableSelector - jQuery selector for the DataTable
   * @param {number[]} columns - Array of column indices to add filters to
   */
  window.initDataTableFilters = function(tableSelector, columns) {
    if (columns && columns.length > 0) {
      columns.forEach(function(columnIndex) {
        initializeColumnFilter(tableSelector, columnIndex);
      });
    }
  };
});

