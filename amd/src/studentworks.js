// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Student Works JavaScript module - Corporate UI/UX 2025
 *
 * @module     local_studentworks/studentworks
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
    'jquery',
    'core/str',
    'core/log',
    'core/notification',
    'core/ajax'
], function($, Str, Log, Notification, Ajax) {
    'use strict';

    console.log('[StudentWorks Debug] AMD module loaded successfully');

    /**
     * Module configuration
     * @type {Object}
     */
    const config = {
        selectors: {
            container: '[data-region="studentworks-works"], [data-region="studentworks-dashboard"], [data-region="studentworks-detail"]',
            themeToggle: '.sw-theme-toggle',
            filterType: '[data-action="filter-type"]',
            filterStatus: '[data-action="filter-status"]',
            filterDateFrom: '[data-action="filter-date-from"]',
            filterDateTo: '[data-action="filter-date-to"]',
            searchInput: '[data-action="search"]',
            searchBtn: '[data-action="search-btn"]',
            clearFilters: '[data-action="clear-filters"]',
            card: '.sw-card',
            toastContainer: '.sw-toast-container',
            worksTableContainer: '[data-region="works-table-container"]',
            paginationContainer: '[data-region="pagination-container"]',
            loader: '[data-region="works-loader"]'
        },
        classes: {
            darkTheme: 'data-theme="dark"',
            dragOver: 'dragover',
            loading: 'sw-loading'
        },
        ajaxDelay: 500
    };

    /**
     * Initialize the module
     * @param {String} region The region identifier
     */
    function init(region) {
        console.log('[StudentWorks Debug] init() called with region:', region);
        try {
            Log.debug('StudentWorks: Initializing region: ' + region);

            const container = document.querySelector(config.selectors.container);
            console.log('[StudentWorks Debug] Container found:', !!container);
            if (!container) {
                Log.debug('StudentWorks: Container not found');
                console.error('[StudentWorks Debug] Container not found! Selector:', config.selectors.container);
                return;
            }

            // Initialize theme toggle
            initThemeToggle();

            // Initialize filters if on dashboard
            if (region === 'dashboard') {
                initFilters();
                initPaginationListeners();
            }

            // Initialize card interactions
            initCards();

            // Initialize keyboard navigation
            initKeyboardNavigation();

            Log.debug('StudentWorks: Initialization complete');
        } catch (error) {
            Log.error('StudentWorks: Initialization error - ' + error.message);
            console.error('[StudentWorks] Initialization error:', error);
        }
    }

    /**
     * Initialize theme toggle functionality
     */
    function initThemeToggle() {
        const toggle = document.querySelector(config.selectors.themeToggle);
        if (!toggle) {
            return;
        }

        // Check saved preference
        const savedTheme = localStorage.getItem('studentworks-theme');
        if (savedTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            updateThemeIcon(toggle, 'dark');
        }

        toggle.addEventListener('click', function(e) {
            e.preventDefault();

            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';

            if (isDark) {
                document.documentElement.removeAttribute('data-theme');
                localStorage.setItem('studentworks-theme', 'light');
                updateThemeIcon(toggle, 'light');
            } else {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('studentworks-theme', 'dark');
                updateThemeIcon(toggle, 'dark');
            }
        });
    }

    /**
     * Update theme toggle icon
     * @param {HTMLElement} toggle The toggle button
     * @param {String} theme Current theme
     */
    function updateThemeIcon(toggle, theme) {
        const icon = toggle.querySelector('i');
        if (icon) {
            icon.className = theme === 'dark' ? 'fa fa-sun-o' : 'fa fa-moon-o';
        }
    }

    /**
     * Initialize filter functionality
     */
    function initFilters() {
        Log.debug('StudentWorks: Initializing filters...');
        console.log('[StudentWorks Debug] initFilters() called');
        
        const filterType = document.querySelector(config.selectors.filterType);
        const filterStatus = document.querySelector(config.selectors.filterStatus);
        const filterDateFrom = document.querySelector(config.selectors.filterDateFrom);
        const filterDateTo = document.querySelector(config.selectors.filterDateTo);
        const searchInput = document.querySelector(config.selectors.searchInput);
        const searchBtn = document.querySelectorAll(config.selectors.searchBtn);
        const clearBtn = document.querySelectorAll(config.selectors.clearFilters);

        console.log('[StudentWorks Debug] Filter elements found:', {
            filterType: !!filterType,
            filterStatus: !!filterStatus,
            filterDateFrom: !!filterDateFrom,
            filterDateTo: !!filterDateTo,
            searchInput: !!searchInput,
            searchBtnCount: searchBtn.length,
            clearBtnCount: clearBtn.length
        });

        // Type filter
        if (filterType) {
            filterType.addEventListener('change', debounce(function() {
                console.log('[StudentWorks Debug] Type filter changed:', filterType.value);
                applyFilters();
            }, 300));
            console.log('[StudentWorks Debug] Type filter listener attached');
        }

        // Status filter
        if (filterStatus) {
            filterStatus.addEventListener('change', debounce(function() {
                console.log('[StudentWorks Debug] Status filter changed:', filterStatus.value);
                applyFilters();
            }, 300));
            console.log('[StudentWorks Debug] Status filter listener attached');
        }

        // Date from filter
        if (filterDateFrom) {
            filterDateFrom.addEventListener('change', debounce(function() {
                console.log('[StudentWorks Debug] Date from filter changed:', filterDateFrom.value);
                applyFilters();
            }, 300));
            console.log('[StudentWorks Debug] Date from filter listener attached');
        }

        // Date to filter
        if (filterDateTo) {
            filterDateTo.addEventListener('change', debounce(function() {
                console.log('[StudentWorks Debug] Date to filter changed:', filterDateTo.value);
                applyFilters();
            }, 300));
            console.log('[StudentWorks Debug] Date to filter listener attached');
        }

        // Search
        if (searchInput) {
            searchInput.addEventListener('input', debounce(function() {
                console.log('[StudentWorks Debug] Search input changed:', searchInput.value);
                applyFilters();
            }, 500));

            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    console.log('[StudentWorks Debug] Search Enter key pressed');
                    applyFilters();
                }
            });
            console.log('[StudentWorks Debug] Search input listeners attached');
        }

        // Search/Apply Filters buttons - attach to ALL buttons with this selector
        if (searchBtn.length > 0) {
            searchBtn.forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    console.log('[StudentWorks Debug] Search/Apply Filters button clicked');
                    e.preventDefault();
                    applyFilters();
                });
            });
            console.log('[StudentWorks Debug] Search button listeners attached to', searchBtn.length, 'buttons');
        }

        // Clear filters buttons - attach to ALL buttons with this selector
        if (clearBtn.length > 0) {
            clearBtn.forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    console.log('[StudentWorks Debug] Clear filters button clicked');
                    e.preventDefault();
                    clearAllFilters();
                });
            });
            console.log('[StudentWorks Debug] Clear button listeners attached to', clearBtn.length, 'buttons');
        }

        // Status update dropdowns
        initStatusUpdates();
        
        Log.debug('StudentWorks: Filters initialized');
        console.log('[StudentWorks Debug] Filters initialization complete');
    }

    /**
     * Initialize status update dropdowns
     */
    function initStatusUpdates() {
        console.log('[StudentWorks Debug] initStatusUpdates() called');
        const statusSelects = document.querySelectorAll('.sw-status-select');
        console.log('[StudentWorks Debug] Found status selects:', statusSelects.length);

        statusSelects.forEach(function(select) {
            // Remove existing listener if any
            const oldListener = select._statusChangeListener;
            if (oldListener) {
                select.removeEventListener('change', oldListener);
            }

            // Create new listener
            const listener = function() {
                console.log('[StudentWorks Debug] Status select changed');
                const workid = this.getAttribute('data-workid');
                const newStatus = this.value;
                const currentStatus = this.getAttribute('data-current-status');

                console.log('[StudentWorks Debug] workid:', workid, 'newStatus:', newStatus, 'currentStatus:', currentStatus);

                if (newStatus === currentStatus) {
                    console.log('[StudentWorks Debug] Status unchanged, skipping');
                    return;
                }

                console.log('[StudentWorks Debug] Calling updateWorkStatus...');
                updateWorkStatus(workid, newStatus).then(function(response) {
                    console.log('[StudentWorks Debug] updateWorkStatus response:', response);
                    if (response.success) {
                        showToast(getString('statusupdatesuccess'), 'success');
                        select.setAttribute('data-current-status', newStatus);
                        // Reload page after a short delay to reflect changes
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showToast(getString('statusupdateerror'), 'danger');
                        select.value = currentStatus;
                    }
                }).catch(function(error) {
                    console.error('[StudentWorks Debug] updateWorkStatus error:', error);
                    showToast(getString('statusupdateerror'), 'danger');
                    select.value = currentStatus;
                });
            };

            // Store listener reference and attach
            select._statusChangeListener = listener;
            select.addEventListener('change', listener);
            console.log('[StudentWorks Debug] Listener attached to select with workid:', select.getAttribute('data-workid'));
        });
    }

    /**
     * Get localized string
     * @param {String} key String key
     * @return {String} Localized string
     */
    function getString(key) {
        var strings = {
            'statusupdatesuccess': 'Статус успешно обновлен',
            'statusupdateerror': 'Ошибка обновления статуса',
            'filtererror': 'Ошибка применения фильтров'
        };
        return strings[key] || key;
    }

    /**
     * Update work status via AJAX
     * @param {Number} workid Work ID
     * @param {String} status New status
     * @return {Promise} Promise
     */
    function updateWorkStatus(workid, status) {
        console.log('[StudentWorks Debug] updateWorkStatus called with workid:', workid, 'status:', status);
        var url = M.cfg.wwwroot + '/local/studentworks/teacher.php';
        var params = new URLSearchParams();
        params.append('action', 'update_status');
        params.append('workid', workid);
        params.append('status', status);
        params.append('sesskey', M.cfg.sesskey);

        console.log('[StudentWorks Debug] Sending POST to:', url);
        console.log('[StudentWorks Debug] POST params:', params.toString());

        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: params.toString()
        }).then(function(response) {
            console.log('[StudentWorks Debug] Response status:', response.status);
            return response.json();
        });
    }

    /**
     * Apply filters and update URL
     */
    function applyFilters() {
        console.log('[StudentWorks Debug] applyFilters() called');
        
        const filterType = document.querySelector(config.selectors.filterType);
        const filterStatus = document.querySelector(config.selectors.filterStatus);
        const filterDateFrom = document.querySelector(config.selectors.filterDateFrom);
        const filterDateTo = document.querySelector(config.selectors.filterDateTo);
        const searchInput = document.querySelector(config.selectors.searchInput);

        console.log('[StudentWorks Debug] Filter elements found:', {
            filterType: !!filterType,
            filterStatus: !!filterStatus,
            filterDateFrom: !!filterDateFrom,
            filterDateTo: !!filterDateTo,
            searchInput: !!searchInput
        });

        const filters = {
            type: filterType ? filterType.value : '',
            status: filterStatus ? filterStatus.value : '',
            date_from: filterDateFrom ? filterDateFrom.value : '',
            date_to: filterDateTo ? filterDateTo.value : '',
            search: searchInput ? searchInput.value : '',
            page: 0
        };

        console.log('[StudentWorks Debug] Filter values:', filters);

        // Update URL without reload
        updateUrl(filters);

        // AJAX request to update table
        loadWorksWithAjax(filters);
    }

    /**
     * Update URL parameters without page reload
     * @param {Object} filters Filter values
     */
    function updateUrl(filters) {
        const params = new URLSearchParams();

        if (filters.type) {
            params.set('type', filters.type);
        }
        if (filters.status) {
            params.set('status', filters.status);
        }
        if (filters.date_from) {
            params.set('date_from', filters.date_from);
        }
        if (filters.date_to) {
            params.set('date_to', filters.date_to);
        }
        if (filters.search) {
            params.set('search', filters.search);
        }
        if (filters.page > 0) {
            params.set('page', filters.page);
        }

        const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        window.history.replaceState({}, '', newUrl);
    }

    /**
     * Load works via AJAX
     * @param {Object} filters Filter values
     * @param {Number} page Page number
     */
    function loadWorksWithAjax(filters, page) {
        console.log('[StudentWorks Debug] loadWorksWithAjax() called with filters:', filters);
        
        if (typeof page !== 'undefined') {
            filters.page = page;
        }

        const tableContainer = document.querySelector(config.selectors.worksTableContainer);
        const paginationContainer = document.querySelector(config.selectors.paginationContainer);
        const loader = document.querySelector(config.selectors.loader);

        console.log('[StudentWorks Debug] Container elements found:', {
            tableContainer: !!tableContainer,
            paginationContainer: !!paginationContainer,
            loader: !!loader
        });

        // Show loader
        showLoader(loader, tableContainer);

        // Build request URL
        const url = M.cfg.wwwroot + '/local/studentworks/teacher.php';
        const params = new URLSearchParams();
        params.append('action', 'filter_works');
        params.append('sesskey', M.cfg.sesskey);
        params.append('type', filters.type || '');
        params.append('status', filters.status || '');
        params.append('date_from', filters.date_from || '');
        params.append('date_to', filters.date_to || '');
        params.append('search', filters.search || '');
        params.append('page', filters.page || 0);

        const fullUrl = url + '?' + params.toString();
        console.log('[StudentWorks Debug] AJAX URL:', fullUrl);
        
        fetch(fullUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(response) {
            console.log('[StudentWorks Debug] AJAX response status:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.text().then(function(text) {
                console.log('[StudentWorks Debug] Raw response (first 500 chars):', text.substring(0, 500));
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('[StudentWorks Debug] JSON parse error. Full response:', text);
                    throw e;
                }
            });
        })
        .then(function(data) {
            console.log('[StudentWorks Debug] AJAX response data:', data);
            hideLoader(loader, tableContainer);

            if (data.success) {
                console.log('[StudentWorks Debug] Response success, updating table...');
                updateWorksTable(data.html, data.pagination);
                updateUrl(filters);

                // Update stats if provided
                if (data.stats) {
                    updateStats(data.stats);
                }
            } else {
                console.error('[StudentWorks Debug] Response error:', data);
                showToast(getString('filtererror'), 'danger');
            }
        })
        .catch(function(error) {
            hideLoader(loader, tableContainer);
            console.error('[StudentWorks Debug] AJAX error:', error);
            Log.debug('StudentWorks: AJAX error - ' + error);
            showToast(getString('filtererror'), 'danger');
        });
    }

    /**
     * Show loader overlay
     * @param {HTMLElement} loader Loader element
     * @param {HTMLElement} container Table container
     */
    function showLoader(loader, container) {
        if (loader) {
            loader.classList.remove('sw-hidden');
        }
        if (container) {
            container.classList.add('sw-loading');
        }
    }

    /**
     * Hide loader overlay
     * @param {HTMLElement} loader Loader element
     * @param {HTMLElement} container Table container
     */
    function hideLoader(loader, container) {
        if (loader) {
            loader.classList.add('sw-hidden');
        }
        if (container) {
            container.classList.remove('sw-loading');
        }
    }

    /**
     * Update works table with new HTML
     * @param {String} html Table HTML
     * @param {String} pagination Pagination HTML
     */
    function updateWorksTable(html, pagination) {
        console.log('[StudentWorks Debug] updateWorksTable() called');
        console.log('[StudentWorks Debug] HTML length:', html ? html.length : 0);
        console.log('[StudentWorks Debug] Pagination length:', pagination ? pagination.length : 0);
        
        const tableContainer = document.querySelector(config.selectors.worksTableContainer);
        const paginationContainer = document.querySelector(config.selectors.paginationContainer);

        console.log('[StudentWorks Debug] Containers found:', {
            tableContainer: !!tableContainer,
            paginationContainer: !!paginationContainer
        });

        if (tableContainer) {
            // Find existing table section or empty state
            const existingSection = tableContainer.querySelector('.sw-table-container, .sw-empty');
            console.log('[StudentWorks Debug] Existing section found:', !!existingSection);
            
            if (existingSection) {
                // Create temporary container to parse HTML
                const temp = document.createElement('div');
                temp.innerHTML = html;
                const newSection = temp.firstElementChild;
                
                if (newSection) {
                    // Replace only the table/empty section, preserving loader
                    existingSection.replaceWith(newSection);
                    console.log('[StudentWorks Debug] Section replaced with new content');
                } else {
                    console.error('[StudentWorks Debug] No new section found in HTML');
                }
            } else {
                // If no existing section, insert before loader
                const loader = tableContainer.querySelector(config.selectors.loader);
                const temp = document.createElement('div');
                temp.innerHTML = html;
                
                if (loader) {
                    loader.before(...temp.children);
                    console.log('[StudentWorks Debug] Content inserted before loader');
                } else {
                    tableContainer.appendChild(temp.firstElementChild);
                    console.log('[StudentWorks Debug] Content appended to container');
                }
            }

            // Re-initialize status updates for new elements
            initStatusUpdates();

            // Re-initialize card interactions
            initCards();

            // Log success for debugging
            console.log('[StudentWorks Debug] Table updated successfully');
        } else {
            console.error('[StudentWorks Debug] Table container not found!');
        }

        if (paginationContainer) {
            if (pagination) {
                const temp = document.createElement('div');
                temp.innerHTML = pagination;
                paginationContainer.innerHTML = temp.innerHTML;
                console.log('[StudentWorks Debug] Pagination updated');

                // Re-attach pagination event listeners
                initPaginationListeners();
            } else {
                // Clear pagination if no data
                paginationContainer.innerHTML = '';
                console.log('[StudentWorks Debug] Pagination cleared');
            }
        } else {
            console.error('[StudentWorks Debug] Pagination container not found!');
        }
    }

    /**
     * Update statistics
     * @param {Object} stats Statistics data
     */
    function updateStats(stats) {
        if (stats.total !== undefined) {
            const totalEl = document.querySelector('.sw-stat-value[data-stat="total"]');
            if (totalEl) totalEl.textContent = stats.total;
        }
        if (stats.reviewed !== undefined) {
            const reviewedEl = document.querySelector('.sw-stat-value[data-stat="reviewed"]');
            if (reviewedEl) reviewedEl.textContent = stats.reviewed;
        }
        if (stats.pending !== undefined) {
            const pendingEl = document.querySelector('.sw-stat-value[data-stat="pending"]');
            if (pendingEl) pendingEl.textContent = stats.pending;
        }
        if (stats.rejected !== undefined) {
            const rejectedEl = document.querySelector('.sw-stat-value[data-stat="rejected"]');
            if (rejectedEl) rejectedEl.textContent = stats.rejected;
        }
        if (stats.students !== undefined) {
            const studentsEl = document.querySelector('.sw-stat-value[data-stat="students"]');
            if (studentsEl) studentsEl.textContent = stats.students;
        }
    }

    /**
     * Initialize pagination click handlers
     */
    function initPaginationListeners() {
        const paginationContainer = document.querySelector(config.selectors.paginationContainer);
        if (!paginationContainer) {
            return;
        }

        const pageLinks = paginationContainer.querySelectorAll('.sw-page-btn:not([disabled])');
        pageLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = this.getAttribute('data-page');
                if (page !== null) {
                    const filterType = document.querySelector(config.selectors.filterType);
                    const filterStatus = document.querySelector(config.selectors.filterStatus);
                    const filterDateFrom = document.querySelector(config.selectors.filterDateFrom);
                    const filterDateTo = document.querySelector(config.selectors.filterDateTo);
                    const searchInput = document.querySelector(config.selectors.searchInput);

                    const filters = {
                        type: filterType ? filterType.value : '',
                        status: filterStatus ? filterStatus.value : '',
                        date_from: filterDateFrom ? filterDateFrom.value : '',
                        date_to: filterDateTo ? filterDateTo.value : '',
                        search: searchInput ? searchInput.value : ''
                    };

                    loadWorksWithAjax(filters, parseInt(page, 10));
                }
            });
        });
    }

    /**
     * Clear all filters
     */
    function clearAllFilters() {
        const filterType = document.querySelector(config.selectors.filterType);
        const filterStatus = document.querySelector(config.selectors.filterStatus);
        const filterDateFrom = document.querySelector(config.selectors.filterDateFrom);
        const filterDateTo = document.querySelector(config.selectors.filterDateTo);
        const searchInput = document.querySelector(config.selectors.searchInput);

        if (filterType) filterType.value = '';
        if (filterStatus) filterStatus.value = '';
        if (filterDateFrom) filterDateFrom.value = '';
        if (filterDateTo) filterDateTo.value = '';
        if (searchInput) searchInput.value = '';

        applyFilters();
    }

    /**
     * Initialize card interactions
     */
    function initCards() {
        const cards = document.querySelectorAll(config.selectors.card);

        cards.forEach(function(card) {
            // Add click handler for keyboard accessibility
            card.addEventListener('click', function(e) {
                // Don't navigate if clicking on a button or link inside the card
                if (e.target.closest('a, button')) {
                    return;
                }

                // Find the view link and navigate
                const viewLink = card.querySelector('a[href*="view.php"]');
                if (viewLink) {
                    window.location.href = viewLink.href;
                }
            });

            // Add keyboard support
            card.setAttribute('tabindex', '0');
            card.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    card.click();
                }
            });
        });
    }

    /**
     * Initialize keyboard navigation
     */
    function initKeyboardNavigation() {
        // Escape key to close modals or go back
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const activeElement = document.activeElement;
                if (activeElement && activeElement.classList.contains('sw-card')) {
                    activeElement.blur();
                }
            }
        });
    }

    /**
     * Show toast notification
     * @param {String} message The message to display
     * @param {String} type The type of notification
     * @param {String} title Optional title
     */
    function showToast(message, type = 'info', title = '') {
        let container = document.querySelector(config.selectors.toastContainer);

        if (!container) {
            container = document.createElement('div');
            container.className = 'sw-toast-container';
            container.setAttribute('role', 'region');
            container.setAttribute('aria-live', 'polite');
            container.setAttribute('aria-label', 'Notifications');
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = 'sw-toast sw-toast-' + type;
        toast.setAttribute('role', 'alert');

        const iconClass = {
            'success': 'fa-check-circle',
            'warning': 'fa-exclamation-triangle',
            'danger': 'fa-times-circle',
            'info': 'fa-info-circle'
        }[type] || 'fa-info-circle';

        let html = '<i class="fa ' + iconClass + ' sw-toast-icon" aria-hidden="true"></i>';
        html += '<div class="sw-toast-content">';
        if (title) {
            html += '<div class="sw-toast-title">' + escapeHtml(title) + '</div>';
        }
        html += '<div class="sw-toast-message">' + escapeHtml(message) + '</div>';
        html += '</div>';
        html += '<button class="sw-toast-close" aria-label="Close notification">&times;</button>';

        toast.innerHTML = html;
        container.appendChild(toast);

        // Close button handler
        toast.querySelector('.sw-toast-close').addEventListener('click', function() {
            removeToast(toast);
        });

        // Auto-remove after 5 seconds
        setTimeout(function() {
            removeToast(toast);
        }, 5000);
    }

    /**
     * Remove toast notification
     * @param {HTMLElement} toast The toast element
     */
    function removeToast(toast) {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(function() {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }

    /**
     * Debounce function
     * @param {Function} func The function to debounce
     * @param {Number} wait Wait time in milliseconds
     * @return {Function} Debounced function
     */
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this;
            const args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    }

    /**
     * Escape HTML entities
     * @param {String} text The text to escape
     * @return {String} Escaped text
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Confirm action with custom dialog
     * @param {String} message The confirmation message
     * @param {Function} onConfirm Callback when confirmed
     * @param {Function} onCancel Callback when cancelled
     */
    function confirmAction(message, onConfirm, onCancel) {
        if (confirm(message)) {
            if (typeof onConfirm === 'function') {
                onConfirm();
            }
        } else {
            if (typeof onCancel === 'function') {
                onCancel();
            }
        }
    }

    // Public API
    return {
        init: init,
        showToast: showToast,
        confirmAction: confirmAction
    };
});
