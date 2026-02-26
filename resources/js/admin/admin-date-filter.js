/**
 * Admin Date Filter Module
 * Reusable date filtering functionality for admin index pages
 */

class AdminDateFilter {
    constructor(options = {}) {
        this.filterId = options.filterId || 'dateFilter';
        this.modalId = options.modalId || 'customDateRangeModal';
        this.startDateId = options.startDateId || 'modalStartDate';
        this.endDateId = options.endDateId || 'modalEndDate';
        this.onFilterChange = options.onFilterChange || null;

        this.init();
    }

    init() {
        this.setupDateFilterChange();
        this.setupPresetButtons();
        this.setupApplyButton();
        this.setupModalClose();
    }

    /**
     * Setup date filter dropdown change event
     */
    setupDateFilterChange() {
        $(`#${this.filterId}`).on('change', (e) => {
            const value = $(e.target).val();

            if (value === 'custom') {
                this.showCustomDateModal();
            } else if (value !== '') {
                if (this.onFilterChange) {
                    this.onFilterChange();
                }
            }
        });
    }

    /**
     * Show custom date range modal with default dates
     */
    showCustomDateModal() {
        // Set default dates (last 30 days)
        const endDate = new Date().toISOString().split('T')[0];
        const startDate = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];

        $(`#${this.startDateId}`).val(startDate);
        $(`#${this.endDateId}`).val(endDate);

        // Show modal
        $(`#${this.modalId}`).modal('show');
    }

    /**
     * Setup preset date range buttons
     */
    setupPresetButtons() {
        const modal = $(`#${this.modalId}`);

        modal.find('.preset-date-btn').on('click', (e) => {
            const preset = $(e.target).data('preset');
            const { startDate, endDate } = this.getPresetDates(preset);

            $(`#${this.startDateId}`).val(startDate);
            $(`#${this.endDateId}`).val(endDate);
        });
    }

    /**
     * Get preset date ranges
     */
    getPresetDates(preset) {
        const endDate = new Date().toISOString().split('T')[0];
        let startDate;

        switch (preset) {
            case '7':
                startDate = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
                break;
            case '30':
                startDate = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
                break;
            case '90':
                startDate = new Date(Date.now() - 90 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
                break;
            case 'year':
                const now = new Date();
                startDate = new Date(now.getFullYear(), 0, 1).toISOString().split('T')[0];
                break;
            default:
                startDate = endDate;
        }

        return { startDate, endDate };
    }

    /**
     * Setup apply button
     */
    setupApplyButton() {
        $(`#${this.modalId}`).find('.apply-date-range-btn').on('click', () => {
            const startDate = $(`#${this.startDateId}`).val();
            const endDate = $(`#${this.endDateId}`).val();

            if (!startDate || !endDate) {
                alert('Please select both start and end dates.');
                return;
            }

            if (new Date(startDate) > new Date(endDate)) {
                alert('Start date cannot be later than end date.');
                return;
            }

            // Store the selected dates
            $(`#${this.filterId}`).data('startDate', startDate);
            $(`#${this.filterId}`).data('endDate', endDate);

            if (this.onFilterChange) {
                this.onFilterChange();
            }

            $(`#${this.modalId}`).modal('hide');
        });
    }

    /**
     * Setup modal close event
     */
    setupModalClose() {
        $(`#${this.modalId}`).on('hidden.bs.modal', () => {
            // If no dates were applied, reset the filter
            if (!$(`#${this.filterId}`).data('startDate')) {
                $(`#${this.filterId}`).val('');
                if (this.onFilterChange) {
                    this.onFilterChange();
                }
            }
        });
    }

    /**
     * Get current filter value
     */
    getValue() {
        return $(`#${this.filterId}`).val();
    }

    /**
     * Get custom date range
     */
    getCustomDates() {
        return {
            startDate: $(`#${this.filterId}`).data('startDate'),
            endDate: $(`#${this.filterId}`).data('endDate')
        };
    }

    /**
     * Clear filter
     */
    clear() {
        $(`#${this.filterId}`).val('');
        $(`#${this.filterId}`).removeData('startDate');
        $(`#${this.filterId}`).removeData('endDate');
        $(`#${this.startDateId}`).val('');
        $(`#${this.endDateId}`).val('');
    }

    /**
     * Get AJAX data for DataTables
     */
    getAjaxData() {
        const data = {
            date: this.getValue()
        };

        if (data.date === 'custom') {
            const customDates = this.getCustomDates();
            data.start_date = customDates.startDate;
            data.end_date = customDates.endDate;
        }

        return data;
    }
}

// Make it globally available
window.AdminDateFilter = AdminDateFilter;
