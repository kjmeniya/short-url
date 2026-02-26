<label for="{{ $id ?? 'dateFilter' }}" class="form-label">{{ $label ?? 'Date' }}</label>
<select id="{{ $id ?? 'dateFilter' }}" class="form-select form-select-sm date-filter">
    <option value="">All Time</option>
    <option value="today">Today</option>
    <option value="week">This Week</option>
    <option value="month">This Month</option>
    <option value="year">This Year</option>
    <option value="custom">Custom Date Range</option>
</select>