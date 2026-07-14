<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ChartData
{
    /**
     * Bucket a query into the last N calendar months, zero-filling gaps.
     * $valueExpression is controller-supplied raw SQL (never user input),
     * e.g. 'COUNT(*)' or 'SUM(company_amount)'.
     *
     * @return array{categories: list<string>, data: list<float>}
     */
    public static function monthly(Builder $query, string $dateColumn, string $valueExpression = 'COUNT(*)', int $months = 6): array
    {
        $start = Carbon::now()->startOfMonth()->subMonths($months - 1);

        $rows = $query->clone()
            ->where($dateColumn, '>=', $start)
            ->selectRaw("DATE_FORMAT($dateColumn, '%Y-%m') as bucket, $valueExpression as value")
            ->groupBy('bucket')
            ->pluck('value', 'bucket');

        $categories = [];
        $data = [];

        for ($i = 0; $i < $months; $i++) {
            $point = $start->copy()->addMonths($i);
            $categories[] = $point->format('M Y');
            $data[] = round((float) ($rows[$point->format('Y-m')] ?? 0), 2);
        }

        return ['categories' => $categories, 'data' => $data];
    }

    /**
     * Bucket a query into the last N days, zero-filling gaps.
     *
     * @return array{categories: list<string>, data: list<float>}
     */
    public static function daily(Builder $query, string $dateColumn, string $valueExpression = 'COUNT(*)', int $days = 14): array
    {
        $start = Carbon::now()->startOfDay()->subDays($days - 1);

        $rows = $query->clone()
            ->where($dateColumn, '>=', $start)
            ->selectRaw("DATE($dateColumn) as bucket, $valueExpression as value")
            ->groupBy('bucket')
            ->pluck('value', 'bucket');

        $categories = [];
        $data = [];

        for ($i = 0; $i < $days; $i++) {
            $point = $start->copy()->addDays($i);
            $categories[] = $point->format('d M');
            $data[] = round((float) ($rows[$point->format('Y-m-d')] ?? 0), 2);
        }

        return ['categories' => $categories, 'data' => $data];
    }
}
