<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class RevenueService
{
    private const PER_PAGE = 25;

    public function getOverviewStatistics(): array
    {
        $today = now()->startOfDay();
        $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY);
        $monthStart = $today->copy()->startOfMonth();
        $earliestStart = $weekStart->lessThan($monthStart) ? $weekStart : $monthStart;

        $statistics = Order::query()
            ->where('status', 'delivered')
            ->where('created_at', '>=', $earliestStart)
            ->where('created_at', '<', $today->copy()->addDay())
            ->selectRaw('COALESCE(SUM(CASE WHEN created_at >= ? THEN total_amount ELSE 0 END), 0) as today', [$today])
            ->selectRaw('COALESCE(SUM(CASE WHEN created_at >= ? THEN total_amount ELSE 0 END), 0) as week', [$weekStart])
            ->selectRaw('COALESCE(SUM(CASE WHEN created_at >= ? THEN total_amount ELSE 0 END), 0) as month', [$monthStart])
            ->first();

        return [
            'today' => [
                'label' => 'Doanh thu hôm nay',
                'date_range' => $today->format('d/m/Y'),
                'revenue' => (float) $statistics->today,
            ],
            'week' => [
                'label' => 'Doanh thu tuần này',
                'date_range' => $weekStart->format('d/m') . ' – ' . $today->copy()->endOfWeek(Carbon::SUNDAY)->format('d/m/Y'),
                'revenue' => (float) $statistics->week,
            ],
            'month' => [
                'label' => 'Doanh thu tháng này',
                'date_range' => $today->format('m/Y'),
                'revenue' => (float) $statistics->month,
            ],
        ];
    }

    public function getRevenueReport(Carbon $startDate, Carbon $endDate, string $groupBy): array
    {
        $dailyRevenue = Order::query()
            ->where('status', 'delivered')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<', $endDate->copy()->addDay())
            ->selectRaw('DATE(created_at) as order_date, COUNT(*) as order_count, SUM(total_amount) as revenue')
            ->groupBy('order_date')
            ->orderByDesc('order_date')
            ->get();

        // The date filter limits this collection to at most 366 daily totals, not individual orders.
        $rows = $dailyRevenue->groupBy(function ($day) use ($groupBy) {
            $date = Carbon::parse($day->order_date);

            return match ($groupBy) {
                'week' => $date->startOfWeek(Carbon::MONDAY)->toDateString(),
                'month' => $date->startOfMonth()->toDateString(),
                default => $date->toDateString(),
            };
        })->map(function ($days, $period) use ($groupBy, $startDate, $endDate) {
            $periodStart = Carbon::parse($period);
            $periodEnd = match ($groupBy) {
                'week' => $periodStart->copy()->endOfWeek(Carbon::SUNDAY)->startOfDay(),
                'month' => $periodStart->copy()->endOfMonth()->startOfDay(),
                default => $periodStart->copy(),
            };
            $rangeStart = $periodStart->lessThan($startDate) ? $startDate : $periodStart;
            $rangeEnd = $periodEnd->greaterThan($endDate) ? $endDate : $periodEnd;

            return [
                'label' => match ($groupBy) {
                    'week' => 'Tuần ' . $periodStart->format('d/m/Y'),
                    'month' => 'Tháng ' . $periodStart->format('m/Y'),
                    default => $periodStart->format('d/m/Y'),
                },
                'date_range' => $groupBy === 'day' ? null : $rangeStart->format('d/m/Y') . ' – ' . $rangeEnd->format('d/m/Y'),
                'start_date' => $rangeStart->format('d/m/Y'),
                'end_date' => $rangeEnd->format('d/m/Y'),
                'order_count' => (int) $days->sum('order_count'),
                'revenue' => round($days->sum('revenue'), 2),
            ];
        })->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $periods = new LengthAwarePaginator($rows->forPage($page, self::PER_PAGE)->values(), $rows->count(), self::PER_PAGE, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        return [
            'periods' => $periods->withQueryString(),
            'order_count' => (int) $dailyRevenue->sum('order_count'),
            'revenue' => round($dailyRevenue->sum('revenue'), 2),
            'date_range' => $startDate->format('d/m/Y') . ' – ' . $endDate->format('d/m/Y'),
        ];
    }

    public function getRevenueOrders(Carbon $startDate, Carbon $endDate): array
    {
        $query = Order::query()
            ->where('status', 'delivered')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<', $endDate->copy()->addDay());

        $summary = (clone $query)
            ->selectRaw('COUNT(*) as order_count, COALESCE(SUM(total_amount), 0) as revenue')
            ->first();

        $orders = $query
            ->select(['id', 'order_code', 'customer_name', 'created_at', 'total_amount'])
            ->with([
                'items:id,order_id,product_id,size,color,stud_type,quantity,price,total',
                'items.product:id,name',
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return [
            'orders' => $orders,
            'order_count' => (int) $summary->order_count,
            'revenue' => (float) $summary->revenue,
            'date_range' => $startDate->format('d/m/Y') . ' – ' . $endDate->format('d/m/Y'),
        ];
    }
}
