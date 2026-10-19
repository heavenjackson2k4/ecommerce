<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RevenueService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RevenueController extends Controller
{
    private const GROUP_OPTIONS = [
        'day' => 'Theo ngày',
        'week' => 'Theo tuần',
        'month' => 'Theo tháng',
    ];

    protected RevenueService $revenueService;

    public function __construct(RevenueService $revenueService)
    {
        $this->revenueService = $revenueService;
    }

    public function index(Request $request)
    {
        $dateFilters = $this->getFilters($request);
        $filters = [
            'start_date' => $dateFilters['start_date']->format('d/m/Y'),
            'end_date' => $dateFilters['end_date']->format('d/m/Y'),
            'group_by' => $dateFilters['group_by'],
        ];
        $groupOptions = self::GROUP_OPTIONS;
        $maxDate = now()->toDateString();
        $statistics = $this->revenueService->getOverviewStatistics();
        $report = $this->revenueService->getRevenueReport($dateFilters['start_date'], $dateFilters['end_date'], $filters['group_by']);

        return view('admin.revenue.index', compact('statistics', 'report', 'filters', 'groupOptions', 'maxDate'));
    }

    private function getFilters(Request $request): array
    {
        $today = now()->startOfDay();
        $validated = $request->validate([
            'start_date' => ['nullable', 'date_format:d/m/Y', 'before_or_equal:' . $today->toDateString()],
            'end_date' => ['nullable', 'date_format:d/m/Y', 'before_or_equal:' . $today->toDateString()],
            'group_by' => ['nullable', Rule::in(array_keys(self::GROUP_OPTIONS))],
        ], [
            'start_date.date_format' => 'Ngày bắt đầu phải hợp lệ và có định dạng dd/mm/yyyy.',
            'end_date.date_format' => 'Ngày kết thúc phải hợp lệ và có định dạng dd/mm/yyyy.',
            'start_date.before_or_equal' => 'Ngày bắt đầu không được sau hôm nay.',
            'end_date.before_or_equal' => 'Ngày kết thúc không được sau hôm nay.',
            'group_by.in' => 'Vui lòng chọn tổng hợp theo ngày, tuần hoặc tháng.',
        ]);

        $startDate = isset($validated['start_date'])
            ? Carbon::createFromFormat('!d/m/Y', $validated['start_date'])
            : $today->copy()->startOfMonth();
        $endDate = isset($validated['end_date'])
            ? Carbon::createFromFormat('!d/m/Y', $validated['end_date'])
            : $today->copy();

        if ($startDate->greaterThan($endDate)) {
            throw ValidationException::withMessages(['end_date' => 'Ngày kết thúc phải bằng hoặc sau ngày bắt đầu.']);
        }

        if ($startDate->diffInDays($endDate) > 365) {
            throw ValidationException::withMessages(['end_date' => 'Mỗi lần chỉ được xem tối đa 366 ngày.']);
        }

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'group_by' => $validated['group_by'] ?? 'day',
        ];
    }
}
