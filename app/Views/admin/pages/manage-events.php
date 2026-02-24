<div class="container">
    <section class="admin-page-title-card">
        <div class="admin-page-title-content">
            <h1>Manage Events</h1>
            <p>Review event submissions and process approvals by stage.</p>
        </div>
    </section>

    <section class="admin-page-toolbar-card">
        <div class="admin-toolbar-grid">
            <div class="admin-toolbar-input">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="manage-events-search-input" placeholder="Search event, organization, or stage">
            </div>
            <select id="manage-events-status-select" class="admin-toolbar-select">
                <option value="">All Status</option>
                <option value="Pending">Pending</option>
                <option value="In-Progress">In-Progress</option>
                <option value="Awaiting Documentation">Awaiting Documentation</option>
                <option value="For Verification">For Verification</option>
                <option value="Completed">Completed</option>
                <option value="Rejected">Rejected</option>
                <option value="Returned for Revision">Returned for Revision</option>
            </select>
            <button type="button" id="manage-events-clear-btn" class="admin-toolbar-btn subtle">Clear</button>
        </div>
        <div class="table-filter">
            <button id="filter-annual" class="active">Annually</button>
            <button id="filter-first">1st Semester</button>
            <button id="filter-second">2nd Semester</button>
            <button id="filter-summer">Summer</button>
        </div>
    </section>

    <section class="admin-page-table-card">
        <div class="table-header">
            <h3>Event Approvals</h3>
            <div class="table-actions">
                <span class="table-hint">Click row to open details</span>
            </div>
        </div>

        <table class="org-table" id="manage-events-table">

            <thead>

                <tr>
                    <th>
                        Event Date
                    </th>
                    <th>Event Status <span class="filter-dropdown-container" data-column="1">
                            <button class="filter-button" aria-expanded="false">&#x22EE;</button>
                            <div class="filter-menu">
                                <div class="search-input-wrapper">
                                    <input type="text" placeholder="Search Status..." class="column-search-input">
                                </div>

                                <div class="selection-buttons">
                                    <button class="select-all-btn">Select All</button>
                                    <button class="uncheck-all-btn">Uncheck All</button>
                                </div>
                                <hr>

                                <div class="sort-buttons">
                                    <button class="sort-asc-btn">Sort A-Z</button>
                                    <button class="sort-desc-btn">Sort Z-A</button>
                                    <button class="apply-filter-btn">Apply</button>
                                    <button class="clear-filter-btn">Clear</button>
                                </div>
                                <hr>
                                <div class="filter-checkbox-list">
                                </div>

                            </div>
                        </span></th>
                    <th>Event Stage <span class="filter-dropdown-container" data-column="2">
                            <button class="filter-button" aria-expanded="false">&#x22EE;</button>
                            <div class="filter-menu">
                                <div class="search-input-wrapper">
                                    <input type="text" placeholder="Search Stage..." class="column-search-input">
                                </div>

                                <div class="selection-buttons">
                                    <button class="select-all-btn">Select All</button>
                                    <button class="uncheck-all-btn">Uncheck All</button>
                                </div>
                                <hr>

                                <div class="sort-buttons">
                                    <button class="sort-asc-btn">Sort A-Z</button>
                                    <button class="sort-desc-btn">Sort Z-A</button>
                                    <button class="apply-filter-btn">Apply</button>
                                    <button class="clear-filter-btn">Clear</button>
                                </div>
                                <hr>
                                <div class="filter-checkbox-list">
                                </div>

                            </div>
                        </span></th>
                    <th>Event Name</th>
                    <th>
                        Organization <span class="filter-dropdown-container" data-column="4">
                            <button class="filter-button" aria-expanded="false">&#x22EE;</button>
                            <div class="filter-menu">
                                <div class="search-input-wrapper">
                                    <input type="text" placeholder="Search Organization..." class="column-search-input">
                                </div>

                                <div class="selection-buttons">
                                    <button class="select-all-btn">Select All</button>
                                    <button class="uncheck-all-btn">Uncheck All</button>
                                </div>
                                <hr>

                                <div class="sort-buttons">
                                    <button class="sort-asc-btn">Sort A-Z</button>
                                    <button class="sort-desc-btn">Sort Z-A</button>
                                    <button class="apply-filter-btn">Apply</button>
                                    <button class="clear-filter-btn">Clear</button>
                                </div>
                                <hr>
                                <div class="filter-checkbox-list">
                                </div>

                            </div>
                        </span>
                    </th>
                    <th>Approval Level</th>
                </tr>
            </thead>
            <tbody>

            </tbody>

        </table>
    </section>
</div>
