<!-- Custom Date Range Modal -->
<div class="modal fade" id="{{ $modalId ?? 'customDateRangeModal' }}" tabindex="-1" aria-labelledby="{{ $modalId ?? 'customDateRangeModal' }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center" id="{{ $modalId ?? 'customDateRangeModal' }}Label">
                    <i data-lucide="calendar" class="icon-sm me-2"></i>Select Date Range
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6 mt-2 mb-0">
                        <label for="{{ $startDateId ?? 'modalStartDate' }}" class="form-label">From Date</label>
                        <input type="date" id="{{ $startDateId ?? 'modalStartDate' }}" class="form-control">
                    </div>
                    <div class="col-12 col-md-6 mt-2 mb-0">
                        <label for="{{ $endDateId ?? 'modalEndDate' }}" class="form-label">To Date</label>
                        <input type="date" id="{{ $endDateId ?? 'modalEndDate' }}" class="form-control">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm preset-date-btn" data-preset="7">Last 7 Days</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm preset-date-btn" data-preset="30">Last 30 Days</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm preset-date-btn" data-preset="90">Last 90 Days</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm preset-date-btn" data-preset="year">This Year</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                    <i data-lucide="x" class="icon-sm me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-sm btn-primary apply-date-range-btn">
                    <i data-lucide="check" class="icon-sm me-1"></i>Apply Filter
                </button>
            </div>
        </div>
    </div>
</div>