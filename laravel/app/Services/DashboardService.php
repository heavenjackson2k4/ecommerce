<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;

class DashboardService
{
    public function getOverviewStatistics(): array
    {
        $startOfToday = now()->startOfDay();
        $startOfTomorrow = $startOfToday->copy()->addDay();

        $orderStatistics = Order::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw(
                'SUM(CASE WHEN created_at >= ? AND created_at < ? THEN 1 ELSE 0 END) as today',
                [$startOfToday, $startOfTomorrow]
            )
            ->selectRaw(
                'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending',
                ['pending']
            )
            ->first();

        return [
            'products' => Product::count(),
            'orders' => (int) $orderStatistics->total,
            'orders_today' => (int) $orderStatistics->today,
            'pending_orders' => (int) $orderStatistics->pending,
            'categories' => Category::count(),
        ];
    }
}
