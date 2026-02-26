<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait HasDateFilter
{
    /**
     * Apply date filter to query
     *
     * @param Builder|\Illuminate\Database\Eloquent\Relations\Relation $query
     * @param Request $request
     * @param string $column The column to filter (default: 'created_at')
     * @return Builder|\Illuminate\Database\Eloquent\Relations\Relation
     */
    public function applyDateFilter($query, Request $request, string $column = 'created_at')
    {
        if (!$request->filled('date')) {
            return $query;
        }

        $dateFilter = $request->date;

        switch ($dateFilter) {
            case 'today':
                $query->whereDate($column, today());
                break;

            case 'week':
                $query->whereBetween($column, [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ]);
                break;

            case 'month':
                $query->whereMonth($column, now()->month)
                    ->whereYear($column, now()->year);
                break;

            case 'year':
                $query->whereYear($column, now()->year);
                break;

            case 'custom':
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $query->whereBetween($column, [
                        $request->start_date . ' 00:00:00',
                        $request->end_date . ' 23:59:59'
                    ]);
                }
                break;
        }

        return $query;
    }
}
