/**
 * Global Search Modal Functionality
 * Handles the search modal with AJAX calls (reui.io style)
 */

// Wait for jQuery to be available
function initializeGlobalSearch() {
    if (typeof $ === 'undefined') {
        setTimeout(initializeGlobalSearch, 100);
        return;
    }

    $(document).ready(function() {
    const globalSearchInput = $('.globalSearch');
    const searchModal = $('#searchModal');
    const modalSearchInput = $('#modalSearchInput');
    const modalSearchLoader = $('#modalSearchLoader');
    const quickActions = $('#quickActions');
    const searchResultsSection = $('#searchResultsSection');
    const modalSearchResultsContent = $('#modalSearchResultsContent');
    const modalSearchResultsCount = $('#modalSearchResultsCount');
    const emptySearchState = $('#emptySearchState');
    let searchTimeout;
    let currentRequest;

    // Initialize modal search functionality
    initializeModalSearch();

    function initializeModalSearch() {
        // Open modal on search input click
        globalSearchInput.on('click focus', function(e) {
            e.preventDefault();
            openSearchModal();
        });

        // Keyboard shortcut (Cmd/Ctrl + K)
        $(document).on('keydown', function(e) {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                openSearchModal();
            }
        });

        function openSearchModal() {
            // Ensure modal is properly positioned
            searchModal.modal('show');
        }

        // Focus search input when modal opens
        searchModal.on('shown.bs.modal', function() {
            setTimeout(function() {
                modalSearchInput[0].focus();
            }, 100);
            resetModalState();
        });

        // Clear search when modal closes
        searchModal.on('hidden.bs.modal', function() {
            modalSearchInput.val('');
            resetModalState();
            if (currentRequest) {
                currentRequest.abort();
            }
        });

        // Debounced search function for modal
        modalSearchInput.on('input', function() {
            const query = $(this).val().trim();

            // Clear previous timeout
            clearTimeout(searchTimeout);

            // Cancel previous request
            if (currentRequest) {
                currentRequest.abort();
            }

            if (query.length === 0) {
                resetModalState();
                return;
            }

            if (query.length < 2) {
                showEmptyState();
                return;
            }

            // Show loader
            modalSearchLoader.removeClass('d-none');

            // Debounce search
            searchTimeout = setTimeout(() => {
                performModalSearch(query);
            }, 300);
        });

        // Handle keyboard navigation in modal
        modalSearchInput.on('keydown', function(e) {
            handleModalKeyboardNavigation(e);
        });

        // Handle result clicks
        $(document).on('click', '.modal-search-result-item', function() {
            // Let the default link behavior handle navigation
            searchModal.modal('hide');
        });
    }

    function performModalSearch(query) {
        showModalLoader();

        currentRequest = $.ajax({
            url: '/admin/search',
            method: 'GET',
            data: { q: query, limit: 15 },
            success: function(response) {
                hideModalLoader();
                if (response.success) {
                    displayModalSearchResults(response.results, query);
                } else {
                    showModalErrorMessage('Search failed. Please try again.');
                }
            },
            error: function(xhr) {
                hideModalLoader();
                if (xhr.statusText !== 'abort') {
                    showModalErrorMessage('Search failed. Please check your connection.');
                }
            }
        });
    }

    function resetModalState() {
        quickActions.removeClass('d-none');
        searchResultsSection.addClass('d-none');
        emptySearchState.addClass('d-none');
        modalSearchLoader.addClass('d-none');
    }

    function showEmptyState() {
        quickActions.addClass('d-none');
        searchResultsSection.addClass('d-none');
        emptySearchState.removeClass('d-none');
        modalSearchLoader.addClass('d-none');
    }

    function showModalLoader() {
        modalSearchLoader.removeClass('d-none');
    }

    function hideModalLoader() {
        modalSearchLoader.addClass('d-none');
    }

    function displayModalSearchResults(results, query) {
        // Update results count
        modalSearchResultsCount.text(`${results.length} result${results.length !== 1 ? 's' : ''}`);

        if (results.length === 0) {
            showEmptyState();
            return;
        }

        // Show results section
        quickActions.addClass('d-none');
        emptySearchState.addClass('d-none');
        searchResultsSection.removeClass('d-none');

        let html = '';

        if (results.length > 0) {
            // Group results by category
            const groupedResults = groupResultsByCategory(results);

            Object.keys(groupedResults).forEach((category, index) => {
                if (index > 0) {
                    html += `<hr class="my-3">`;
                }

                html += `
                    <div class="mb-4">
                        <h6 class="mb-3 text-uppercase text-muted fw-semibold small">
                            ${category}
                        </h6>
                        <div class="d-flex flex-column gap-1">
                `;

                groupedResults[category].forEach(result => {
                    html += createModalResultItem(result, query);
                });

                html += `
                        </div>
                    </div>
                `;
            });

            // Add "View All Results" link if there are many results
            if (results.length >= 15) {
                html += `
                    <hr class="my-3">
                    <div class="text-center">
                        <small class="text-muted">Showing first 15 results</small>
                    </div>
                `;
            }

            modalSearchResultsContent.html(html);
        }

        // Reinitialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Add hover effects
        addModalHoverEffects();
    }

    function groupResultsByCategory(results) {
        const grouped = {};
        results.forEach(result => {
            const category = result.category || 'Other';
            if (!grouped[category]) {
                grouped[category] = [];
            }
            grouped[category].push(result);
        });
        return grouped;
    }

    function createModalResultItem(result, query) {
        const avatar = result.avatar ?
            `<img src="${result.avatar}" alt="${result.title}" class="rounded-circle avatar-sm">` :
            `<div class="d-flex align-items-center justify-content-center rounded-circle bg-light avatar-sm">
                <i data-lucide="${result.icon || 'file'}" class="text-muted"></i>
            </div>`;

        const badge = result.badge ?
            `<span class="badge bg-light text-dark small">${result.badge}</span>` : '';

        const status = result.status ?
            `<span class="badge ${result.status.toLowerCase() === 'active' ? 'bg-success' : 'bg-danger'} small">${result.status}</span>` : '';

        return `
            <a href="${result.url}" class="modal-search-result-item d-flex align-items-center py-2 px-3 text-decoration-none rounded border-0" data-url="${result.url}">
                ${avatar}
                <div class="flex-grow-1 ms-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-medium small">
                            ${highlightQuery(result.title, query)}
                        </span>
                        ${badge}
                        ${status}
                    </div>
                  
                    ${result.description ? `
                        <div class="text-muted small opacity-75">
                            ${result.description}
                        </div>
                    ` : ''}
                </div>
                <i data-lucide="external-link" class="icon-sm text-muted ms-2"></i>
            </a>
        `;
    }

    function addModalHoverEffects() {
        // Hover effects are now handled by Bootstrap classes (hover-bg-light)
        // No additional JavaScript needed
    }

    function showModalErrorMessage(message) {
        modalSearchResultsCount.text('Error');

        quickActions.addClass('d-none');
        emptySearchState.addClass('d-none');
        searchResultsSection.removeClass('d-none');

        modalSearchResultsContent.html(`
            <div class="text-center py-4">
                <div class="d-flex justify-content-center mb-3">
                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10 avatar-lg">
                        <i data-lucide="alert-circle" class="icon-lg text-danger"></i>
                    </div>
                </div>
                <h6 class="mb-2 text-danger fw-semibold">Something went wrong</h6>
                <p class="mb-0 text-muted small">${message}</p>
            </div>
        `);

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    function handleModalKeyboardNavigation(e) {
        const items = searchResultsSection.find('.modal-search-result-item');
        const activeItem = items.filter('.active');

        switch(e.keyCode) {
            case 40: // Arrow Down
                e.preventDefault();
                if (activeItem.length === 0) {
                    items.first().addClass('active bg-light');
                } else {
                    const next = activeItem.removeClass('active bg-light').next('.modal-search-result-item');
                    if (next.length > 0) {
                        next.addClass('active bg-light');
                    } else {
                        items.first().addClass('active bg-light');
                    }
                }
                break;

            case 38: // Arrow Up
                e.preventDefault();
                if (activeItem.length === 0) {
                    items.last().addClass('active bg-light');
                } else {
                    const prev = activeItem.removeClass('active bg-light').prev('.modal-search-result-item');
                    if (prev.length > 0) {
                        prev.addClass('active bg-light');
                    } else {
                        items.last().addClass('active bg-light');
                    }
                }
                break;

            case 13: // Enter
                e.preventDefault();
                if (activeItem.length > 0) {
                    const url = activeItem.attr('href');
                    if (url) {
                        window.location.href = url;
                    }
                }
                break;

            case 27: // Escape
                e.preventDefault();
                searchModal.modal('hide');
                break;
        }
    }

    function highlightQuery(text, query) {
        if (!query || !text) return text;
        const regex = new RegExp(`(${escapeRegex(query)})`, 'gi');
        return text.replace(regex, '<mark class="bg-warning bg-opacity-25 text-dark fw-semibold rounded-1">$1</mark>');
    }

    function escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
    });
}

// Initialize the search functionality
initializeGlobalSearch();
