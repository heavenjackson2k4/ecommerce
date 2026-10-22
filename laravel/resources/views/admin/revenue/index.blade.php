@extends('layouts.admin')

@section('title', 'Doanh thu')
@section('page-title', 'Doanh thu')

@section('content')
<div class="relative mx-auto max-w-7xl space-y-6">
    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        @foreach($statistics as $key => $statistic)
            <div class="relative rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <div class="absolute right-5 top-5 flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 text-gray-500" aria-hidden="true">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        @if($key === 'today')
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 3h10a1 1 0 0 1 1 1v17l-3-2-3 2-3-2-3 2V4a1 1 0 0 1 1-1ZM9 7h6m-6 4h6m-6 4h3" />
                        @elseif($key === 'week')
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 2v4m8-4v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" />
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 20h16M7 16v-3m5 3V9m5 7V5" />
                        @endif
                    </svg>
                </div>
                <h2 class="pr-10 text-sm font-medium text-gray-500">{{ $statistic['label'] }}</h2>
                <p class="mt-2 break-words text-2xl font-bold text-gray-800">{{ number_format($statistic['revenue'], 0, ',', '.') }} đ</p>
                <p class="mt-2 text-sm text-gray-500">{{ $statistic['date_range'] }}</p>
            </div>
        @endforeach
    </div>

    <p class="text-sm leading-6 text-gray-500">
        Chỉ tính tổng tiền của đơn đã nhận hàng (delivered), thống kê theo ngày đặt đơn. Tuần bắt đầu từ thứ Hai.
    </p>

    <form action="{{ route('admin.revenue.index') }}" method="GET" class="rounded-xl border border-gray-200 bg-white p-5">
        <h2 class="font-semibold text-gray-900">Bộ lọc doanh thu</h2>
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach(['start_date' => 'Từ ngày', 'end_date' => 'Đến ngày'] as $field => $label)
                <div>
                    <label for="revenue-{{ $field }}" class="mb-2 block text-sm font-medium text-gray-700">{{ $label }}</label>
                    <div class="relative">
                        <input
                            type="text"
                            id="revenue-{{ $field }}"
                            name="{{ $field }}"
                            value="{{ is_string(old($field)) ? old($field) : $filters[$field] }}"
                            placeholder="dd/mm/yyyy"
                            maxlength="10"
                            pattern="[0-9]{2}/[0-9]{2}/[0-9]{4}"
                            required
                            @error($field) aria-invalid="true" aria-describedby="revenue-{{ $field }}-error" @enderror
                            class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-3 pr-11 text-sm text-gray-900 outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900"
                        >
                        <button type="button" id="revenue-{{ $field }}-open" aria-label="{{ $label }}: mở lịch" class="absolute right-0 top-0 flex h-full w-10 items-center justify-center rounded-lg text-gray-500 hover:text-black focus-visible:outline focus-visible:outline-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 2v4m8-4v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" />
                            </svg>
                        </button>
                        <input type="date" id="revenue-{{ $field }}-picker" max="{{ $maxDate }}" tabindex="-1" aria-label="{{ $label }}: lịch chọn ngày" class="pointer-events-none absolute bottom-0 left-0 h-px w-px opacity-0">
                    </div>
                    @error($field)
                        <p id="revenue-{{ $field }}-error" class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach
            <div>
                <label for="revenue-group-by" class="mb-2 block text-sm font-medium text-gray-700">Tổng hợp</label>
                <select
                    id="revenue-group-by"
                    name="group_by"
                    @error('group_by') aria-invalid="true" aria-describedby="revenue-group-by-error" @enderror
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900"
                >
                    @foreach($groupOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('group_by', $filters['group_by']) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('group_by')
                    <p id="revenue-group-by-error" class="mt-2 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex flex-wrap items-start gap-3 sm:col-span-2 lg:col-span-1 lg:pt-7">
                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-black focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">
                    Áp dụng
                </button>
                <a href="{{ route('admin.revenue.index') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:border-gray-900 hover:text-black">
                    Đặt lại
                </a>
            </div>
        </div>
        <p class="mt-3 text-xs text-gray-500">Khoảng lọc tối đa 366 ngày. Bộ lọc chỉ áp dụng cho bảng bên dưới.</p>
    </form>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-6 py-5">
            <div>
                <h2 class="font-semibold text-gray-900">Chi tiết doanh thu</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $report['date_range'] }}</p>
            </div>
            <span class="rounded-lg bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-700">{{ $groupOptions[$filters['group_by']] }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <caption class="sr-only">Doanh thu và số đơn đã nhận hàng theo ngày đặt trong khoảng lọc</caption>
                <thead class="border-b border-gray-200 bg-gray-50 text-gray-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left font-medium">Thời gian</th>
                        <th scope="col" class="px-6 py-4 text-right font-medium">Số đơn đã nhận hàng</th>
                        <th scope="col" class="px-6 py-4 text-right font-medium">Doanh thu</th>
                        <th scope="col" class="w-24 px-6 py-4 text-center font-medium">Chi tiết</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($report['periods'] as $period)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900">{{ $period['label'] }}</p>
                                @if($period['date_range'])
                                    <p class="mt-1 text-xs text-gray-500">{{ $period['date_range'] }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right tabular-nums text-gray-700">{{ number_format($period['order_count'], 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right font-semibold tabular-nums text-gray-900">{{ number_format($period['revenue'], 0, ',', '.') }} đ</td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('admin.revenue.orders', [
                                    'start_date' => $filters['start_date'],
                                    'end_date' => $filters['end_date'],
                                    'group_by' => $filters['group_by'],
                                    'period_start' => $period['start_date'],
                                    'period_end' => $period['end_date'],
                                    'report_page' => $report['periods']->currentPage(),
                                ]) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 transition hover:border-gray-900 hover:text-black focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900" aria-label="Xem đơn hàng: {{ $period['label'] }}" title="Xem đơn hàng">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" />
                                        <circle cx="12" cy="12" r="3" stroke-width="1.5" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                {{ $report['order_count'] ? 'Không có dữ liệu trên trang này.' : 'Chưa có doanh thu trong khoảng thời gian đã chọn.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="border-t border-gray-200 bg-gray-50">
                    <tr>
                        <th scope="row" class="px-6 py-4 text-left font-semibold text-gray-900">Tổng trong khoảng lọc</th>
                        <td class="px-6 py-4 text-right font-semibold tabular-nums text-gray-900">{{ number_format($report['order_count'], 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-right font-bold tabular-nums text-gray-900">{{ number_format($report['revenue'], 0, ',', '.') }} đ</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if($report['periods']->hasPages())
        <div>{{ $report['periods']->links() }}</div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        ['start_date', 'end_date'].forEach(function (field) {
            const dateInput = document.getElementById('revenue-' + field);
            const picker = document.getElementById('revenue-' + field + '-picker');

            function syncPicker() {
                const match = dateInput.value.trim().match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
                picker.value = match ? `${match[3]}-${match[2]}-${match[1]}` : '';
            }

            syncPicker();
            dateInput.addEventListener('input', syncPicker);
            picker.addEventListener('change', function () {
                const [year, month, day] = picker.value.split('-');
                dateInput.value = picker.value ? `${day}/${month}/${year}` : '';
            });

            document.getElementById('revenue-' + field + '-open').addEventListener('click', function () {
                syncPicker();
                if (typeof picker.showPicker === 'function') {
                    picker.showPicker();
                } else {
                    picker.className = 'mt-2 block w-full rounded-lg border border-gray-300 p-2';
                    picker.tabIndex = 0;
                    picker.focus();
                    picker.click();
                }
            });
        });
    });
</script>
@endpush
